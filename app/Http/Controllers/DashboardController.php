<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DetalleAlfoli;
use App\Models\Articulo;
use App\Models\Hermano;

class DashboardController extends Controller
{
    public function index()
    {
        // Indicadores de cumplimiento
        $indicadores = $this->getIndicadoresCumplimiento();
        
        // Comparación total por artículo
        $comparacionTotal = $this->getComparacionTotal();
        
        // Productos próximos a caducar
        $productosCaducidad = $this->getProductosCaducidad();

        return view('dashboard.index', compact(
            'indicadores',
            'comparacionTotal', 
            'productosCaducidad'
        ));
    }

    private function getIndicadoresCumplimiento()
    {
        // Llamar al stored procedure ObtCumpAportes()
        return DB::select('CALL ObtCumpAportes()');
    }

    private function getComparacionTotal()
    {
        // Llamar al stored procedure ObtCompTotalesArt()
        return DB::select('CALL ObtCompTotalesArt()');
    }

    private function getProductosCaducidad($dias = 55)
    {
        // Llamar al stored procedure ObtArtProxAVencer()
        return DB::select('CALL ObtArtProxAVencer(?)', [$dias]);
    }

    public function exportExcel()
    {
        // Implementar exportación a Excel
        // Usar Maatwebsite\Excel
    }

    public function exportPdf()
    {
        // Implementar exportación a PDF
        // Usar barryvdh/laravel-dompdf
    }
}