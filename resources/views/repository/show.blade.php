@extends('layouts.app', ['title' => 'Manuscript Details', 'console' => 'Academic Console'])

@section('content')
    <section class="page-wide">
        <header class="page-header">
            <div>
                <a class="button" href="{{ route('repository.index') }}" style="margin-bottom: 18px;">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Back to Repository
                </a>
                <h1 class="page-title">{{ $manuscript->title }}</h1>
                <p class="page-subtitle">Reference ID: {{ $manuscript->arxiv_id ?? $manuscript->source_filename }}</p>
            </div>
            <div class="actions">
                <a class="button" href="{{ route('repository.viewer', $manuscript) }}"><span class="material-symbols-outlined">visibility</span>Open Viewer</a>
                <button class="button button-primary" type="button"><span class="material-symbols-outlined">bookmark</span>Save Reference</button>
            </div>
        </header>

        <div class="detail-grid">
            <div class="list-stack" style="gap: 30px;">
                <article class="card card-pad">
                    <h2 class="section-title"><span class="material-symbols-outlined" style="color: var(--primary);">notes</span> Abstract</h2>
                    <p style="font-size: 20px;">
                        {{ $manuscript->abstract }}
                    </p>
                    <p style="font-size: 20px;">
                        This imported paper is registered in CapstoneLens and ready for later metadata enrichment, text extraction, and similarity analysis.
                    </p>
                </article>

                <div class="dashboard-grid">
                    <article class="card card-pad">
                        <h2 class="section-title"><span class="material-symbols-outlined" style="color: var(--primary);">inventory_2</span> Corpus Role</h2>
                        <ul class="check-list">
                            <li><span class="material-symbols-outlined" style="color: var(--primary);">check_circle</span>Stored privately outside the public web directory.</li>
                            <li><span class="material-symbols-outlined" style="color: var(--primary);">check_circle</span>Indexed with filename, checksum, arXiv-style ID, and import timestamp.</li>
                            <li><span class="material-symbols-outlined" style="color: var(--primary);">check_circle</span>Available as reference material for future similarity workflows.</li>
                        </ul>
                    </article>

                    <article class="card card-pad">
                        <h2 class="section-title"><span class="material-symbols-outlined" style="color: var(--primary);">select_check_box</span> Next Processing</h2>
                        <ul class="check-list">
                            <li><span class="material-symbols-outlined">info</span>Extract title, authors, and abstract from PDF text.</li>
                            <li><span class="material-symbols-outlined">info</span>Create embeddings for semantic search.</li>
                            <li><span class="material-symbols-outlined">info</span>Connect similarity scoring after the auth/RBAC phase.</li>
                        </ul>
                    </article>
                </div>
            </div>

            <aside class="list-stack" style="gap: 30px;">
                <article class="card card-pad">
                    <h2 class="meta-label">Document Metadata</h2>
                    <hr class="rule" style="margin: 18px 0;">
                    <div class="list-stack">
                        <div>
                            <strong>Authors</strong>
                            <p style="margin: 8px 0 0;">{{ $manuscript->authors ?? 'Imported Research Corpus' }}</p>
                        </div>
                        <div>
                            <strong>Source File</strong>
                            <p style="margin: 8px 0 0;">{{ $manuscript->source_filename }}</p>
                        </div>
                        <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr;">
                            <div>
                                <strong>Year</strong>
                                <p style="margin: 8px 0 0;">{{ $manuscript->published_year ?? 'Pending' }}</p>
                            </div>
                            <div>
                                <strong>Size</strong>
                                <p style="margin: 8px 0 0;">{{ number_format($manuscript->file_size / 1024 / 1024, 1) }} MB</p>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="card card-pad">
                    <h2 class="meta-label">Keywords</h2>
                    <div class="tag-row" style="margin-top: 16px;">
                        @foreach (($manuscript->keywords ?? []) as $keyword)
                            <span class="tag">{{ $keyword }}</span>
                        @endforeach
                        <span class="tag">{{ $manuscript->arxiv_id ?? 'Imported PDF' }}</span>
                    </div>
                    <h2 class="meta-label" style="margin-top: 30px;">Checksum</h2>
                    <div class="insight-box" style="margin-top: 16px; word-break: break-all;">
                        {{ $manuscript->checksum }}
                    </div>
                </article>

                <article class="card card-pad">
                    <h2 class="meta-label">Similarity Analysis</h2>
                    <div class="metric" style="margin-top: 18px;">
                        <div class="metric-head">
                            <span>Processing Status</span>
                            <span style="color: var(--primary);">Queued</span>
                        </div>
                        <div class="progress-track">
                            <span class="progress-bar" style="width: 18%;"></span>
                        </div>
                    </div>
                    <div class="insight-box" style="margin-top: 18px;">
                        Metadata import is complete. AI similarity scoring is intentionally not enabled yet.
                    </div>
                </article>
            </aside>
        </div>
    </section>
@endsection
