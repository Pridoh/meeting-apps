<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthPageTest extends TestCase
{
    public function test_landing_navigation_links_to_both_auth_pages(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('href="'.route('register').'"', false);
    }

    public function test_auth_pages_use_the_shared_theme_and_link_back_home(): void
    {
        foreach (['login', 'register'] as $page) {
            $this->get(route($page))->assertOk()
                ->assertSee("const storageKey = 'rapatin-theme';", false)
                ->assertSee('data-theme-toggle aria-label="Mode gelap"', false)
                ->assertSee('href="'.route('home').'"', false);
        }
    }
}
