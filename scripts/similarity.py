import json
import os
import re
import sys
from collections import Counter
from pathlib import Path

import torch
from docx import Document
from pypdf import PdfReader
from transformers import AutoModel, AutoModelForSequenceClassification, AutoTokenizer


MODEL_NAME = os.environ.get("SIMILARITY_MODEL", "sentence-transformers/all-MiniLM-L6-v2")
RERANKER_NAME = os.environ.get("SIMILARITY_RERANKER", "cross-encoder/ms-marco-MiniLM-L-6-v2")
CHUNK_SIZE = 1200
EVIDENCE_CACHE_VERSION = 2
STOP_WORDS = {
    "about", "across", "also", "analysis", "approach", "based", "baseline", "between", "both", "can",
    "data", "document", "documents", "each", "effectiveness", "evaluated", "evaluation", "extends", "from",
    "illustrates", "integrated", "into", "more", "most", "other", "over", "paper", "papers", "project",
    "research", "result", "results", "significant", "significantly", "such", "systems", "that", "their",
    "there", "these", "this", "through", "under", "using", "with", "within", "which", "will", "would", "your",
}
GENERIC_METHOD_TERMS = {
    "algorithm", "algorithms", "artificial", "classification", "computer", "dataset", "datasets",
    "deep", "embedding", "embeddings", "framework", "learning", "machine", "model", "models",
    "neural", "ranking", "retrieval", "search", "semantic", "system", "systems", "transformer",
}


def split_chunks(text: str, page: str) -> list[dict]:
    text = clean_text(text)
    if not text:
        return []

    sentences = [sentence.strip() for sentence in re.split(r"(?<=[.!?])\s+", text) if sentence.strip()]
    if not sentences:
        return []

    chunks = []
    current = []
    current_length = 0
    for sentence in sentences:
        if current and current_length + len(sentence) + 1 > CHUNK_SIZE:
            chunks.append({"page": page, "text": " ".join(current)})
            current = [current[-1]]
            current_length = len(current[0])
        current.append(sentence)
        current_length += len(sentence) + 1

    if current:
        chunks.append({"page": page, "text": " ".join(current)})
    return chunks


def extract_page_chunks(path: str, max_characters: int = 12000, max_pdf_pages: int = 6) -> list[dict]:
    source = Path(path)
    if source.suffix.lower() == ".docx":
        parts = []
        characters_read = 0
        for paragraph in Document(path).paragraphs:
            parts.append(paragraph.text)
            characters_read += len(paragraph.text)
            if characters_read >= max_characters:
                break
        return split_chunks("\n".join(parts)[:max_characters], "Document")

    reader = PdfReader(path)
    chunks = []
    characters_read = 0
    for page_number, page in enumerate(reader.pages):
        if page_number >= max_pdf_pages:
            break

        remaining = max_characters - characters_read
        if remaining <= 0:
            break
        text = (page.extract_text() or "")[:remaining]
        characters_read += len(text)
        chunks.extend(split_chunks(text, f"Page {page_number + 1}"))

    return chunks


def extract_text(path: str, max_characters: int = 12000, max_pdf_pages: int = 6) -> str:
    chunks = extract_page_chunks(path, max_characters, max_pdf_pages)
    return " ".join(chunk["text"] for chunk in chunks)[:max_characters]


def clean_text(value: str) -> str:
    value = re.sub(r"(\w)-\s+(\w)", r"\1\2", str(value or ""))
    return re.sub(r"\s+", " ", value).strip()


def mean_pooling(model_output, attention_mask):
    tokens = model_output.last_hidden_state
    mask = attention_mask.unsqueeze(-1).expand(tokens.size()).float()
    return torch.sum(tokens * mask, dim=1) / torch.clamp(mask.sum(dim=1), min=1e-9)


def encode(texts, tokenizer, model, batch_size=8):
    texts = [text if isinstance(text, str) else str(text or "") for text in texts]
    vectors = []
    with torch.no_grad():
        for start in range(0, len(texts), batch_size):
            batch = texts[start:start + batch_size]
            try:
                encoded = tokenizer(batch, padding=True, truncation=True, max_length=512, return_tensors="pt")
                batch_vectors = mean_pooling(model(**encoded), encoded["attention_mask"])
                vectors.append(torch.nn.functional.normalize(batch_vectors, p=2, dim=1))
            except (TypeError, ValueError):
                # A malformed PDF text fragment must not prevent every other paper from being compared.
                fallback_vectors = []
                for text in batch:
                    try:
                        encoded = tokenizer(text, truncation=True, max_length=512, return_tensors="pt")
                    except (TypeError, ValueError):
                        encoded = tokenizer("No readable text.", truncation=True, max_length=512, return_tensors="pt")
                    vector = mean_pooling(model(**encoded), encoded["attention_mask"])
                    fallback_vectors.append(torch.nn.functional.normalize(vector, p=2, dim=1))
                vectors.append(torch.cat(fallback_vectors))
    return torch.cat(vectors) if vectors else torch.empty((0, 384))


