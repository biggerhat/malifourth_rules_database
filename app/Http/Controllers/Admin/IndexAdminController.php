<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IndexTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Index;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class IndexAdminController extends Controller
{
    public function list(Request $request)
    {
        return Index::orderBy('title', 'ASC')->get()->toJson();
    }

    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Indices/Index', [
            'indices' => Index::with('approval', 'batch')->orderBy('title', 'ASC')->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Indices/IndexForm', [
            'index_types' => IndexTypeEnum::toSelectOptions(),
        ]);
    }

    public function edit(Request $request, Index $index)
    {
        return inertia('Admin/Indices/IndexForm', [
            'index' => $index,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('indices')],
            'permissions' => ['required', 'array'],
        ]);

        $index = Index::create([
            'name' => $request->title,
            'guard_name' => 'web',
        ]);

        return to_route('admin.indices.index')->withMessage($index->title.' created successfully!');
    }

    public function update(Request $request, Index $index)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('indices')->ignore($index->id)],
            'permissions' => ['required', 'array'],
        ]);

        $index->update($validated);
        $index->syncPermissions(Permission::whereIn('name', $request->permissions)->get());

        return to_route('admin.indices.index')->withMessage($index->title.' updated successfully!');
    }

    public function delete(Request $request, Index $index)
    {
        $name = $index->title;

        $index->delete();

        return to_route('admin.indices.index')->withMessage($name.' has been deleted.');
    }

    private function validateAndSave(Request $request, ?Index $index = null): Index
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

    }
}
