<?php

namespace App\Support;

use App\Models\Manuscript;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class PdfPageRenderer
{
    public function render(Manuscript $manuscript, int $page): string
    {
        if ($page < 1) {
            throw new RuntimeException('PDF page numbers start at 1.');
        }

        $cachePath = $this->cachePath($manuscript, $page);

        if (Storage::disk('local')->exists($cachePath)) {
            return $cachePath;
        }

        if (! Storage::disk('local')->exists($manuscript->file_path)) {
            throw new RuntimeException('The source PDF could not be found.');
        }

        $targetPath = Storage::disk('local')->path($cachePath);
        $outputPrefix = mb_substr($targetPath, 0, -4);

        File::ensureDirectoryExists(dirname($targetPath));

        $process = new Process([
            $this->binary('pdftoppm'),
            '-png',
            '-f',
            (string) $page,
            '-l',
            (string) $page,
            '-singlefile',
            '-r',
            '140',
            Storage::disk('local')->path($manuscript->file_path),
            $outputPrefix,
        ]);
        $process->setTimeout(90);
        $process->run();

        if (! $process->isSuccessful() || ! File::exists($targetPath)) {
            throw new RuntimeException('The PDF page could not be rendered.');
        }

        return $cachePath;
    }

    public function pageCount(Manuscript $manuscript): ?int
    {
        if (! Storage::disk('local')->exists($manuscript->file_path)) {
            return null;
        }

        try {
            $process = new Process([
                $this->binary('pdfinfo'),
                Storage::disk('local')->path($manuscript->file_path),
            ]);
        } catch (RuntimeException) {
            return null;
        }

        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        preg_match('/^Pages:\s+(\d+)\s*$/mi', $process->getOutput(), $matches);

        return isset($matches[1]) ? (int) $matches[1] : null;
    }

    private function cachePath(Manuscript $manuscript, int $page): string
    {
        return "manuscript-pages/{$manuscript->id}/page-{$page}.png";
    }

    private function binary(string $name): string
    {
        $configured = config("services.poppler.{$name}");

        if (is_string($configured) && $configured !== '' && File::exists($configured)) {
            return $configured;
        }

        $pathBinary = (new ExecutableFinder)->find($name);

        if (is_string($pathBinary)) {
            return $pathBinary;
        }

        foreach ($this->bundledBinaryCandidates($name) as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException("The {$name} binary could not be found.");
    }

    /**
     * @return array<int, string>
     */
    private function bundledBinaryCandidates(string $name): array
    {
        $home = $_SERVER['USERPROFILE']
            ?? getenv('USERPROFILE')
            ?: ($_SERVER['HOME'] ?? getenv('HOME') ?: null);

        if (! is_string($home) || $home === '') {
            return [];
        }

        $popplerRoot = $home.'\\.cache\\codex-runtimes\\codex-primary-runtime\\dependencies\\native\\poppler';

        return [
            "{$popplerRoot}\\Library\\bin\\{$name}.exe",
            "{$popplerRoot}\\bin\\{$name}.cmd",
        ];
    }
}
