<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    // Listar departamentos de la empresa del usuario autenticado
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->companies_id) {
            return response()->json(['message' => 'No tienes una empresa asociada.'], 403);
        }

        $departments = Department::where('companies_id', $user->companies_id)->get();

        return response()->json($departments);
    }

    // Crear un nuevo departamento
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->companies_id) {
            return response()->json(['message' => 'Debes tener una empresa registrada.'], 403);
        }

        $data = $request->validate([
            'code'        => 'required|string|max:50|unique:departments,code',
            'description' => 'required|string|max:255',
            'type'        => 'in:service,unit',
        ]);

        $data['companies_id'] = $user->companies_id;

        $department = Department::create($data);

        return response()->json([
            'message' => 'Departamento creado correctamente ✅',
            'department' => $department,
        ], 201);
    }

    // Editar un departamento
    public function update(Request $request, $id)
    {
        $user = $request->user();

        // 1. Encontrar el departamento (asegura que existe y pertenece a la compañía)
        $department = Department::where('companies_id', $user->companies_id)->findOrFail($id);

        // 2. Definir las reglas de validación
        $rules = [
            // 🚀 CAMBIO CLAVE AQUÍ: Ignorar el ID actual ($id)
            // Sintaxis: unique:table,column,except,idColumn
            // Donde 'except' es el valor a ignorar (el $id de la URL)
            'code'          => 'string|max:50|unique:departments,code,' . $id,
            
            'description'   => 'sometimes|string|max:255',
            'type'          => 'nullable|in:service,unit',
        ];

        $data = $request->validate($rules);

        // 3. Actualizar
        $department->update($data);

        return response()->json([
            'message' => 'Departamento actualizado correctamente ✅',
            'department' => $department,
        ]);
    }

    // Eliminar un departamento
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $department = Department::where('companies_id', $user->companies_id)->findOrFail($id);
   
        $department->delete();

        return response()->json([
            'message' => 'Departamento eliminado correctamente 🗑️',
        ]);
    }
}
