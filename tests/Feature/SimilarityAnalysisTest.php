<?php

namespace Tests\Feature;

use App\Models\Manuscript;
use App\Services\SimilarityAnalyzer;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SimilarityAnalysisTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_submit_idea_includes_the_similarity_loading_state(): void
    {
        $this->withoutVite();

        $this->get(route('submit-idea'))
            ->assertOk()
            ->assertSee('id="analysis-loading"', false)
            ->assertSee('Comparing your proposal with the repository')
            ->assertSee('Reviewing repository papers');
    }

    public function test_document_upload_finishes_before_evaluation_starts(): void
    {
        Storage::fake('local');

        $this->postJson(route('similarity.upload'), [
            'document' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
        ])
            ->assertOk()
            ->assertJson(['uploaded' => true, 'filename' => 'proposal.pdf']);

        $pendingPath = session('similarity_pending_upload');
        $this->assertIsString($pendingPath);
        Storage::disk('local')->assertExists($pendingPath);
    }

    public function test_uploaded_document_is_analyzed_and_results_are_rendered(): void
    {
        $this->withoutVite();
        Storage::fake('local');
        $manuscript = Manuscript::factory()->create();

        $this->mock(SimilarityAnalyzer::class, function ($mock) use ($manuscript): void {
            $mock->shouldReceive('analyze')->once()->andReturn([
                'score' => 78,
                'papers_analyzed' => 1,
                'matches' => [[
                    'slug' => $manuscript->slug,
                    'title' => $manuscript->title,
                    'authors' => $manuscript->authors,
                    'score' => 78,
                    'excerpt' => 'A matching research excerpt.',
                    'category' => 'Strong topical overlap',
                    'reason' => 'Both passages discuss semantic search and document embeddings.',
                    'shared_concepts' => ['semantic', 'embeddings'],
                    'source_excerpt' => 'The proposal uses semantic search for research documents.',
                    'source_page' => 'Page 2',
                    'reference_excerpt' => 'The manuscript uses document embeddings for semantic retrieval.',
                    'reference_page' => 'Page 4',
                ]],
            ]);
        });

        $this->post(route('similarity.analyze'), [
            'title' => 'My proposal',
            'document' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
        ])
            ->assertRedirect(route('similarity-result'));

        $this->get(route('similarity-result'))
            ->assertOk()
            ->assertSee('78% Match')
            ->assertSee('Strong topical overlap')
            ->assertSee($manuscript->title)
            ->assertSee('Why It Is Similar')
            ->assertSee('Both passages discuss semantic search and document embeddings.')
            ->assertSee('Your document, Page 2')
            ->assertSee('Repository manuscript, Page 4')
            ->assertSee('Similarity Review')
            ->assertDontSee('Novelty Score');
    }

    public function test_json_evaluation_returns_a_result_redirect(): void
    {
        Storage::fake('local');

        $this->mock(SimilarityAnalyzer::class, function ($mock): void {
            $mock->shouldReceive('analyze')->once()->andReturn([
                'score' => 42,
                'papers_analyzed' => 1,
                'matches' => [],
            ]);
        });

        $this->postJson(route('similarity.analyze'), [
            'document' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
        ])
            ->assertOk()
            ->assertJsonPath('redirect', route('similarity-result'))
            ->assertSessionHas('analysis.score', 42);
    }

    public function test_json_evaluation_returns_a_visible_error_when_analysis_fails(): void
    {
        Storage::fake('local');

        $this->mock(SimilarityAnalyzer::class, function ($mock): void {
            $mock->shouldReceive('analyze')->once()->andThrow(new \RuntimeException('Local model error'));
        });

        $this->postJson(route('similarity.analyze'), [
            'document' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
        ])
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'The document could not be analyzed locally. Please check the file and try again.',
            ]);
    }

    public function test_similarity_upload_requires_a_pdf_or_docx_document(): void
    {
        $this->post(route('similarity.analyze'), [])
            ->assertInvalid(['document']);
    }
}