def shared_concepts(source_text: str, reference_text: str) -> list[str]:
    def terms(value: str) -> Counter:
        tokens = re.findall(r"[a-zA-Z][a-zA-Z-]{3,}", value.lower())
        return Counter(token for token in tokens if token not in STOP_WORDS)

    source_terms = terms(source_text)
    reference_terms = terms(reference_text)
    common = set(source_terms).intersection(reference_terms)
    ranked = sorted(
        common,
        key=lambda term: (min(source_terms[term], reference_terms[term]), len(term)),
        reverse=True,
    )
    return ranked[:4]


def load_reranker():
    tokenizer = AutoTokenizer.from_pretrained(RERANKER_NAME)
    model = AutoModelForSequenceClassification.from_pretrained(RERANKER_NAME)
    model.eval()
    return tokenizer, model


def rerank_pairs(pairs: list[tuple[str, str]], tokenizer, model) -> list[float]:
    with torch.no_grad():
        encoded = tokenizer(
            [pair[0] for pair in pairs],
            [pair[1] for pair in pairs],
            padding=True,
            truncation=True,
            max_length=512,
            return_tensors="pt",
        )
        logits = model(**encoded).logits.squeeze(-1)
        return torch.sigmoid(logits).tolist()


def classify_match(concepts: list[str], reranker_score: float) -> str:
    specific_concepts = [concept for concept in concepts if concept not in GENERIC_METHOD_TERMS]
    if reranker_score >= 0.6 and len(specific_concepts) >= 2:
        return "Strong topical overlap"
    if reranker_score >= 0.35 or concepts:
        return "Methodological overlap"
    return "No meaningful overlap"


def explanation_for(category: str, concepts: list[str]) -> str:
    if category == "No meaningful overlap":
        return "The passages are not sufficiently related to treat this as a meaningful research overlap."
    if category == "Methodological overlap":
        return "Both passages use related methods, but the underlying research problem appears different."
    if len(concepts) == 1:
        return f"Both passages focus on {concepts[0]}."
    if len(concepts) == 2:
        return f"Both passages discuss {concepts[0]} and {concepts[1]}."
    return f"Both passages discuss {concepts[0]}, {concepts[1]}, and {concepts[2]}."


def evidence_chunks(manuscript: dict, cache: dict, tokenizer, model) -> tuple[list[dict], bool]:
    key = manuscript["checksum"]
    cache_entry = cache.get(key, {})
    cached_chunks = cache_entry.get("evidence_chunks")
    if cache_entry.get("evidence_version") == EVIDENCE_CACHE_VERSION and isinstance(cached_chunks, list) and cached_chunks:
        return cached_chunks, False

    chunks = extract_page_chunks(manuscript["file_path"])
    if not chunks:
        fallback = clean_text(" ".join([
            manuscript.get("title") or "",
            manuscript.get("abstract") or "",
        ]))
        chunks = [{"page": "Document", "text": fallback or "No readable text."}]

    vectors = encode([chunk["text"] for chunk in chunks], tokenizer, model)
    cached_chunks = [
        {"page": chunk["page"], "text": chunk["text"][:CHUNK_SIZE], "vector": vectors[index].tolist()}
        for index, chunk in enumerate(chunks)
    ]
    cache_entry = cache.setdefault(key, {})
    cache_entry["evidence_chunks"] = cached_chunks
    cache_entry["evidence_version"] = EVIDENCE_CACHE_VERSION
    return cached_chunks, True


def match_evidence(target_chunks: list[dict], target_vectors, reference_chunks: list[dict], reranker_tokenizer, reranker_model) -> dict:
    reference_vectors = torch.tensor([chunk["vector"] for chunk in reference_chunks])
    similarities = target_vectors @ reference_vectors.T
    candidate_count = min(4, similarities.numel())
    candidate_indexes = torch.topk(similarities.flatten(), candidate_count).indices.tolist()
    candidate_pairs = []
    candidate_locations = []
    for candidate_index in candidate_indexes:
        target_index, reference_index = divmod(candidate_index, similarities.shape[1])
        candidate_pairs.append((target_chunks[target_index]["text"], reference_chunks[reference_index]["text"]))
        candidate_locations.append((target_index, reference_index))

    reranker_scores = rerank_pairs(candidate_pairs, reranker_tokenizer, reranker_model)
    best_candidate = max(range(len(reranker_scores)), key=lambda index: reranker_scores[index])
    target_index, reference_index = candidate_locations[best_candidate]
    source = target_chunks[target_index]
    reference = reference_chunks[reference_index]
    concepts = shared_concepts(source["text"], reference["text"])
    reranker_score = reranker_scores[best_candidate]
    category = classify_match(concepts, reranker_score)
    return {
        "reason": explanation_for(category, concepts),
        "category": category,
        "shared_concepts": concepts,
        "source_excerpt": source["text"][:360],
        "source_page": source["page"],
        "reference_excerpt": reference["text"][:360],
        "reference_page": reference["page"],
    }


