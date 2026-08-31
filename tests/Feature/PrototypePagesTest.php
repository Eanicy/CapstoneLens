<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PrototypePagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_prototype_pages_render_stitch_screen_content(): void
    {
        $this->withoutVite();

        $pages = [
            '/' => 'Welcome back, Alex.',
            '/submit-idea' => 'Submit Capstone Idea',
            '/feasibility-result' => 'Feasibility Analysis',
            '/similarity-result' => 'AI Similarity Analysis',
            '/profile' => 'Student Profile',
            '/repository' => 'Manuscript Repository',
        ];

        foreach ($pages as $uri => $expectedText) {
            $this->get($uri)->assertOk()->assertSee($expectedText);
        }
    }

    public function test_login_and_registration_routes_are_not_part_of_this_prototype(): void
    {
        $this->withoutVite();

        $this->get('/login')->assertNotFound();
        $this->get('/register')->assertNotFound();
        $this->get('/')->assertDontSee('Logout');
    }
}
