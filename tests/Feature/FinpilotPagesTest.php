<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FinpilotPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_render(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Pages/Home'));

        $this->get('/transactions')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Pages/Transactions'));

        $this->get('/debts')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Pages/Debts'));

        $this->get('/ai')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Pages/AI'));

        $this->get('/reports')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Pages/Reports'));

        $this->get('/settings')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Pages/Settings'));
    }
}
