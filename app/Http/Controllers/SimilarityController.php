<?php

namespace App\Http\Controllers;

use App\Services\SimilarityAnalyzer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SimilarityController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,docx', 'max:'.config('services.similarity.max_upload_kb', 51200)],
        ]);

        $previousPath = $request->session()->pull('similarity_pending_upload');
        if (is_string($previousPath)) {
            Storage::disk('local')->delete($previousPath);
        }

        $path = $validated['document']->store('similarity-uploads', 'local');
        $request->session()->put('similarity_pending_upload', $path);

        return response()->json([
            'uploaded' => true,
            'filename' => $validated['document']->getClientOriginalName(),
        ]);
    }

    public function analyze(Request $request, SimilarityAnalyzer $analyzer): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'problem' => ['nullable', 'string', 'max:10000'],
            'objectives' => ['nullable', 'string', 'max:10000'],
            'solution' => ['nullable', 'string', 'max:10000'],
            'target_users' => ['nullable', 'string', 'max:255'],
            'technologies' => ['nullable', 'string', 'max:255'],
            'document' => ['nullable', 'file', 'mimes:pdf,docx', 'max:'.config('services.similarity.max_upload_kb', 51200)],
        ]);

        $path = $request->session()->pull('similarity_pending_upload');
        if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
            $document = $request->file('document');
            $path = $document?->store('similarity-uploads', 'local');
        }

        if (! is_string($path)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Upload a document before starting the AI evaluation.',
                ], 422);
            }

            return back()->withInput()->withErrors(['document' => 'Upload a document before starting the AI evaluation.']);
        }

        set_time_limit((int) config('services.similarity.request_timeout', 600));

        try {
            $analysis = $analyzer->analyze(
                Storage::disk('local')->path($path),
                collect($validated)->except('document')->all(),
            );

            $proposal = collect($validated)->except('document')->all();

            if ($request->expectsJson()) {
                $request->session()->put('analysis', $analysis);
                $request->session()->put('proposal', $proposal);

                return response()->json([
                    'redirect' => route('similarity-result'),
                ]);
            }

            return redirect()
                ->route('similarity-result')
                ->with('analysis', $analysis)
                ->with('proposal', $proposal);
        } catch (Throwable $exception) {
            report($exception);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The document could not be analyzed locally. Please check the file and try again.',
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['document' => 'The document could not be analyzed locally. Please check the file and try again.']);
        } finally {
            Storage::disk('local')->delete($path);
        }
    }

    public function result(Request $request): View
    {
        return view('similarity-result', [
            'analysis' => $request->session()->get('analysis', []),
            'proposal' => $request->session()->get('proposal', []),
        ]);
    }
}
