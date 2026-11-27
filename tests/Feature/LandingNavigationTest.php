<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingNavigationTest extends TestCase
{
    public function test_landing_page_renders_one_primary_navigation_with_a_mobile_toggle(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('aria-controls="landing-menu"', false)
            ->assertSee('data-theme-toggle aria-label="Mode gelap" aria-pressed="false"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('id="landing-menu"', false);

        $this->assertSame(1, substr_count($response->getContent(), 'aria-label="Navigasi utama"'));
    }
}
