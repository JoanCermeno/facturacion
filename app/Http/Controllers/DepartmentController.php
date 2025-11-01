<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\Companies;

class DepartmentController extends Controller
{
    // ✅ Listar departamentos solo de la compañía del usuario
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->companies_id) {
            return response()->json(['message' => 'No tienes una empresa asociada.'], 403);
        }

        $departments = Department::where('companies_id', $user->companies_id)->get();

        return response()->json($departments);
    }

    // ✅ Crear un departamento con opción de generar código automáticamente
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->companies_id) {
            return response()->json(['message' => 'Debes tener una empresa registrada.'], 403);
        }

        $company = Companies::find($user->companies_id);

        // ✅ Reglas base
        $rules = [
            'description' => 'required|string|max:255',
            'type'        => 'required|in:service,unit',
        ];

        // ✅ Si NO autogenera códigos → "code" es requerido
        if (!$company->auto_code_departments) {
            $rules['code'] = 'required|string|max:50|unique:departments,code';
        }

        $data = $request->validate($rules);

        // ✅ Vincular a la compañía del usuario
        $data['companies_id'] = $user->companies_id;

        // ✅ Si autogenera código → generarlo aquí
        if ($company->auto_code_departments) {
            $prefix = $company->department_code_prefix ?? 'DEP';

            // Buscar el último registro con ese prefijo
            $lastCode = Department::where('companies_id', $company->id)
                ->where('code', 'like', $prefix . '-%')
                ->orderBy('id', 'desc')
                ->value('code');

            // Extraer número
            if ($lastCode) {
                $num = intval(str_replace($prefix . '-', '', $lastCode)) + 1;
            } else {
                $num = 1;
            }

            $data['code'] = $prefix . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
        }

        $department = Department::create($data);

        return response()->json([
            'message'    => 'Departamento creado correctamente ✅',
            'department' => $department,
        ], 201);
    }


    // ✅ Actualizar departamento
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $company = Companies::find($user->companies_id);

        // Verificar que el departamento exista y pertenezca a la compañía
        $department = Department::where('companies_id', $user->companies_id)->findOrFail($id);

        // ✅ Reglas base
        $rules = [
            'description' => 'sometimes|string|max:255',
            'type'        => 'sometimes|in:service,unit',
        ];

        // ✅ Si NO autogenera códigos → validar campo code
        if (!$company->auto_code_departments) {
            $rules['code'] = 'sometimes|string|max:50|unique:departments,code,' . $id;
        }

        $data = $request->validate($rules);

        // ✅ Si la empresa autogenera códigos → ignorar cualquier intento de enviar "code"
        if ($company->auto_code_departments) {
            unset($data['code']);
        }

        $department->update($data);

        return response()->json([
            'message'    => 'Departamento actualizado correctamente ✅',
            'department' => $department,
        ]);
    }


    // ✅ Eliminar departamento
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
