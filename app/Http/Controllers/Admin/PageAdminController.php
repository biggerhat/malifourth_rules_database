<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PageAdminController extends Controller
{
    public function list(Request $request)
    {
        return Page::orderBy('title', 'ASC')->get()->toJson();
    }

    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Batches/Index', [
            'batches' => Batch::with('approval')->orderBy('id', 'DESC')->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Batches/BatchForm');
    }

    public function edit(Request $request, Batch $batch)
    {
        return inertia('Admin/Batches/BatchForm', [
            'batch' => $batch,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('batches')],
            'permissions' => ['required', 'array'],
        ]);

        $permissions = Permission::whereIn('name', $request->permissions)->get();

        $batch = Batch::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        $batch->syncPermissions($permissions);

        return to_route('admin.batches.index')->withMessage($batch->name.' created successfully!');
    }

    public function update(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('batches')->ignore($batch->id)],
            'permissions' => ['required', 'array'],
        ]);

        $batch->update($validated);
        $batch->syncPermissions(Permission::whereIn('name', $request->permissions)->get());

        return to_route('admin.batches.index')->withMessage($batch->name.' updated successfully!');
    }

    public function delete(Request $request, Batch $batch)
    {
        $name = $batch->name;

        $batch->delete();

        return to_route('admin.batches.index')->withMessage($name.' has been deleted.');
    }

    private function validateAndSave(Request $request, ?Batch $batch = null): Batch
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

    }
}
