@extends('layouts.app', ['title' => 'Feasibility Analysis', 'console' => 'Academic Console'])

@section('content')
    <section class="page-wide">
        <header class="page-header">
            <div>
                <div class="eyebrow">AI Evaluation Result</div>
                <h1 class="page-title">Feasibility Analysis</h1>
            </div>
            <div class="actions">
                <a class="button button-outline" href="{{ route('submit-idea') }}">Revise Idea</a>
                <a class="button button-primary" href="{{ route('similarity-result') }}">Submit to Faculty <span class="material-symbols-outlined">send</span></a>
            </div>
        </header>

        <div class="result-grid">
            <div class="list-stack">
                <article class="card card-pad center-card" style="background: #f1f6fd;">
                    <div>
                        <div class="status-icon" style="margin: 0 auto 24px;">
                            <span class="material-symbols-outlined">check_circle</span>
                        </div>
                        <div class="score">Feasible</div>
                        <p>Your thesis proposal meets all core requirements for successful execution.</p>
                    </div>
                </article>

                <article class="card card-pad">
                    <button class="button" type="button" style="width: 100%;">
                        <span class="material-symbols-outlined">download</span>
                        Download Full PDF Report
                    </button>
                </article>
            </div>

            <div class="list-stack" style="gap: 28px;">
                <article class="card card-pad">
                    <h2 class="section-title">Criteria Breakdown</h2>
                    <div class="list-stack" style="margin-top: 30px; gap: 26px;">
                        @foreach ([
                            ['Scope & Relevance', '90/100', '90%', 'Well-defined boundaries with clear academic contribution.', false],
                            ['Resource Availability', '85/100', '85%', 'Requires access to specialized databases, but generally accessible.', false],
                            ['Time Constraints', '70/100', '70%', 'Data collection phase might require careful scheduling.', true],
                            ['Methodological Complexity', '95/100', '95%', 'Appropriate level of difficulty for a capstone project.', false],
                        ] as [$label, $score, $width, $description, $warning])
                            <div class="metric">
                                <div class="metric-head">
                                    <span>{{ $label }}</span>
                                    <span class="badge @if ($warning) badge-warning @endif">{{ $score }}</span>
                                </div>
                                <div class="progress-track">
                                    <span class="progress-bar @if ($warning) progress-bar-warning @endif" style="width: {{ $width }};"></span>
                                </div>
                                <p style="margin: 0;">{{ $description }}</p>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="card card-pad">
                    <h2 class="section-title"><span class="material-symbols-outlined" style="color: var(--primary);">auto_awesome</span> AI Insights &amp; Recommendations</h2>
                    <div class="list-stack" style="margin-top: 22px;">
                        <div class="insight-box">
                            <h3>Strengths</h3>
                            <ul>
                                <li>The proposed methodology is robust and well-suited to the research question.</li>
                                <li>The literature review foundations are clearly articulated.</li>
                            </ul>
                        </div>
                        <div class="warning-panel">
                            <h3>Areas for Refinement</h3>
                            <ul>
                                <li>Consider narrowing the demographic scope in phase 2 to ensure timely completion.</li>
                                <li>Clarify the specific statistical tools intended for the quantitative analysis.</li>
                            </ul>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
@endsection
