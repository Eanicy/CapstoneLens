# CapstoneLens

CapstoneLens is a Laravel-based academic manuscript repository and capstone proposal evaluation prototype. It lets students submit a document, compare it with locally stored research papers, and review the strongest evidence for each similarity result.

## Features

- Manuscript repository with local PDF import.
- In-browser PDF reader rendered as page images with continuous scrolling.
- Document upload progress before evaluation begins.
- Local, CPU-friendly similarity analysis for PDF and DOCX submissions.
- Evidence-based results that show matching passages, source pages, shared concepts, and overlap categories.
- No login, registration, or role-based access control is included in this prototype.

## Similarity Analysis

The comparison pipeline runs completely on the local machine:

1. `all-MiniLM-L6-v2` creates semantic embeddings and uses cosine similarity to retrieve candidate papers.
2. `ms-marco-MiniLM-L-6-v2` reranks relevant passage pairs to reduce superficial matches.
3. Results are labeled as strong topical overlap or methodological overlap, with no-meaningful-overlap results removed.

The first run can take longer while models and repository embeddings are prepared. Later requests reuse the local corpus cache.

## Requirements

- PHP 8.3 or later
- Composer
- Node.js and npm
- Python 3.14 with the locally installed similarity dependencies
- Poppler utilities (`pdftoppm` and `pdfinfo`) for PDF page rendering

## Installation

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Configure the local Python runtime in `.env` when it is not available through `python`:

```env
SIMILARITY_PYTHON=C:/Python314/python.exe
SIMILARITY_PYTHONPATH=C:/Users/YourName/AppData/Roaming/Python/Python314/site-packages
SIMILARITY_RERANKER=cross-encoder/ms-marco-MiniLM-L-6-v2
SIMILARITY_TIMEOUT=600
SIMILARITY_REQUEST_TIMEOUT=600
```

## Import Papers

Import PDFs from a folder into the local manuscript repository:

```powershell
php artisan manuscripts:import "C:\Users\Rig\Desktop\Thesis Dataset\computer\_science\_pdfs"
```

Duplicate files are skipped using their checksum. Imported PDFs are stored outside the public web root.

## Run Locally

Start the local server with the project script:

```powershell
.\serve-local.ps1
```

Open [http://127.0.0.1:8080](http://127.0.0.1:8080).

The script allows uploads up to 50 MB. Similarity evaluations have a 10-minute request allowance so the local model can finish processing the repository.

## Testing

```powershell
php artisan test --compact
vendor\bin\pint --test --format agent
npm run build
```

## Project Structure

- `app/Console/Commands/ImportManuscripts.php`: imports local repository PDFs.
- `app/Services/SimilarityAnalyzer.php`: launches the local similarity pipeline.
- `scripts/similarity.py`: embedding retrieval, reranking, and evidence extraction.
- `app/Support/PdfPageRenderer.php`: renders protected PDF pages for the reader.
- `resources/views/`: application screens based on the Stitch design.
