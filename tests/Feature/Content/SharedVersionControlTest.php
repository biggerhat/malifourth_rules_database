<?php

use App\Models\Index;
use App\Models\Scheme;
use App\Models\Season;
use App\Models\SeasonPage;
use App\Models\Strategy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * These exercise UsesVersionControl::publish() directly against several of the models
 * that share the trait, to prove the approval gate and version-supersession behavior
 * are properties of the shared trait itself rather than coverage incidental to one model.
 */
dataset('versioned model classes', [
    'Season' => Season::class,
    'SeasonPage' => SeasonPage::class,
    'Strategy' => Strategy::class,
    'Scheme' => Scheme::class,
    'Index' => Index::class,
]);

it('throws when publishing a model that has no approval at all', function (string $modelClass) {
    $editor = User::factory()->create();
    $model = $modelClass::factory()->create();

    expect(fn () => $model->publish($editor))
        ->toThrow(Exception::class, $modelClass::NO_APPROVAL);
})->with('versioned model classes');

it('throws when publishing a model whose approval has not been approved', function (string $modelClass) {
    $editor = User::factory()->create();
    $model = $modelClass::factory()->create();
    $model->approval()->create(['initiated_by' => $editor->id]);

    expect(fn () => $model->publish($editor))
        ->toThrow(Exception::class, $modelClass::NO_APPROVAL);
})->with('versioned model classes');

it('publishes a model once its approval is approved, setting published_at and published_by', function (string $modelClass) {
    $editor = User::factory()->create();
    $model = $modelClass::factory()->create();
    $model->approval()->create(['initiated_by' => $editor->id, 'approved_at' => now(), 'approved_by' => $editor->id]);

    $model->publish($editor);
    $model->refresh();

    expect($model->published_at)->not->toBeNull();
    expect($model->published_by)->toBe($editor->id);
})->with('versioned model classes');

it('retires the previous version and repoints newest when a draft supersedes a published row', function (string $modelClass) {
    $editor = User::factory()->create();

    $original = $modelClass::factory()->create();
    $original->approval()->create(['initiated_by' => $editor->id, 'approved_at' => now(), 'approved_by' => $editor->id]);
    $original->publish($editor);
    $original->refresh();

    // A fresh model instance, mirroring how a real edit creates a brand-new draft row
    // linked back to the live version — not an in-memory replica of it.
    $draft = $modelClass::factory()->create([
        'previous' => $original->id,
        'original' => $original->id,
    ]);
    $draft->approval()->create(['initiated_by' => $editor->id, 'approved_at' => now(), 'approved_by' => $editor->id]);

    $draft->publish($editor);
    $draft->refresh();

    expect($draft->published_at)->not->toBeNull();
    expect($draft->newest)->toBeNull();

    $refetchedOriginal = $modelClass::withTrashed()->findOrFail($original->id);
    expect($refetchedOriginal->trashed())->toBeTrue();
    expect($refetchedOriginal->newest)->toBe($draft->id);
    expect($refetchedOriginal->fresh()->approval)->toBeNull();
})->with('versioned model classes');
