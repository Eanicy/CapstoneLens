<?php

namespace Tests\Feature;

use App\Models\Manuscript;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportManuscriptsCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('framework/testing/manuscript-fixtures'));

        parent::tearDown();
    }

    public function test_imports_pdf_files_from_a_folder_into_private_storage(): void
    {
        Storage::fake('local');
        $sourceDirectory = $this->freshSourceDirectory();
        File::ensureDirectoryExists($sourceDirectory);
        File::put($sourceDirectory.DIRECTORY_SEPARATOR.'2608.22036v1.pdf', '%PDF-1.4 sample one');
        File::put($sourceDirectory.DIRECTORY_SEPARATOR.'notes.txt', 'ignore me');

        $this->artisan('manuscripts:import', ['path' => $sourceDirectory])
            ->assertSuccessful();

        $manuscript = Manuscript::firstOrFail();

        $this->assertSame('2608.22036v1', $manuscript->arxiv_id);
        $this->assertSame('arXiv 2608.22036v1', $manuscript->title);
        $this->assertSame('2608.22036v1.pdf', $manuscript->source_filename);
        Storage::disk('local')->assertExists($manuscript->file_path);
        $this->assertDatabaseCount('manuscripts', 1);
    }

    public function test_import_skips_previously_imported_pdf_checksums(): void
    {
        Storage::fake('local');
        $sourceDirectory = $this->freshSourceDirectory();
        File::ensureDirectoryExists($sourceDirectory);
        File::put($sourceDirectory.DIRECTORY_SEPARATOR.'2608.22081v1.pdf', '%PDF-1.4 repeatable');

        $this->artisan('manuscripts:import', ['path' => $sourceDirectory])
            ->assertSuccessful();
        $this->artisan('manuscripts:import', ['path' => $sourceDirectory])
            ->assertSuccessful();

        $this->assertDatabaseCount('manuscripts', 1);
    }

    private function freshSourceDirectory(): string
    {
        $sourceDirectory = storage_path('framework/testing/manuscript-fixtures');

        File::deleteDirectory($sourceDirectory);
        File::ensureDirectoryExists($sourceDirectory);

        return $sourceDirectory;
    }
}
