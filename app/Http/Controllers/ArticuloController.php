<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Articulo;

class ArticuloController extends Controller
{
    public function create()
    {
        $meses = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];

        return view('articulos.create', compact('meses'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo_barra' => 'required|numeric|digits_between:1,13|unique:articulos,codigo_barra',
            'descripcion' => 'required|string|max:150',
            'cantidad' => 'required|integer|min:1|max:9',
            'mes_articulo' => 'required|string|in:Enero,Febrero,Marzo,Abril,Mayo,Junio,Julio,Agosto,Septiembre,Octubre,Noviembre,Diciembre',
        ], [
            'codigo_barra.required' => 'El código de barra es obligatorio.',
            'codigo_barra.numeric' => 'El código de barra debe ser numérico.',
            'codigo_barra.digits_between' => 'El código de barra debe tener entre 1 y 13 dígitos.',
            'codigo_barra.unique' => 'Este código de barra ya existe.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede exceder 150 caracteres.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número.',
            'cantidad.min' => 'La cantidad mínima es 1.',
            'cantidad.max' => 'La cantidad máxima es 9.',
            'mes_articulo.required' => 'Debe seleccionar un mes.',
            'mes_articulo.in' => 'El mes seleccionado no es válido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            Articulo::create($request->all());

            $message = $request->has('guardar_y_agregar') 
                ? 'Artículo guardado. Puedes agregar otro.'
                : 'Artículo guardado exitosamente.';

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }
}