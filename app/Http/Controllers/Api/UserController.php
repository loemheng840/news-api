<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // GET: /api/users
    public function index()
    {
        return User::all();
    }

    // POST: /api/users
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:ADMIN,EDITOR,AUTHOR,READER'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json($user, 201);
    }

    // GET: /api/users/{id}
    public function show($id)
    {
        return User::findOrFail($id);
    }

    // PUT: /api/users/{id}
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update($request->only(['name', 'email', 'role']));

        return response()->json($user);
    }

    // DELETE: /api/users/{id}
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'User deleted']);
    }

   // PATCH: /api/users/{id}/role
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:ADMIN,EDITOR,AUTHOR,READER'
        ]);

        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->save();

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user
        ]);
    }
}
