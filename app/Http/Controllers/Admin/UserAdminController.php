<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserAdminController extends Controller
{
    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Users/Index', [
            'users' => User::with('roles')->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Users/UserForm', [
            'roles' => Role::get(),
        ]);
    }

    public function edit(Request $request, User $user)
    {
        return inertia('Admin/Users/UserForm', [
            'user' => $user->loadMissing('roles'),
            'current_role' => $user->roles->first(),
            'roles' => Role::get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['nullable', 'array'],
        ]);

        $role = isset($validated['role']) ? $validated['role']['id'] : null;
        if (isset($validated['role'])) {
            unset($validated['role']);
        }

        /** @var User $user */
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $user->syncRoles([$role]);

        return to_route('admin.users.index')->withMessage($user->name.' created successfully!');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['nullable', 'array'],
        ]);

        $role = isset($validated['role']) ? $validated['role']['id'] : null;
        if (isset($validated['role'])) {
            unset($validated['role']);
        }

        $user->update($validated);
        $user->syncRoles([$role]);

        return to_route('admin.users.index')->withMessage($user->name.' updated successfully!');
    }

    public function delete(Request $request, User $user)
    {
        $userName = $user->name;

        $user->delete();

        return to_route('admin.users.index')->withMessage($userName.' has been deleted.');
    }
}
