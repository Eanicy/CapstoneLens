<?php

namespace App\Console\Commands;

use App\Models\Manuscript;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SplFileInfo;

#[Signature('manuscripts:import {path? : Folder containing PDF files}')]
#[Description('Import local PDF papers into the manuscript repository')]
class ImportManuscripts extends Command
{
    public function handle(): int
    {
        $path = $this->argument('path') ?? base_path('../computer_science_pdfs');

        if (! is_string($path) || ! File::isDirectory($path)) {
            $this->error('PDF source folder was not found: '.$path);

            return self::FAILURE;
        }

        $imported = 0;
        $skipped = 0;

        foreach (File::files($path) as $file) {
            if (Str::lower($file->getExtension()) !== 'pdf') {
                continue;
            }

            $checksum = hash_file('sha256', $file->getPathname());

            if ($checksum === false) {
                $this->warn('Could not checksum '.$file->getFilename().'; skipping.');

                continue;
            }

            if (Manuscript::where('checksum', $checksum)->exists()) {
                $skipped++;

                continue;
            }

            $filename = $file->getFilename();
            $destination = 'manuscripts/'.$filename;

            Storage::disk('local')->makeDirectory('manuscripts');
            File::copy($file->getPathname(), Storage::disk('local')->path($destination));

            Manuscript::create([
                'title' => $this->titleFromFile($file),
                'slug' => $this->uniqueSlug($file),
                'arxiv_id' => $this->arxivIdFromFile($file),
                'authors' => 'Imported Research Corpus',
                'published_year' => $this->publishedYearFromFile($file),
                'abstract' => 'Imported PDF awaiting metadata enrichment.',
                'keywords' => ['Computer Science', 'Imported PDF'],
                'source_filename' => $filename,
                'file_path' => $destination,
                'checksum' => $checksum,
                'file_size' => $file->getSize(),
                'imported_at' => now(),
            ]);

            $imported++;
        }

        $this->info("Imported {$imported} manuscript(s); skipped {$skipped} duplicate(s).");

        return self::SUCCESS;
    }

    private function arxivIdFromFile(SplFileInfo $file): string
    {
        return pathinfo($file->getFilename(), PATHINFO_FILENAME);
    }

    private function titleFromFile(SplFileInfo $file): string
    {
        return 'arXiv '.$this->arxivIdFromFile($file);
    }

    private function publishedYearFromFile(SplFileInfo $file): int
    {
        $prefix = Str::substr($this->arxivIdFromFile($file), 0, 2);

        return 2000 + (int) $prefix;
    }

    private function uniqueSlug(SplFileInfo $file): string
    {
        $baseSlug = Str::slug($this->titleFromFile($file));
        $slug = $baseSlug;
        $suffix = 2;

        while (Manuscript::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
