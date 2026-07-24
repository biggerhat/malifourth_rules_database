<?php

use App\Models\Errata;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders a published errata item with flat top-level props', function () {
    $publisher = User::factory()->create(['name' => 'Jane Editor']);
    $errata = Errata::factory()->create([
        'title' => 'Line of Sight Clarification',
        'content' => 'Some clarified rules text.',
        'published_at' => now(),
        'published_by' => $publisher->id,
    ]);

    $response = $this->get(route('errata.view', $errata));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('title', 'Line of Sight Clarification')
        ->where('slug', $errata->slug)
        ->where('published_by', 'Jane Editor')
        ->where('content.0.text', 'Some clarified rules text.')
    );
});

it('returns 404 for an unpublished errata item', function () {
    $errata = Errata::factory()->create();

    $this->get(route('errata.view', $errata))->assertStatus(404);
});

it('redirects the history route to the live item once it is the newest version', function () {
    $errata = Errata::factory()->published()->create();

    $this->get(route('errata.history', $errata))->assertRedirect(route('errata.view', $errata->slug));
});
