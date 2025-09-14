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

        if (!$user->fk_company) {
            return response()->json(['message' => 'No tienes una empresa asociada.'], 403);
        }

        $departments = Department::where('company_id', $user->fk_company)->get();

        return response()->json($departments);
    }

    // Crear un nuevo departamento
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->fk_company) {
            return response()->json(['message' => 'Debes tener una empresa registrada.'], 403);
        }

        $data = $request->validate([
            'code'        => 'required|string|max:50|unique:departments,code',
            'description' => 'required|string|max:255',
            'type'        => 'in:service,unit',
        ]);

        $data['company_id'] = $user->fk_company;

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

        $department = Department::where('company_id', $user->fk_company)->findOrFail($id);

        $data = $request->validate([
            'description' => 'sometimes|string|max:255',
            'type'        => 'nullable|in:service,unit',
        ]);

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

        $department = Department::where('company_id', $user->fk_company)->findOrFail($id);
   
        $department->delete();

        return response()->json([
            'message' => 'Departamento eliminado correctamente 🗑️',
        ]);
    }
}
