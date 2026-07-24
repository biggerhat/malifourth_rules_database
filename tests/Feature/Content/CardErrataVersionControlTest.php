<?php

use App\Models\CardErrata;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

it('exposes a flat json shape from the admin view endpoint for the in-form preview', function () {
    $this->actingAs($this->editor)->post(route('admin.card-errata.store'), $this->cardAttributes);
    $card = CardErrata::latest('id')->firstOrFail();

    $response = $this->actingAs($this->editor)->get(route('admin.card-errata.view', $card));

    $response->assertOk();
    $response->assertJsonPath('faction', 'guild');
    $response->assertJsonPath('faction_label', 'Guild');
    $response->assertJsonPath('card_name', 'Lucius Mattheson');
    $response->assertJsonPath('entries.0.what_changed.0.text', 'Reduced defense');
});

it('throws when publishing a card errata that was never submitted for approval', function () {
    $card = CardErrata::factory()->create();

    $this->expectException(Exception::class);
    $this->expectExceptionMessage(CardErrata::NO_APPROVAL);

    $card->publish($this->editor);
});

it('uploads front and back card-level images and stores them on the card, not the entries', function () {
    Storage::fake('public');

    $attributes = $this->cardAttributes;
    $attributes['front_image'] = UploadedFile::fake()->image('lucius-front.png');
    $attributes['back_image'] = UploadedFile::fake()->image('lucius-back.png');

    $this->actingAs($this->editor)->post(route('admin.card-errata.store'), $attributes)->assertRedirect();
    $card = CardErrata::with('entries')->latest('id')->firstOrFail();

    expect($card->front_image)->not->toBeNull();
    expect($card->back_image)->not->toBeNull();
    expect($card->front_image)->not->toBe($card->back_image);
    Storage::disk('public')->assertExists(str_replace('/storage/', '', $card->front_image));
    Storage::disk('public')->assertExists(str_replace('/storage/', '', $card->back_image));
    expect($card->entries->first()->getAttributes())->not->toHaveKeys(['front_image', 'back_image']);
});

it('preserves the existing card images when a new version is created without new uploads', function () {
    Storage::fake('public');

    $attributes = $this->cardAttributes;
    $attributes['front_image'] = UploadedFile::fake()->image('lucius-front.png');
    $attributes['back_image'] = UploadedFile::fake()->image('lucius-back.png');
    $this->actingAs($this->editor)->post(route('admin.card-errata.store'), $attributes);
    $card = CardErrata::latest('id')->firstOrFail();
    $card->approval->update(['approved_at' => now(), 'approved_by' => $this->editor->id]);
    $this->actingAs($this->editor)->post(route('admin.card-errata.publish', $card));
    $card->refresh();

    $updatedAttributes = $this->cardAttributes;
    $updatedAttributes['existing_front_image'] = $card->front_image;
    $updatedAttributes['existing_back_image'] = $card->back_image;

    $this->actingAs($this->editor)->post(route('admin.card-errata.update', $card), $updatedAttributes);

    $draft = CardErrata::where('id', '!=', $card->id)->latest('id')->firstOrFail();
    expect($draft->front_image)->toBe($card->front_image);
    expect($draft->back_image)->toBe($card->back_image);
});

it('renders bold and italic markup from entry text as real html, not raw markup or escaped text', function () {
    $attributes = $this->cardAttributes;
    $attributes['entries'] = [
        ['what_changed' => '{{b}}Bold{{/b}} and {{i}}italic{{/i}} text', 'what_it_was' => '', 'what_it_is_now' => ''],
    ];

    $this->actingAs($this->editor)->post(route('admin.card-errata.store'), $attributes);
    $card = CardErrata::latest('id')->firstOrFail();
    $card->approval->update(['approved_at' => now(), 'approved_by' => $this->editor->id]);
    $this->actingAs($this->editor)->post(route('admin.card-errata.publish', $card));

    $response = $this->get(route('errata.cards.view', $card->fresh()));

    $response->assertInertia(fn ($page) => $page
        ->where('entries.0.what_changed.0.text', '<strong>Bold</strong> and <i>italic</i> text')
    );
});

it('parses game symbol tags in entry text into structured nodes the frontend can render as icons', function () {
    $attributes = $this->cardAttributes;
    $attributes['entries'] = [
        ['what_changed' => 'Gains {{crow /}} and {{ram /}} on this attack', 'what_it_was' => '', 'what_it_is_now' => ''],
    ];

    $this->actingAs($this->editor)->post(route('admin.card-errata.store'), $attributes);
    $card = CardErrata::latest('id')->firstOrFail();
    $card->approval->update(['approved_at' => now(), 'approved_by' => $this->editor->id]);
    $this->actingAs($this->editor)->post(route('admin.card-errata.publish', $card));

    $response = $this->get(route('errata.cards.view', $card->fresh()));

    $response->assertInertia(fn ($page) => $page
        ->has('entries.0.what_changed', 5)
        ->where('entries.0.what_changed.0.text', 'Gains ')
        ->where('entries.0.what_changed.1.crow.inline', true)
        ->where('entries.0.what_changed.2.text', ' and ')
        ->where('entries.0.what_changed.3.ram.inline', true)
        ->where('entries.0.what_changed.4.text', ' on this attack')
    );
});

it('hydrates entry text via the admin preview endpoint without persisting anything', function () {
    $response = $this->actingAs($this->editor)->post(route('admin.card-errata.preview'), [
        'card_name' => 'Lucius Mattheson',
        'faction' => 'guild',
        'entries' => [
            ['what_changed' => '{{b}}Bold{{/b}}', 'what_it_was' => 'was', 'what_it_is_now' => 'now'],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonPath('card_name', 'Lucius Mattheson');
    $response->assertJsonPath('faction_label', 'Guild');
    $response->assertJsonPath('entries.0.what_changed.0.text', '<strong>Bold</strong>');
    expect(CardErrata::count())->toBe(0);
});
