@extends('layouts.app', ['title' => 'Student Dashboard', 'console' => 'Student Portal'])

@section('content')
    <section class="page-wide">
        <header class="page-header">
            <div>
                <h1 class="page-title">Welcome back, Alex.</h1>
                <p class="page-subtitle">Ready to make progress on your thesis today?</p>
            </div>
            <div class="actions">
                <button class="button" type="button" aria-label="Notifications">
                    <span class="material-symbols-outlined">notifications</span>
                </button>
                <div class="avatar" style="width: 46px; height: 46px; border-width: 2px; font-size: 16px;">AM</div>
            </div>
        </header>

        <div class="dashboard-grid">
            <a class="hero-action card-pad" href="{{ route('submit-idea') }}">
                <div class="icon-tile">
                    <span class="material-symbols-outlined">add_circle</span>
                </div>
                <h2>Submit New Idea</h2>
                <p>Start the validation process by submitting your capstone or thesis proposal for feasibility and similarity checks.</p>
                <span class="arrow-link">Get Started <span class="material-symbols-outlined">arrow_forward</span></span>
            </a>

            <article class="quick-card card card-pad">
                <div>
                    <div class="icon-tile" style="background: var(--primary-soft); color: var(--primary);">
                        <span class="material-symbols-outlined">search</span>
                    </div>
                    <h2 style="margin-top: 34px;">Search Repository</h2>
                    <p>Browse approved manuscripts to find inspiration and ensure your topic is unique.</p>
                </div>
                <a class="button" href="{{ route('repository.index') }}">Browse Papers <span class="material-symbols-outlined">open_in_new</span></a>
            </article>
        </div>

        <div class="status-grid">
            <article class="card card-pad">
                <div class="page-header" style="margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid var(--outline);">
                    <h2 class="section-title">Recent Submissions</h2>
                    <a class="button button-outline" href="{{ route('submit-idea') }}">View All</a>
                </div>

                <div class="list-stack">
                    <div class="submission-row">
                        <div>
                            <h4>Predictive Modeling in Urban Transit</h4>
                            <p style="margin: 0;">Submitted Oct 12, 2023</p>
                        </div>
                        <span class="badge badge-warning">In Review</span>
                    </div>
                    <div class="submission-row">
                        <div>
                            <h4>Blockchain for Academic Credentials</h4>
                            <p style="margin: 0;">Submitted Sep 28, 2023</p>
                        </div>
                        <span class="badge">Draft</span>
                    </div>
                </div>
            </article>

            <article class="card card-pad">
                <h2 class="section-title" style="padding-bottom: 18px; border-bottom: 1px solid var(--outline);">Thesis Progress</h2>
                <div style="margin-top: 34px;">
                    <div class="metric-head">
                        <span>Phase 1: Proposal Validation</span>
                        <span class="score" style="font-size: 28px;">35%</span>
                    </div>
                    <div class="progress-track" style="margin-top: 12px;">
                        <span class="progress-bar" style="width: 35%;"></span>
                    </div>
                    <ul class="check-list">
                        <li><span class="material-symbols-outlined" style="color: var(--primary);">check_circle</span>Topic Selection</li>
                        <li><span class="material-symbols-outlined" style="color: var(--primary);">check_circle</span>Initial Literature Review</li>
                        <li><span class="material-symbols-outlined">radio_button_unchecked</span>Feasibility Approval</li>
                        <li><span class="material-symbols-outlined">radio_button_unchecked</span>Similarity Clearance</li>
                    </ul>
                </div>
            </article>
        </div>
    </section>
@endsection
