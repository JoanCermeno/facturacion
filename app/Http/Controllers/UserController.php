<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $auth = $request->user();

        $users = User::where('companies_id', $auth->companies_id)->get();

        return UserResource::collection($users);
    }

    public function store(Request $request)
    {
        $auth = $request->user();

        if (!$auth->isAdmin()) {
            return response()->json(['message' => 'Solo el administrador puede crear usuarios'], 403);
        }

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => ['required', Rule::in(['cashier'])],
            'phone'    => 'nullable|string|max:20',
        ]);

        $data['companies_id'] = $auth->companies_id;
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return new UserResource($user);
    }

    public function show(Request $request, User $user)
    {
        $auth = $request->user();

        if ($auth->companies_id !== $user->companies_id) {
            return response()->json(['message' => 'Usuario fuera de tu empresa'], 403);
        }

        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {
        $auth = $request->user();

        if (!$auth->isAdmin()) {
            return response()->json(['message' => 'Solo el administrador puede editar usuarios'], 403);
        }

        if ($auth->companies_id !== $user->companies_id) {
            return response()->json(['message' => 'No puedes editar usuarios de otra empresa'], 403);
        }

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users','email')->ignore($user->id)
            ],
            'role'  => ['required', Rule::in(['cashier'])],
            'phone' => 'nullable|string|max:20',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:6'
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return new UserResource($user);
    }

    public function destroy(Request $request, User $user)
    {
        $auth = $request->user();

        if (!$auth->isAdmin()) {
            return response()->json(['message' => 'Solo el administrador puede eliminar usuarios'], 403);
        }

        if ($auth->companies_id !== $user->companies_id) {
            return response()->json(['message' => 'No puedes eliminar usuarios de otra empresa'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente 🗑️']);
    }
}
