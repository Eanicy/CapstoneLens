@extends('layouts.app', ['title' => 'Student Profile', 'console' => 'Student Portal'])

@section('content')
    <section class="page profile-grid">
        <header class="page-header">
            <div>
                <h1 class="page-title">Student Profile</h1>
                <p class="page-subtitle">Manage your personal information and account settings.</p>
            </div>
        </header>

        <article class="card card-pad profile-card">
            <div class="profile-hero">
                <div class="avatar">AM</div>
                <div>
                    <h2 style="font-size: 28px;">Alex Mercer</h2>
                    <p style="margin: 8px 0 14px; font-size: 20px;">Computer Science - 4th Year</p>
                    <span class="badge">Active Student</span>
                </div>
            </div>

            <hr class="rule">

            <dl class="detail-list">
                <div>
                    <dt class="meta-label">Student ID</dt>
                    <dd><p>2021-04958</p></dd>
                </div>
                <div>
                    <dt class="meta-label">Email Address</dt>
                    <dd><p>alex.mercer@university.edu</p></dd>
                </div>
                <div>
                    <dt class="meta-label">Course / Program</dt>
                    <dd><p>Bachelor of Science in Computer Science</p></dd>
                </div>
                <div>
                    <dt class="meta-label">Year Level</dt>
                    <dd><p>4th Year</p></dd>
                </div>
                <div>
                    <dt class="meta-label">Section</dt>
                    <dd><p>CS4-A</p></dd>
                </div>
                <div>
                    <dt class="meta-label">Adviser</dt>
                    <dd><p>Dr. Sarah Jenkins</p></dd>
                </div>
            </dl>

            <hr class="rule">

            <div class="actions">
                <button class="button button-primary" type="button" style="flex: 1;">
                    <span class="material-symbols-outlined">edit</span>
                    Edit Profile
                </button>
                <button class="button button-outline" type="button" style="flex: 1;">
                    <span class="material-symbols-outlined">lock</span>
                    Change Password
                </button>
                <button class="button button-danger" type="button">
                    <span class="material-symbols-outlined">logout</span>
                    Logout
                </button>
            </div>
        </article>
    </section>
@endsection
