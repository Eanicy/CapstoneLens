<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'CapstoneLens' }} - CapstoneLens</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined:wght@300..600&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        @php
            $navItems = [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'dashboard'],
                ['label' => 'Submit Idea', 'icon' => 'description', 'route' => 'submit-idea'],
                ['label' => 'AI Evaluation', 'icon' => 'psychology', 'route' => 'feasibility-result'],
                ['label' => 'Similarity Result', 'icon' => 'plagiarism', 'route' => 'similarity-result'],
                ['label' => 'Manuscript Repository', 'icon' => 'article', 'route' => 'repository.index'],
                ['label' => 'Profile', 'icon' => 'person', 'route' => 'profile'],
            ];
        @endphp

        <div class="app-shell">
            <aside class="sidebar">
                <div class="brand">
                    <div class="brand-mark">
                        <span class="material-symbols-outlined">school</span>
                    </div>
                    <div>
                        <strong>CapstoneLens</strong>
                        <span>{{ $console ?? 'Student Portal' }}</span>
                    </div>
                </div>

                <nav class="side-nav" aria-label="Primary navigation">
                    @foreach ($navItems as $item)
                        <a
                            @class(['nav-link', 'active' => request()->routeIs($item['route']) || ($item['route'] === 'repository.index' && request()->routeIs('repository.*'))])
                            href="{{ route($item['route']) }}"
                        >
                            <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <header class="mobile-header">
                <strong>CapstoneLens</strong>
                <span class="material-symbols-outlined">menu</span>
            </header>

            <main class="main-stage">
                @yield('content')
            </main>
        </div>
    </body>
</html>
