<?php

namespace Tests\Feature;

use App\Models\Manuscript;
use App\Support\PdfPageRenderer;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManuscriptRepositoryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_repository_lists_imported_manuscripts(): void
    {
        $this->withoutVite();
        Manuscript::factory()->create([
            'title' => 'arXiv 2608.22036v1',
            'arxiv_id' => '2608.22036v1',
            'source_filename' => '2608.22036v1.pdf',
        ]);

        $this->get('/repository')
            ->assertSee('Manuscript Repository')
            ->assertSee('arXiv 2608.22036v1')
            ->assertSee('2608.22036v1.pdf');
    }

    public function test_repository_detail_renders_imported_manuscript(): void
    {
        $this->withoutVite();
        $manuscript = Manuscript::factory()->create([
            'title' => 'arXiv 2608.22081v1',
            'slug' => 'arxiv-2608-22081v1',
            'arxiv_id' => '2608.22081v1',
            'source_filename' => '2608.22081v1.pdf',
        ]);

        $this->get(route('repository.show', $manuscript))
            ->assertSee('arXiv 2608.22081v1')
            ->assertSee('Reference ID: 2608.22081v1')
            ->assertSee('2608.22081v1.pdf')
            ->assertSee('Open Viewer')
            ->assertDontSee('Download PDF');
    }

    public function test_manuscript_pdf_download_route_is_not_available(): void
    {
        $manuscript = Manuscript::factory()->create([
            'source_filename' => '2608.22036v1.pdf',
            'file_path' => 'manuscripts/2608.22036v1.pdf',
        ]);

        $this->get("/repository/{$manuscript->slug}/download")
            ->assertNotFound();
    }

    public function test_manuscript_viewer_renders_without_exposing_pdf_controls(): void
    {
        $this->withoutVite();
        $manuscript = Manuscript::factory()->create([
            'slug' => 'arxiv-2608-22036v1',
            'source_filename' => '2608.22036v1.pdf',
            'file_path' => 'manuscripts/2608.22036v1.pdf',
        ]);

        $this->get(route('repository.viewer', $manuscript))
            ->assertOk()
            ->assertSee('PDF Viewer')
            ->assertSee(route('repository.viewer.page', [$manuscript, 1]), false)
            ->assertDontSee('Download PDF')
            ->assertDontSee('<iframe', false)
            ->assertDontSee('<object', false)
            ->assertDontSee('<embed', false);
    }

    public function test_manuscript_viewer_page_serves_cached_page_image(): void
    {
        Storage::fake('local');
        $manuscript = Manuscript::factory()->create([
            'source_filename' => '2608.22036v1.pdf',
            'file_path' => 'manuscripts/2608.22036v1.pdf',
        ]);
        Storage::disk('local')->put(
            "manuscript-pages/{$manuscript->id}/page-1.png",
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );

        $this->get(route('repository.viewer.page', [$manuscript, 1]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_manuscript_viewer_renders_all_pages_in_one_scrollable_reader(): void
    {
        $this->withoutVite();
        $manuscript = Manuscript::factory()->create();
        $this->mock(PdfPageRenderer::class, function ($mock): void {
            $mock->shouldReceive('pageCount')->once()->andReturn(3);
        });

        $this->get(route('repository.viewer', $manuscript))
            ->assertOk()
            ->assertSee('3 pages')
            ->assertSee(route('repository.viewer.page', [$manuscript, 1]), false)
            ->assertSee(route('repository.viewer.page', [$manuscript, 2]), false)
            ->assertSee(route('repository.viewer.page', [$manuscript, 3]), false)
            ->assertSee('contextmenu', false)
            ->assertSee('loading="lazy"', false);
    }
}
