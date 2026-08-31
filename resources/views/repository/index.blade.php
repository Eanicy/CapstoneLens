@extends('layouts.app', ['title' => 'Manuscript Repository', 'console' => 'Academic Console'])

@section('content')
    <section class="page-wide">
        <header class="page-header">
            <div>
                <h1 class="page-title">Manuscript Repository</h1>
                <p class="page-subtitle">Explore and analyze imported academic papers from the local computer science corpus.</p>
            </div>
            <form method="GET" action="{{ route('repository.index') }}" style="width: min(100%, 480px);">
                <label>
                    <span class="meta-label">Search</span>
                    <input
                        class="search-input"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Search title, arXiv ID, filename, or author..."
                    >
                </label>
            </form>
        </header>

        <div class="repository-shell">
            <aside class="card card-pad filters">
                <div class="page-header" style="margin: 0 0 4px; padding-bottom: 18px; border-bottom: 1px solid var(--outline);">
                    <h2 class="section-title">Filters</h2>
                    <a class="button button-outline" href="{{ route('repository.index') }}" style="min-height: 34px; padding: 6px 10px;">Clear All</a>
                </div>

                <label class="filter-row">
                    <span class="meta-label">Year</span>
                    <select>
                        <option>All Years</option>
                        <option>2026</option>
                        <option>2025</option>
                        <option>2024</option>
                    </select>
                </label>
                <label class="filter-row">
                    <span class="meta-label">Department</span>
                    <select>
                        <option>Computer Science</option>
                        <option>Information Systems</option>
                    </select>
                </label>
                <div class="filter-row">
                    <span class="meta-label">Topic Area</span>
                    <label><input type="checkbox" checked> Imported PDFs</label>
                    <label><input type="checkbox"> Machine Learning</label>
                    <label><input type="checkbox"> Natural Language Processing</label>
                </div>
                <div class="filter-row">
                    <span class="meta-label">Max Similarity <strong style="float: right; color: var(--primary);">Pending</strong></span>
                    <input type="range" min="0" max="100" value="0" disabled>
                </div>
            </aside>

            <div class="list-stack">
                <div class="card card-pad page-header" style="margin: 0;">
                    <p style="margin: 0;">Showing <strong>{{ $manuscripts->total() }}</strong> manuscripts</p>
                    <span class="meta-label">Sort by <strong style="color: var(--primary); margin-left: 12px;">Newest Import</strong></span>
                </div>

                @forelse ($manuscripts as $manuscript)
                    <article class="paper-card">
                        <div class="paper-top">
                            <div>
                                <h2>{{ $manuscript->title }}</h2>
                                <p style="margin: 0 0 18px;">
                                    Authors: {{ $manuscript->authors ?? 'Imported Research Corpus' }}
                                    @if ($manuscript->published_year)
                                        &bull; {{ $manuscript->published_year }}
                                    @endif
                                </p>
                                <p>{{ $manuscript->abstract }}</p>
                            </div>
                            <span class="badge">Imported PDF</span>
                        </div>
                        <div class="tag-row">
                            @foreach (($manuscript->keywords ?? []) as $keyword)
                                <span class="tag">{{ $keyword }}</span>
                            @endforeach
                            <span class="tag">{{ $manuscript->source_filename }}</span>
                        </div>
                        <div class="actions" style="justify-content: space-between; padding-top: 20px; border-top: 1px solid var(--outline-soft);">
                            <a class="button button-primary" href="{{ route('repository.show', $manuscript) }}">View Details</a>
                            <a class="button button-outline" href="{{ route('repository.viewer', $manuscript) }}"><span class="material-symbols-outlined">visibility</span>Open Viewer</a>
                            <button class="button" type="button"><span class="material-symbols-outlined">travel_explore</span>Search Similar Projects</button>
                        </div>
                    </article>
                @empty
                    <article class="paper-card">
                        <div class="paper-top">
                            <div>
                                <h2>No manuscripts imported yet.</h2>
                                <p>Run the importer to copy PDFs into private storage and register them in the repository.</p>
                            </div>
                            <span class="badge badge-warning">Empty Corpus</span>
                        </div>
                        <div class="insight-box" style="margin-top: 18px;">
                            php artisan manuscripts:import "../computer_science_pdfs"
                        </div>
                    </article>
                @endforelse

                @if ($manuscripts->hasPages())
                    <div class="pagination-wrap">
                        {{ $manuscripts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
