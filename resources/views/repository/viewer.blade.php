@extends('layouts.app', ['title' => 'PDF Viewer', 'console' => 'Academic Console'])

@section('content')
    <section class="page-wide">
        <header class="page-header">
            <div>
                <a class="button" href="{{ route('repository.show', $manuscript) }}" style="margin-bottom: 18px;">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Back to Details
                </a>
                <h1 class="page-title">PDF Viewer</h1>
                <p class="page-subtitle">{{ $manuscript->title }}</p>
            </div>
            <div class="viewer-controls">
                <span class="viewer-page-label">
                    {{ $pageCount ? $pageCount.' pages' : 'Document preview' }}
                </span>
            </div>
        </header>

        <article class="viewer-shell">
            <div class="viewer-pages">
                @for ($viewerPage = 1; $viewerPage <= max(1, $pageCount ?? 1); $viewerPage++)
                    <figure class="viewer-page">
                        <img
                            class="viewer-page-image"
                            src="{{ route('repository.viewer.page', [$manuscript, $viewerPage]) }}"
                            alt="Rendered page {{ $viewerPage }} of {{ $manuscript->title }}"
                            draggable="false"
                            loading="lazy"
                        >
                        <figcaption>Page {{ $viewerPage }}</figcaption>
                    </figure>
                @endfor
            </div>
        </article>
    </section>

    <script>
        document.querySelectorAll('.viewer-page-image').forEach((image) => {
            image.addEventListener('contextmenu', (event) => event.preventDefault());
        });
    </script>
@endsection
