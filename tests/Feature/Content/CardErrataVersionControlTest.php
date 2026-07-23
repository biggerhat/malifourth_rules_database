<?php

use App\Models\CardErrata;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
    $role->syncPermissions(Permission::all());

    $this->editor = User::factory()->create();
    $this->editor->assignRole($role);

    $this->cardAttributes = [
        'faction' => 'guild',
        'card_name' => 'Lucius Mattheson',
        'internal_notes' => null,
        'change_notes' => 'Initial errata',
        'batch_id' => null,
        'publish_directly' => false,
        'approve_directly' => false,
        'entries' => [
            [
                'what_changed' => 'Reduced defense',
                'what_it_was' => 'Df 5',
                'what_it_is_now' => 'Df 4',
            ],
        ],
    ];
});

it('will not publish a card errata that has no approved approval', function () {
    $this->actingAs($this->editor)->post(route('admin.card-errata.store'), $this->cardAttributes)->assertRedirect();
    $card = CardErrata::latest('id')->firstOrFail();

    expect($card->published_at)->toBeNull();

    $this->actingAs($this->editor)->post(route('admin.card-errata.publish', $card))->assertRedirect();

    $card->refresh();
    expect($card->published_at)->toBeNull();
});

it('creates entries alongside the card and computes searchable_text from them', function () {
    $this->actingAs($this->editor)->post(route('admin.card-errata.store'), $this->cardAttributes)->assertRedirect();
    $card = CardErrata::with('entries')->latest('id')->firstOrFail();

    expect($card->entries)->toHaveCount(1);
    expect($card->entries->first()->what_changed)->toBe('Reduced defense');
    expect($card->searchable_text)->toContain('Lucius Mattheson')->toContain('Reduced defense');
});

it('publishes a card errata once its approval is approved', function () {
    $this->actingAs($this->editor)->post(route('admin.card-errata.store'), $this->cardAttributes);
    $card = CardErrata::latest('id')->firstOrFail();
    $card->approval->update(['approved_at' => now(), 'approved_by' => $this->editor->id]);

    $this->actingAs($this->editor)->post(route('admin.card-errata.publish', $card))->assertRedirect();

    $card->refresh();
    expect($card->published_at)->not->toBeNull();
});

it('creates a new draft row with fresh entries when editing a published card, leaving the live version untouched', function () {
    $this->actingAs($this->editor)->post(route('admin.card-errata.store'), $this->cardAttributes);
    $card = CardErrata::latest('id')->firstOrFail();
    $card->approval->update(['approved_at' => now(), 'approved_by' => $this->editor->id]);
    $this->actingAs($this->editor)->post(route('admin.card-errata.publish', $card));
    $card->refresh();

    $originalEntryId = $card->entries()->first()->id;

    $updatedAttributes = $this->cardAttributes;
    $updatedAttributes['entries'] = [
        ['what_changed' => 'Reduced defense', 'what_it_was' => 'Df 5', 'what_it_is_now' => 'Df 4'],
        ['what_changed' => 'Reduced willpower', 'what_it_was' => 'Wp 6', 'what_it_is_now' => 'Wp 5'],
    ];
    $updatedAttributes['change_notes'] = 'Second wave of nerfs';

    $this->actingAs($this->editor)->post(route('admin.card-errata.update', $card), $updatedAttributes)->assertRedirect();

    $draft = CardErrata::with('entries')->where('id', '!=', $card->id)->latest('id')->firstOrFail();

    expect($draft->previous)->toBe($card->id);
    expect($draft->original)->toBe($card->id);
    expect($draft->published_at)->toBeNull();
    expect($draft->entries)->toHaveCount(2);

    // Live version is untouched: still one entry, same row.
    $card->refresh();
    expect($card->entries()->count())->toBe(1);
    expect($card->entries()->first()->id)->toBe($originalEntryId);
    expect($card->trashed())->toBeFalse();
});

it('retires the previous version and preserves its entries for history when a draft is published', function () {
    $this->actingAs($this->editor)->post(route('admin.card-errata.store'), $this->cardAttributes);
    $card = CardErrata::latest('id')->firstOrFail();
    $card->approval->update(['approved_at' => now(), 'approved_by' => $this->editor->id]);
    $this->actingAs($this->editor)->post(route('admin.card-errata.publish', $card));
    $card->refresh();
    $originalId = $card->id;

    $updatedAttributes = $this->cardAttributes;
    $updatedAttributes['entries'] = [
        ['what_changed' => 'Reduced defense', 'what_it_was' => 'Df 5', 'what_it_is_now' => 'Df 4'],
        ['what_changed' => 'Reduced willpower', 'what_it_was' => 'Wp 6', 'what_it_is_now' => 'Wp 5'],
    ];

    $this->actingAs($this->editor)->post(route('admin.card-errata.update', $card), $updatedAttributes);

    $draft = CardErrata::where('id', '!=', $originalId)->latest('id')->firstOrFail();
    $draft->approval->update(['approved_at' => now(), 'approved_by' => $this->editor->id]);

    $this->actingAs($this->editor)->post(route('admin.card-errata.publish', $draft))->assertRedirect();

    $draft->refresh();
    expect($draft->published_at)->not->toBeNull();

    $original = CardErrata::withTrashed()->with('entries')->findOrFail($originalId);
    expect($original->trashed())->toBeTrue();
    expect($original->newest)->toBe($draft->id);
    expect($original->entries)->toHaveCount(1);
});

it('throws when publishing a card errata that was never submitted for approval', function () {
    $card = CardErrata::factory()->create();

    $this->expectException(Exception::class);
    $this->expectExceptionMessage(CardErrata::NO_APPROVAL);

    $card->publish($this->editor);
});
