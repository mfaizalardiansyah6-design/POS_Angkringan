<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'role' => ['required', Rule::enum(Role::class)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'role' => $validated['role'],
            'password' => $validated['password'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogger::log('user.created', $user, 'Pengguna dibuat: '.$user->username.' ('.$user->role->value.').', null, [
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role->value,
            'is_active' => $user->is_active,
        ]);

        return redirect()->route('users.index')->with('success', 'Pengguna "'.$user->name.'" ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $isSelf = $request->user()->is($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'role' => ['required', Rule::enum(Role::class)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($isSelf) {
            $validated['role'] = Role::Owner->value;
            $request->merge(['is_active' => '1']);
        }

        if (! $request->boolean('is_active') && $user->role === Role::Owner) {
            $this->ensureAnotherOwnerExists($user);
        }

        $old = $user->only(['name', 'username', 'role', 'is_active']);

        $user->fill([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $passwordChanged = filled($validated['password'] ?? null);

        if ($passwordChanged) {
            $user->password = $validated['password'];
        }

        $user->save();

        $new = $user->only(['name', 'username', 'role', 'is_active']);

        if ($old !== $new || $passwordChanged) {
            ActivityLogger::log('user.updated', $user, 'Pengguna diperbarui: '.$user->username.'.', $old, $new + ($passwordChanged ? ['password_changed' => true] : []));
        }

        return redirect()->route('users.index')->with('success', 'Pengguna diperbarui.');
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            throw ValidationException::withMessages(['user' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }

        if ($user->is_active && $user->role === Role::Owner) {
            $this->ensureAnotherOwnerExists($user);
        }

        $user->update(['is_active' => ! $user->is_active]);

        ActivityLogger::log(
            'user.status_changed',
            $user,
            'Status pengguna "'.$user->username.'" diubah menjadi '.($user->is_active ? 'aktif' : 'nonaktif').'.',
            ['is_active' => ! $user->is_active],
            ['is_active' => $user->is_active],
        );

        return back()->with('success', 'Status pengguna diperbarui.');
    }

    private function ensureAnotherOwnerExists(User $user): void
    {
        $otherOwners = User::query()
            ->where('role', Role::Owner->value)
            ->where('is_active', true)
            ->whereKeyNot($user->id)
            ->count();

        if ($otherOwners === 0) {
            throw ValidationException::withMessages([
                'is_active' => 'Minimal satu owner aktif harus tersedia.',
            ]);
        }
    }
}
