@extends('layouts.app', ['title' => 'Submit Capstone Idea', 'console' => 'Student Console'])

@section('content')
    <section class="page-wide">
        <header class="page-header">
            <div>
                <h1 class="page-title">Submit Capstone Idea</h1>
                <p class="page-subtitle">Detail your research proposal for AI preliminary evaluation.</p>
            </div>
        </header>

        <form id="similarity-form" class="card card-pad form-card" action="{{ route('similarity.analyze') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="form-grid">
                <div class="field full">
                    <label for="title">Project Title</label>
                    <input id="title" name="title" type="text" placeholder="Enter a descriptive title for your research">
                </div>
                <div class="field full">
                    <label for="problem">Problem Statement</label>
                    <textarea id="problem" name="problem" placeholder="Describe the specific problem your capstone aims to solve..."></textarea>
                </div>
                <div class="field">
                    <label for="objectives">Key Objectives</label>
                    <textarea id="objectives" name="objectives" placeholder="1.&#10;2.&#10;3."></textarea>
                </div>
                <div class="field">
                    <label for="solution">Proposed Solution</label>
                    <textarea id="solution" name="solution" placeholder="Outline how you intend to solve the stated problem..."></textarea>
                </div>
                <div class="field">
                    <label for="target_users">Target Users / Demographics</label>
                    <input id="target_users" name="target_users" type="text" placeholder="e.g., University Students, Local Businesses">
                </div>
                <div class="field">
                    <label for="technologies">Proposed Technologies</label>
                    <input id="technologies" name="technologies" type="text" placeholder="e.g., React, Python, TensorFlow">
                </div>
            </div>

            <hr class="rule">

            <section>
                <h2 class="section-title">Supporting Materials</h2>
                <div class="upload-grid" style="margin-top: 24px;">
                    <div class="upload-panel">
                        <div>
                            <span class="material-symbols-outlined" style="color: var(--primary); font-size: 54px;">mic</span>
                            <h3>Audio Pitch</h3>
                            <p>Record a short 2-minute elevator pitch for your idea.</p>
                            <div class="actions" style="justify-content: center;">
                                <button class="button" type="button"><span class="material-symbols-outlined">mic</span>Record</button>
                                <button class="button" type="button"><span class="material-symbols-outlined">upload</span>Upload Audio</button>
                            </div>
                        </div>
                    </div>
                    <div class="upload-panel">
                        <div>
                            <span class="material-symbols-outlined" style="font-size: 54px;">upload_file</span>
                            <h3>Upload Document</h3>
                            <p>Select a PDF or DOCX to upload it before starting the AI evaluation.</p>
                            <label class="button" for="document"><span class="material-symbols-outlined">upload</span>Browse Files</label>
                            <input id="document" name="document" type="file" accept=".pdf,.docx" required hidden>
                            <div id="document-status" class="document-status" hidden aria-live="polite">
                                <div class="document-status-heading">
                                    <span class="material-symbols-outlined">description</span>
                                    <div>
                                        <strong id="document-name">No document selected</strong>
                                        <span id="document-size" class="document-size"></span>
                                    </div>
                                    <span id="document-percent" class="document-percent">0%</span>
                                </div>
                                <div class="progress-track" role="progressbar" aria-label="Document upload progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                    <span id="document-progress" class="progress-bar"></span>
                                </div>
                                <p id="document-message" class="document-message">Ready to upload</p>
                            </div>
                            @error('document')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            <hr class="rule">

            <div class="page-header" style="margin: 0;">
                <button class="button" type="reset">Clear Form</button>
                <div class="actions">
                    <button class="button button-outline" type="button">Save Draft</button>
                    <button class="button button-primary" type="submit" disabled>
                        <span class="material-symbols-outlined">psychology</span>
                        Submit for AI Evaluation
                    </button>
                </div>
            </div>
        </form>
    </section>

    <section id="analysis-loading" class="analysis-loading" hidden aria-live="assertive" aria-busy="true" role="status">
        <div class="analysis-loading-panel">
            <div class="analysis-loader" aria-hidden="true">
                <span class="material-symbols-outlined">psychology</span>
            </div>
            <p class="eyebrow">Local AI Evaluation</p>
            <h2>Comparing your proposal with the repository</h2>
            <p id="analysis-loading-message" class="analysis-loading-message">Reading your uploaded document...</p>
            <div class="analysis-stage-list" aria-label="Evaluation progress">
                <div class="analysis-stage is-active" data-stage="0">
                    <span class="material-symbols-outlined">description</span>
                    <span>Reading proposal</span>
                </div>
                <div class="analysis-stage" data-stage="1">
                    <span class="material-symbols-outlined">memory</span>
                    <span>Preparing semantic comparison</span>
                </div>
                <div class="analysis-stage" data-stage="2">
                    <span class="material-symbols-outlined">library_books</span>
                    <span>Reviewing repository papers</span>
                </div>
                <div class="analysis-stage" data-stage="3">
                    <span class="material-symbols-outlined">format_list_numbered</span>
                    <span>Ranking closest matches</span>
                </div>
            </div>
            <p class="analysis-loading-note">The first comparison can take a few minutes while the local model prepares the repository.</p>
        </div>
    </section>

    <script>
        (() => {
            const form = document.getElementById('similarity-form');
            const input = document.getElementById('document');
            const status = document.getElementById('document-status');
            const name = document.getElementById('document-name');
            const size = document.getElementById('document-size');
            const percent = document.getElementById('document-percent');
            const progress = document.getElementById('document-progress');
            const track = status.querySelector('[role="progressbar"]');
            const message = document.getElementById('document-message');
            const submit = form.querySelector('button[type="submit"]');
            const loading = document.getElementById('analysis-loading');
            const loadingMessage = document.getElementById('analysis-loading-message');
            const loadingStages = [...loading.querySelectorAll('.analysis-stage')];
            let loadingTimer;

            const formatBytes = (bytes) => {
                if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
                return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
            };

            const setProgress = (value) => {
                const bounded = Math.max(0, Math.min(100, Math.round(value)));
                progress.style.width = `${bounded}%`;
                percent.textContent = `${bounded}%`;
                track.setAttribute('aria-valuenow', bounded);
            };

            let isUploading = false;
            let documentUploaded = false;

            const showAnalysisLoading = () => {
                const messages = [
                    'Reading your uploaded document...',
                    'Preparing semantic comparison...',
                    'Reviewing repository papers...',
                    'Ranking the closest matches...',
                ];
                let activeStage = 0;

                const renderStage = () => {
                    loadingMessage.textContent = messages[activeStage];
                    loadingStages.forEach((stage, index) => {
                        stage.classList.toggle('is-active', index === activeStage);
                        stage.classList.toggle('is-complete', index < activeStage);
                    });
                };

                renderStage();
                loading.hidden = false;
                document.body.classList.add('is-analysis-loading');
                loadingTimer = window.setInterval(() => {
                    activeStage = Math.min(activeStage + 1, messages.length - 1);
                    renderStage();
                }, 4500);
            };

            const hideAnalysisLoading = () => {
                window.clearInterval(loadingTimer);
                loading.hidden = true;
                document.body.classList.remove('is-analysis-loading');
            };

            const startUpload = () => {
                if (isUploading || !input.files.length) return;

                isUploading = true;
                status.hidden = false;
                submit.disabled = true;
                message.textContent = 'Uploading document...';
                setProgress(0);

                const request = new XMLHttpRequest();
                request.open('POST', '{{ route('similarity.upload') }}');
                request.upload.addEventListener('progress', (progressEvent) => {
                    if (!progressEvent.lengthComputable) return;
                    setProgress((progressEvent.loaded / progressEvent.total) * 100);
                });
                request.addEventListener('load', () => {
                    if (request.status >= 200 && request.status < 400) {
                        setProgress(100);
                        isUploading = false;
                        documentUploaded = true;
                        submit.disabled = false;
                        message.textContent = 'Document uploaded. Ready for AI evaluation.';
                        return;
                    }

                    isUploading = false;
                    submit.disabled = false;
                    message.textContent = 'The upload could not be completed. Please try again.';
                });
                request.addEventListener('error', () => {
                    isUploading = false;
                    submit.disabled = false;
                    message.textContent = 'The upload could not be completed. Please check your connection and try again.';
                });
                const uploadData = new FormData();
                uploadData.append('document', input.files[0]);
                uploadData.append('_token', form.querySelector('input[name="_token"]').value);
                request.send(uploadData);
            };

            input.addEventListener('change', () => {
                const file = input.files[0];
                if (!file) {
                    status.hidden = true;
                    return;
                }

                status.hidden = false;
                documentUploaded = false;
                name.textContent = file.name;
                size.textContent = formatBytes(file.size);
                message.textContent = 'Starting upload...';
                setProgress(0);
                startUpload();
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                if (!input.files.length) {
                    status.hidden = false;
                    message.textContent = 'Choose a PDF or DOCX document before submitting.';
                    return;
                }
                if (!documentUploaded) {
                    status.hidden = false;
                    message.textContent = 'Wait for the document upload to finish.';
                    return;
                }

                isUploading = true;
                submit.disabled = true;
                message.textContent = 'Starting AI evaluation...';
                showAnalysisLoading();

                const request = new XMLHttpRequest();
                request.open('POST', form.action);
                request.setRequestHeader('Accept', 'application/json');
                request.addEventListener('load', () => {
                    let response = {};
                    try {
                        response = JSON.parse(request.responseText);
                    } catch (_) {
                        // The API should always return JSON, but retain a safe fallback for an interrupted response.
                    }

                    if (request.status >= 200 && request.status < 300 && response.redirect) {
                        setProgress(100);
                        message.textContent = 'Analysis complete. Opening results...';
                        window.location.assign(response.redirect);
                        return;
                    }

                    isUploading = false;
                    submit.disabled = false;
                    hideAnalysisLoading();
                    message.textContent = response.message || 'The AI evaluation could not be completed. Please try again.';
                });
                request.addEventListener('error', () => {
                    isUploading = false;
                    submit.disabled = false;
                    hideAnalysisLoading();
                    message.textContent = 'The AI evaluation could not be completed. Please try again.';
                });
                const evaluationData = new FormData(form);
                evaluationData.delete('document');
                request.send(evaluationData);
            });
        })();
    </script>
@endsection
