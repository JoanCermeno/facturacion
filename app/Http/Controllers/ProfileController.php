<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Company;

class ProfileController extends Controller
{
    // Mostrar perfil
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    // Actualizar datos personales
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'  => 'string|max:255',
            'email' => ['email', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update($request->only(['name', 'email']));

        return response()->json([
            'message' => 'Perfil actualizado correctamente ✅',
            'user' => $user
        ]);
    }

    // Cambiar contraseña
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'      => 'required',
            'new_password'          => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'La contraseña actual no es correcta'], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada correctamente ✅']);
    }

}
