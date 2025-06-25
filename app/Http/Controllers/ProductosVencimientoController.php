<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\DetalleAlfoli;
use App\Models\Articulo;
use Carbon\Carbon;

class ProductosVencimientoController extends Controller
{
    public function index()
    {
        return view('productos-vencimiento.index');
    }

    public function getData()
    {
        $diasProntoVencer = 59;
        $hoy = Carbon::now()->format('Y-m-d');
        $fechaLimite = Carbon::now()->addDays($diasProntoVencer)->format('Y-m-d');

        $productos = DB::select("
            SELECT 
                a.id AS id_articulo,
                b.id AS id_detalle,
                b.fecha_registro,
                b.fecha_caducidad,
                a.codigo_barra,
                a.descripcion,
                b.cantidad,
                CASE 
                    WHEN b.fecha_caducidad < ? THEN 'vencido'
                    WHEN b.fecha_caducidad BETWEEN ? AND ? THEN 'pronto_vencer'
                    ELSE 'ok'
                END AS estado
            FROM articulos a
            JOIN detalle_alfoli b ON a.id = b.id_articulo
            WHERE b.fecha_caducidad <= ?
            ORDER BY b.fecha_caducidad ASC
        ", [$hoy, $hoy, $fechaLimite, $fechaLimite]);

        return response()->json($productos);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_detalle' => 'required|exists:detalle_alfoli,id',
            'id_articulo' => 'required|exists:articulos,id',
            'codigo_barra' => 'required|numeric|digits_between:1,13',
            'descripcion' => 'required|string|max:150',
            'cantidad' => 'required|integer|min:1',
            'fecha_caducidad' => 'required|date|after:' . Carbon::now()->addDays(59)->format('Y-m-d'),
        ], [
            'fecha_caducidad.after' => 'La fecha debe ser 2 meses posterior a la fecha actual.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();

            // Actualizar artículo
            Articulo::where('id', $request->id_articulo)->update([
                'codigo_barra' => $request->codigo_barra,
                'descripcion' => $request->descripcion,
            ]);

            // Actualizar detalle
            DetalleAlfoli::where('id', $request->id_detalle)->update([
                'fecha_caducidad' => $request->fecha_caducidad,
                'cantidad' => $request->cantidad,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el producto: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:detalle_alfoli,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ID de producto no válido.'
            ]);
        }

        try {
            DetalleAlfoli::where('id', $request->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado correctamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el producto: ' . $e->getMessage()
            ]);
        }
    }
}