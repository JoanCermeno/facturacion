<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CashierController extends Controller
{
    // Registrar un cajero
    public function store(Request $request)
    {
        $admin = $request->user();

        if (!$admin->isAdmin()) {
            return response()->json(['message' => 'Solo un admin puede crear cajeros.'], 403);
        }

        if (!$admin->companies_id) {
            return response()->json(['message' => 'El admin no tiene una empresa asociada.'], 403);
        }


        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone'    => 'required|string|max:20',
        ]);

    

        // Crear usuario cajero
        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'companies_id' => $admin->companies_id,
            'role' => 'cashier',
        ]);

        return response()->json([
            'message' => 'Cajero creado correctamente ✅',
            'user'    => $user,
        ], 201);
    }

    // Listar cajeros de la empresa del admin
    public function index(Request $request)
    {
        $admin = $request->user();
     
        if (!$admin->companies_id) {
            return response()->json(['message' => 'No tienes empresa asociada.'], 403);
        }

     
        $cashiers = User::where('companies_id', $admin->companies_id)
            ->where('role', 'cashier')
            ->get();
            return response()->json($cashiers);

          
    }
}
