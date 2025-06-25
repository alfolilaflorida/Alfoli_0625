<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\DetalleAlfoli;
use App\Models\Articulo;
use App\Models\Hermano;
use Carbon\Carbon;

class AlfoliController extends Controller
{
    public function index()
    {
        return view('alfoli.index');
    }

    public function getData()
    {
        // Llamar al stored procedure ObtRegisAlfoli()
        $data = DB::select('CALL ObtRegisAlfoli()');
        
        return response()->json($data);
    }

    public function create()
    {
        $hermanos = Hermano::orderBy('nombres')->get();
        
        // Obtener artículos del mes actual
        $mesActual = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));
        $articulos = Articulo::where('mes_articulo', $mesActual)->get();

        return view('alfoli.create', compact('hermanos', 'articulos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hermano' => 'required|exists:hermanos,id',
            'articulo' => 'required|exists:articulos,id',
            'cantidad' => 'required|integer|min:1|max:9',
            'fecha_caducidad' => 'required|date|after:' . Carbon::now()->addDays(59)->format('Y-m-d'),
        ], [
            'hermano.required' => 'Debe seleccionar un hermano.',
            'hermano.exists' => 'El hermano seleccionado no es válido.',
            'articulo.required' => 'Debe seleccionar un artículo.',
            'articulo.exists' => 'El artículo seleccionado no es válido.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número.',
            'cantidad.min' => 'La cantidad mínima es 1.',
            'cantidad.max' => 'La cantidad máxima es 9.',
            'fecha_caducidad.required' => 'La fecha de caducidad es obligatoria.',
            'fecha_caducidad.date' => 'La fecha de caducidad debe ser una fecha válida.',
            'fecha_caducidad.after' => 'La fecha de caducidad debe ser al menos 60 días posterior a hoy.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Llamar al stored procedure InsAlfoli
            DB::statement('CALL InsAlfoli(?, ?, ?, ?, ?)', [
                $request->hermano,
                $request->articulo,
                $request->cantidad,
                $request->fecha_caducidad,
                auth()->user()->nombre_usuario
            ]);

            $message = 'Registro guardado exitosamente.';
            
            if ($request->has('guardar_y_agregar')) {
                return redirect()->route('alfoli.create')
                    ->with('success', $message . ' Puedes agregar otro.');
            }

            return redirect()->route('alfoli.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al guardar: ' . $e->getMessage()])
                ->withInput();
        }
    }
}