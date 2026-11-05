<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MedicamentoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Listar medicamentos
    |--------------------------------------------------------------------------
    */

    public function index(): Response
    {
        $medicamentos = Medicamento::query()
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Medicamentos/Index', [
            'medicamentos' => $medicamentos,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar medicamento
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],
            'principio_activo' => [
                'required',
                'string',
                'max:150',
            ],
            'concentracion' => [
                'required',
                'string',
                'max:100',
            ],
            'forma_farmaceutica' => [
                'required',
                'string',
                'max:100',
            ],
            'presentacion' => [
                'required',
                'string',
                'max:150',
            ],
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('medicamentos', 'codigo'),
            ],
        ]);

        $datos['activo'] = true;

        Medicamento::create($datos);

        return redirect()
            ->route('medicamentos.index')
            ->with(
                'success',
                'Medicamento registrado correctamente.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar medicamento
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Medicamento $medicamento
    ): RedirectResponse {
        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],
            'principio_activo' => [
                'required',
                'string',
                'max:150',
            ],
            'concentracion' => [
                'required',
                'string',
                'max:100',
            ],
            'forma_farmaceutica' => [
                'required',
                'string',
                'max:100',
            ],
            'presentacion' => [
                'required',
                'string',
                'max:150',
            ],
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('medicamentos', 'codigo')
                    ->ignore($medicamento->id),
            ],
        ]);

        $medicamento->update($datos);

        return redirect()
            ->route('medicamentos.index')
            ->with(
                'success',
                'Medicamento actualizado correctamente.'
            );
    }
    
/*
|--------------------------------------------------------------------------
| Activar o desactivar medicamento
|--------------------------------------------------------------------------
*/

public function cambiarEstado(
    Medicamento $medicamento
): RedirectResponse {

    $medicamento->update([
        'activo' => !$medicamento->activo,
    ]);

    return redirect()
        ->route('medicamentos.index')
        ->with(
            'success',
            $medicamento->activo
                ? 'Medicamento activado correctamente.'
                : 'Medicamento desactivado correctamente.'
        );
}
}
