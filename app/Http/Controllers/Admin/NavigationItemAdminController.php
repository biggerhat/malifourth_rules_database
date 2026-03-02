<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Index;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Section;
use Illuminate\Http\Request;

class NavigationItemAdminController extends Controller
{
    public function index(): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Navigation/Index', [
            'navigationItems' => NavigationItem::with('linkable:id,title')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function create(): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Navigation/Form', [
            'linkables' => $this->getLinkables(),
        ]);
    }

    public function edit(NavigationItem $navigationItem): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Navigation/Form', [
            'navigationItem' => $navigationItem->load('linkable:id,title'),
            'linkables' => $this->getLinkables(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'linkable_type' => ['nullable', 'string', 'in:page,section,index'],
            'linkable_id' => ['nullable', 'integer'],
            'sort_order' => ['required', 'integer'],
            'is_active' => ['boolean'],
        ]);

        $validated['linkable_type'] = $this->resolveLinkableType($validated['linkable_type'] ?? null);

        NavigationItem::create($validated);

        return to_route('admin.navigation.index')->withMessage('Navigation item created successfully!');
    }

    public function update(Request $request, NavigationItem $navigationItem)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'linkable_type' => ['nullable', 'string', 'in:page,section,index'],
            'linkable_id' => ['nullable', 'integer'],
            'sort_order' => ['required', 'integer'],
            'is_active' => ['boolean'],
        ]);

        $validated['linkable_type'] = $this->resolveLinkableType($validated['linkable_type'] ?? null);

        $navigationItem->update($validated);

        return to_route('admin.navigation.index')->withMessage('Navigation item updated successfully!');
    }

    public function delete(NavigationItem $navigationItem)
    {
        $title = $navigationItem->title;

        $navigationItem->delete();

        return to_route('admin.navigation.index')->withMessage($title.' has been deleted.');
    }

    private function getLinkables(): array
    {
        return [
            'pages' => Page::whereNotNull('published_at')->orderBy('title')->get(['id', 'title']),
            'sections' => Section::whereNotNull('published_at')->orderBy('title')->get(['id', 'title']),
            'indices' => Index::whereNotNull('published_at')->orderBy('title')->get(['id', 'title']),
        ];
    }

    private function resolveLinkableType(?string $type): ?string
    {
        return match ($type) {
            'page' => Page::class,
            'section' => Section::class,
            'index' => Index::class,
            default => null,
        };
    }
}
