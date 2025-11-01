<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // 🔹 Listar clientes de la empresa actual
    public function index(Request $request)
    {
        $user = $request->user();
        $customers = Customer::where('companies_id', $user->companies_id)
            ->orderBy('name', 'asc')
            ->paginate(20);

        return response()->json([
            'message' => 'Clientes obtenidos correctamente ✅',
            'customers' => $customers
        ]);
    }

    // 🔹 Registrar nuevo cliente
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'id_card' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
        ]);

      

        $validated['companies_id'] = $user->companies_id;

        $customer = Customer::create($validated);

        return response()->json([
            'message' => 'Cliente creado correctamente ✅',
            'customer' => $customer
        ], 201);
    }

    // 🔹 Mostrar cliente específico
    public function show(Customer $customer)
    {
        $customer->load('company');
        return response()->json([
            'message' => 'Cliente obtenido correctamente ✅',
            'customer' => $customer
        ]);
    }

    // 🔹 Actualizar cliente
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'id_card' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => 'Cliente actualizado correctamente ✅',
            'customer' => $customer
        ]);
    }

    // 🔹 Eliminar cliente
    public function destroy(Customer $customer)
    {
        // Evitar eliminar el cliente casual
        if ($customer->name === 'Cliente casual') {
            return response()->json([
                'message' => 'No puedes eliminar el cliente por defecto ❌'
            ], 403);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Cliente eliminado correctamente 🗑️'
        ]);
    }
}