def main():
    request = json.load(sys.stdin)
    cache_path = Path(request["cache_path"])
    cache_path.parent.mkdir(parents=True, exist_ok=True)
    cache = json.loads(cache_path.read_text(encoding="utf-8")) if cache_path.exists() else {}

    tokenizer = AutoTokenizer.from_pretrained(MODEL_NAME)
    model = AutoModel.from_pretrained(MODEL_NAME)
    model.eval()
    reranker_tokenizer, reranker_model = load_reranker()
    torch.set_num_threads(min(4, os.cpu_count() or 1))

    proposal = request.get("proposal") or {}
    proposal_text = clean_text(" ".join([
        proposal.get("title") or "",
        proposal.get("problem") or "",
        proposal.get("objectives") or "",
        proposal.get("solution") or "",
    ]))
    target_text = clean_text(extract_text(request["document_path"]))
    target_text = clean_text(" ".join([
        target_text,
        proposal_text,
        proposal_text,
    ]))
    if not target_text:
        raise ValueError("The uploaded document does not contain readable text.")

    target_vector = encode([target_text[:12000]], tokenizer, model)[0]
    target_chunks = extract_page_chunks(request["document_path"])
    if not target_chunks:
        target_chunks = [{"page": "Uploaded document", "text": target_text[:CHUNK_SIZE]}]
    if proposal_text:
        target_chunks.insert(0, {"page": "Proposal details", "text": proposal_text[:CHUNK_SIZE]})
    target_vectors = encode([chunk["text"] for chunk in target_chunks], tokenizer, model)
    records = []
    missing = []
    manuscripts = request.get("manuscripts", [])
    cache_changed = False

    for manuscript in manuscripts:
        key = manuscript["checksum"]
        if key in cache:
            manuscript["vector"] = cache[key]["vector"]
            manuscript["excerpt"] = cache[key]["excerpt"]
        else:
            text = clean_text(extract_text(manuscript["file_path"]))
            text = clean_text(" ".join([
                text,
                manuscript.get("title") or "",
                manuscript.get("abstract") or "",
            ]))
            missing.append((key, manuscript, text[:12000]))
        records.append(manuscript)

    if missing:
        vectors = encode([item[2] for item in missing], tokenizer, model)
        for index, (key, manuscript, text) in enumerate(missing):
            vector = vectors[index].tolist()
            cache[key] = {"vector": vector, "excerpt": text[:320]}
            manuscript["vector"] = vector
            manuscript["excerpt"] = text[:320]
        cache_changed = True

    matches = []
    records_by_slug = {}
    for manuscript in records:
        records_by_slug[manuscript["slug"]] = manuscript
        score = float(torch.tensor(manuscript["vector"]).dot(target_vector))
        matches.append({
            "slug": manuscript["slug"],
            "title": manuscript["title"],
            "authors": manuscript.get("authors") or "Unknown authors",
            "score": max(0, min(100, round(score * 100))),
            "excerpt": manuscript.get("excerpt", "") or "No readable excerpt was found.",
        })

    matches.sort(key=lambda item: item["score"], reverse=True)
    reviewed_matches = []
    for match in matches[:10]:
        reference_chunks, evidence_cache_changed = evidence_chunks(
            records_by_slug[match["slug"]], cache, tokenizer, model
        )
        match.update(match_evidence(
            target_chunks,
            target_vectors,
            reference_chunks,
            reranker_tokenizer,
            reranker_model,
        ))
        cache_changed = cache_changed or evidence_cache_changed
        if match["category"] != "No meaningful overlap":
            reviewed_matches.append(match)

    matches = reviewed_matches[:5]

    if cache_changed:
        cache_path.write_text(json.dumps(cache), encoding="utf-8")

    score = matches[0]["score"] if matches else 0
    print(json.dumps({"score": score, "matches": matches, "papers_analyzed": len(manuscripts)}))


if __name__ == "__main__":
    main()
