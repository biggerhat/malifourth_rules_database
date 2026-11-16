<?php

use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the gaining grounds index for a published season without lazy loading', function () {
    $publisher = User::factory()->create(['name' => 'Publisher']);
    Season::factory()->published()->create(['title' => 'Older Season', 'published_at' => now()->subYear(), 'published_by' => $publisher->id]);
    Season::factory()->published()->create(['title' => 'Season One', 'published_by' => $publisher->id]);

    $this->get('/rules/gaining-grounds')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Rules/GainingGrounds/Index')
            ->where('season.title', 'Season One')
            ->where('season.published_by', 'Publisher')
        );
});
