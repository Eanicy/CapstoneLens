@extends('layouts.app', ['title' => 'AI Similarity Analysis', 'console' => 'Academic Console'])

@section('content')
    @php($hasAnalysis = isset($analysis['score']))
    <section class="page-wide">
        <header class="page-header">
            <div>
                <h1 class="page-title">AI Similarity Analysis</h1>
                <p class="page-subtitle">{{ $hasAnalysis ? 'Local CPU analysis against '.$analysis['papers_analyzed'].' papers in the repository.' : 'Submit a document to begin local CPU analysis.' }}</p>
            </div>
            <div class="actions">
                <a class="button button-outline" href="{{ route('submit-idea') }}">Revise Idea</a>
                <button class="button button-primary" type="button">Submit to Faculty</button>
            </div>
        </header>

        <article class="warning-panel card-pad" style="margin-top: 28px;">
            <h2><span class="material-symbols-outlined">manage_search</span> Similarity Review</h2>
            <p style="font-size: 20px;">
                {{ $hasAnalysis ? 'The local transformer compared your uploaded document with every paper currently stored in the repository.' : 'No completed analysis is available for this page yet.' }}
            </p>
            <div class="insight-box" style="background: white;">
                <strong>Recommendation:</strong>
                <p style="margin-bottom: 0;">{{ $hasAnalysis ? 'Review the closest matches below. Similarity is an academic screening signal, not a plagiarism determination, and should be interpreted alongside the actual passages and citations.' : 'Return to Submit Idea, upload a document, and press Submit for AI Evaluation to generate results.' }}</p>
            </div>
        </article>

        <article class="card card-pad" style="margin-top: 28px;">
            <div class="page-header" style="margin-bottom: 18px;">
                <h2 class="section-title">Top Similar Projects</h2>
                <span class="meta-label">Found {{ count($analysis['matches'] ?? []) }} close matches</span>
            </div>

            <div class="list-stack">
                @foreach (($analysis['matches'] ?? []) as $match)
                    <div class="paper-card">
                        <div class="paper-top">
                            <div>
                                <span class="badge badge-warning">{{ $match['score'] }}% Match</span>
                                @if (isset($match['category']))
                                    <span class="badge {{ $match['category'] === 'Strong topical overlap' ? '' : 'badge-warning' }}">{{ $match['category'] }}</span>
                                @endif
                                <h2 style="margin-top: 12px; color: var(--primary);">{{ $match['title'] }}</h2>
                                <p style="margin: 0;">Authors: {{ $match['authors'] }}</p>
                            </div>
                            <a class="button" href="{{ route('repository.show', $match['slug']) }}">View Manuscript <span class="material-symbols-outlined">arrow_forward</span></a>
                        </div>
                        <div class="insight-box">
                            <strong>Why It Is Similar</strong>
                            <p class="match-reason">{{ $match['reason'] ?? 'The local model found a related passage in this manuscript.' }}</p>
                            @if (($match['category'] ?? null) === 'Strong topical overlap' && ! empty($match['shared_concepts'] ?? []))
                                <div class="tag-row" aria-label="Shared terms">
                                    @foreach ($match['shared_concepts'] as $concept)
                                        <span class="tag">{{ $concept }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="match-evidence">
                            <p><strong>Your document, {{ $match['source_page'] ?? 'matched passage' }}:</strong> &ldquo;{{ $match['source_excerpt'] ?? 'No readable source passage was found.' }}&rdquo;</p>
                            <p><strong>Repository manuscript, {{ $match['reference_page'] ?? 'matched passage' }}:</strong> &ldquo;{{ $match['reference_excerpt'] ?? $match['excerpt'] }}&rdquo;</p>
                        </div>
                    </div>
                @endforeach
                @if (empty($analysis['matches'] ?? []))
                    <p class="empty-state">{{ $hasAnalysis ? 'No meaningful topical or methodological overlaps were found in the repository.' : 'No analysis has been run yet.' }}</p>
                @endif
            </div>
        </article>
    </section>
@endsection
