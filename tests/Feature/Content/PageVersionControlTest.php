<?php

use App\Models\Page;
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

    $this->pageAttributes = [
        'title' => 'Line of Sight',
        'content' => 'Original content.',
        'book_page_numbers' => null,
        'internal_notes' => null,
        'change_notes' => 'Initial version',
        'batch_id' => null,
        'publish_directly' => false,
        'approve_directly' => false,
    ];
});

it('will not publish a page that has no approved approval', function () {
    $this->actingAs($this->editor)->post(route('admin.pages.store'), $this->pageAttributes)->assertRedirect();
    $page = Page::latest('id')->firstOrFail();

    expect($page->published_at)->toBeNull();

    $this->actingAs($this->editor)->post(route('admin.pages.publish', $page))->assertRedirect();

    $page->refresh();
    expect($page->published_at)->toBeNull();
});

it('publishes a page once its approval is approved', function () {
    $this->actingAs($this->editor)->post(route('admin.pages.store'), $this->pageAttributes)->assertRedirect();
    $page = Page::latest('id')->firstOrFail();
    $page->approval->update(['approved_at' => now(), 'approved_by' => $this->editor->id]);

    $this->actingAs($this->editor)->post(route('admin.pages.publish', $page))->assertRedirect();

    $page->refresh();
    expect($page->published_at)->not->toBeNull();
    expect($page->published_by)->toBe($this->editor->id);
});

it('creates a new draft row linked via previous/original when editing a published page, without touching the live version', function () {
    $this->actingAs($this->editor)->post(route('admin.pages.store'), $this->pageAttributes);
    $page = Page::latest('id')->firstOrFail();
    $page->approval->update(['approved_at' => now(), 'approved_by' => $this->editor->id]);
    $this->actingAs($this->editor)->post(route('admin.pages.publish', $page));
    $page->refresh();

    $originalPublishedAt = $page->published_at;
    $originalContent = $page->content;

    $this->actingAs($this->editor)->post(route('admin.pages.update', $page), array_merge($this->pageAttributes, [
        'content' => 'Updated content.',
        'change_notes' => 'Clarified wording',
    ]))->assertRedirect();

    $draft = Page::where('id', '!=', $page->id)->latest('id')->firstOrFail();

    expect($draft->previous)->toBe($page->id);
    expect($draft->original)->toBe($page->id);
    expect($draft->published_at)->toBeNull();

    // The live, published row must be completely unaffected by the draft.
    $page->refresh();
    expect($page->published_at->equalTo($originalPublishedAt))->toBeTrue();
    expect($page->content)->toBe($originalContent);
    expect($page->trashed())->toBeFalse();
});

it('retires the previous version and repoints newest when a draft is published', function () {
    $this->actingAs($this->editor)->post(route('admin.pages.store'), $this->pageAttributes);
    $page = Page::latest('id')->firstOrFail();
    $page->approval->update(['approved_at' => now(), 'approved_by' => $this->editor->id]);
    $this->actingAs($this->editor)->post(route('admin.pages.publish', $page));
    $page->refresh();
    $originalId = $page->id;

    $this->actingAs($this->editor)->post(route('admin.pages.update', $page), array_merge($this->pageAttributes, [
        'content' => 'Updated content.',
        'change_notes' => 'Clarified wording',
    ]));

    $draft = Page::where('id', '!=', $originalId)->latest('id')->firstOrFail();
    $draft->approval->update(['approved_at' => now(), 'approved_by' => $this->editor->id]);

    $this->actingAs($this->editor)->post(route('admin.pages.publish', $draft))->assertRedirect();

    $draft->refresh();
    expect($draft->published_at)->not->toBeNull();
    expect($draft->newest)->toBeNull();

    $original = Page::withTrashed()->findOrFail($originalId);
    expect($original->trashed())->toBeTrue();
    expect($original->newest)->toBe($draft->id);
    expect($original->fresh()->approval)->toBeNull();

    // The public-facing "newest version" relation must resolve to the new draft.
    expect($original->newestVersion()->first()->id)->toBe($draft->id);
});

it('throws when publishing a page that was never submitted for approval', function () {
    $page = Page::factory()->create();

    $this->expectException(Exception::class);
    $this->expectExceptionMessage(Page::NO_APPROVAL);

    $page->publish($this->editor);
});
