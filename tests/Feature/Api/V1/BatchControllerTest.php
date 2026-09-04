<?php

use App\Models\Batch;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only public, published batches', function () {
    $creator = User::factory()->create();
    Batch::factory()->create(['is_public' => true, 'published_at' => now(), 'title' => 'Public Batch', 'created_by' => $creator->id]);
    Batch::factory()->create(['is_public' => false, 'published_at' => now(), 'title' => 'Private Batch', 'created_by' => $creator->id]);
    Batch::factory()->create(['is_public' => true, 'published_at' => null, 'title' => 'Unpublished Batch', 'created_by' => $creator->id]);

    $response = $this->getJson('/api/v1/batches');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('Public Batch');
});

it('returns a single public batch with its published content', function () {
    $creator = User::factory()->create();
    $batch = Batch::factory()->create(['is_public' => true, 'published_at' => now(), 'title' => 'Batch', 'created_by' => $creator->id]);
    Page::factory()->published()->create(['batch_id' => $batch->id]);
    Page::factory()->create(['batch_id' => $batch->id]);

    $response = $this->getJson("/api/v1/batches/{$batch->slug}");

    $response->assertOk();
    expect($response->json('data.contents.pages'))->toHaveCount(1);
});

it('returns 404 for a private batch', function () {
    $creator = User::factory()->create();
    $batch = Batch::factory()->create(['is_public' => false, 'published_at' => now(), 'title' => 'Batch', 'created_by' => $creator->id]);

    $this->getJson("/api/v1/batches/{$batch->slug}")->assertStatus(404);
});

it('returns 404 for an unpublished batch', function () {
    $creator = User::factory()->create();
    $batch = Batch::factory()->create(['is_public' => true, 'published_at' => null, 'title' => 'Batch', 'created_by' => $creator->id]);

    $this->getJson("/api/v1/batches/{$batch->slug}")->assertStatus(404);
});
