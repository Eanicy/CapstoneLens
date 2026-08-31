<?php

namespace App\Services;

use App\Models\Manuscript;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class SimilarityAnalyzer
{
    /**
     * @param  array<string, mixed>  $proposal
     * @return array{score: int, matches: array<int, array<string, mixed>>, papers_analyzed: int}
     */
    public function analyze(string $documentPath, array $proposal = []): array
    {
        $input = json_encode([
            'document_path' => $documentPath,
            'proposal' => $proposal,
            'cache_path' => storage_path('app/private/similarity/corpus-cache.json'),
            'manuscripts' => Manuscript::query()
                ->get(['id', 'slug', 'title', 'authors', 'abstract', 'file_path', 'checksum'])
                ->map(fn (Manuscript $manuscript): array => [
                    'id' => $manuscript->id,
                    'slug' => $manuscript->slug,
                    'title' => $manuscript->title,
                    'authors' => $manuscript->authors,
                    'abstract' => $manuscript->abstract,
                    'file_path' => storage_path('app/private/'.$manuscript->file_path),
                    'checksum' => $manuscript->checksum,
                ])->all(),
        ], JSON_THROW_ON_ERROR);

        $configuredPython = config('services.similarity.python', 'python');
        $bundledWindowsPython = 'C:/Python314/python.exe';
        $python = $configuredPython === 'python' && is_file($bundledWindowsPython)
            ? $bundledWindowsPython
            : $configuredPython;
        $pythonPath = config('services.similarity.python_path');
        $environment = getenv();
        $environment = is_array($environment) ? $environment : [];
        if (is_string($pythonPath) && $pythonPath !== '') {
            $environment['PYTHONPATH'] = $pythonPath;
        }
        $environment['SIMILARITY_RERANKER'] = config('services.similarity.reranker');

        $result = Process::path(base_path())
            ->timeout((int) config('services.similarity.timeout', 600))
            ->input($input)
            ->env($environment)
            ->run([$python, base_path('scripts/similarity.py')]);

        if ($result->failed()) {
            throw new RuntimeException(trim($result->errorOutput() ?: $result->output()));
        }

        $analysis = json_decode($result->output(), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($analysis) || ! isset($analysis['matches'])) {
            throw new RuntimeException('The similarity runner returned an invalid response.');
        }

        return $analysis;
    }
}
