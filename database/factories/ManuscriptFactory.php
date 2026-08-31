<?php

namespace Database\Factories;

use App\Models\Manuscript;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Manuscript>
 */
class ManuscriptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arxivId = fake()->numerify('2608.#####v1');
        $title = 'arXiv '.$arxivId;

        return [
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'arxiv_id' => $arxivId,
            'authors' => 'Imported Research Corpus',
            'published_year' => 2026,
            'abstract' => 'Imported PDF awaiting metadata enrichment.',
            'keywords' => ['Computer Science', 'Imported PDF'],
            'source_filename' => $arxivId.'.pdf',
            'file_path' => 'manuscripts/'.$arxivId.'.pdf',
            'checksum' => hash('sha256', fake()->uuid()),
            'file_size' => fake()->numberBetween(50_000, 8_000_000),
            'imported_at' => now(),
        ];
    }
}
