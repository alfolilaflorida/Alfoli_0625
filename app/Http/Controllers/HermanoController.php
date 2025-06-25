<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Hermano;

class HermanoController extends Controller
{
    public function create()
    {
        // Obtener hermanos usando el stored procedure
        $hermanos = DB::select('CALL participantes(?, ?, ?, ?)', [
            'LISTAR', null, null, null
        ]);

        return view('hermanos.create', compact('hermanos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20|regex:/^[0-9+ -]*$/',
        ], [
            'nombres.required' => 'Los nombres son obligatorios.',
            'nombres.max' => 'Los nombres no pueden exceder 100 caracteres.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.max' => 'Los apellidos no pueden exceder 100 caracteres.',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
            'telefono.regex' => 'El formato del teléfono no es válido.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Agregar prefijo "Hno. " a los nombres
            $nombres = 'Hno. ' . $request->nombres;
            
            // Llamar al stored procedure
            DB::statement('CALL participantes(?, ?, ?, ?)', [
                'AGREGAR',
                $nombres,
                $request->apellidos,
                $request->telefono
            ]);

            $message = 'Participante registrado exitosamente.';
            
            if ($request->has('guardar_y_agregar')) {
                return redirect()->route('hermanos.create')
                    ->with('success', $message);
            }

            return redirect()->route('alfoli.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al guardar: ' . $e->getMessage()])
                ->withInput();
        }
    }
}