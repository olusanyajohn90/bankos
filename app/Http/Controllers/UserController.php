<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['roles', 'branch'])->paginate();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $branches = Branch::all();
        return view('users.create', compact('roles', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lower', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,name'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $user = User::create([
            'name'      => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'branch_id' => $validated['branch_id'],
            'status'    => $validated['status'],
        ]);

        $user->assignRole($validated['roles']);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Don't allow editing Super Admins unless the current user is a super admin
        if ($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::all();
        if (!auth()->user()->hasRole('super_admin')) {
            $roles = $roles->reject(function ($role) {
                return $role->name === 'super_admin';
            });
        }

        $branches = Branch::all();
        return view('users.edit', compact('user', 'roles', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if ($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lower', 'email', 'max:255', 'unique:'.User::class.',email,'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,name'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $user->name      = trim($validated['first_name'] . ' ' . $validated['last_name']);
        $user->email     = $validated['email'];
        $user->branch_id = $validated['branch_id'];
        $user->status    = $validated['status'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Sync roles carefully (don't let tenant admins accidentally grant super_admin)
        $rolesToSync = $validated['roles'];
        if (!auth()->user()->hasRole('super_admin') && in_array('super_admin', $rolesToSync)) {
            $rolesToSync = array_diff($rolesToSync, ['super_admin']);
        }
        
        // Retain super_admin if they already had it and current user isn't super admin
        if ($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')) {
            $rolesToSync[] = 'super_admin';
        }

        $user->syncRoles($rolesToSync);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        if ($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
