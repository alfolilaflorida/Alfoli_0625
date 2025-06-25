@extends('layouts.app')

@section('title', 'Dashboard Alfolí')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-chart-line me-2"></i>Dashboard Alfolí
    </h1>
    
    <div class="dropdown">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-download me-2"></i>Exportar
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="exportToExcel()">
                <i class="fas fa-file-excel me-2"></i>Excel
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="exportToPDF()">
                <i class="fas fa-file-pdf me-2"></i>PDF
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimir
            </a></li>
        </ul>
    </div>
</div>

<!-- Indicadores de Cumplimiento -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-chart-bar me-2"></i>Indicadores de Cumplimiento
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="tablaIndicadores">
                <thead>
                    <tr>
                        <th>Hermano</th>
                        <th>Mes</th>
                        <th>Artículo</th>
                        <th>Cantidad</th>
                        <th>Aporte</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($indicadores as $indicador)
                    <tr class="{{ $indicador->estado === 'No Cumple' ? 'table-danger' : '' }}">
                        <td>{{ $indicador->hermano }}</td>
                        <td>{{ $indicador->mes }}</td>
                        <td>{{ $indicador->articulo }}</td>
                        <td>{{ $indicador->cant }}</td>
                        <td>{{ $indicador->aporte }}</td>
                        <td>
                            @if($indicador->estado === 'No Cumple')
                                <span class="status-badge status-inactive">
                                    <i class="fas fa-times me-1"></i>No Cumple
                                </span>
                            @else
                                <span class="status-badge status-active">
                                    <i class="fas fa-check me-1"></i>Cumple
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Productos Próximos a Caducar -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>Productos Próximos a Caducar
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="tablaProductosCaducidad">
                <thead>
                    <tr>
                        <th>Fecha Registro</th>
                        <th>Mes</th>
                        <th>Fecha Caducidad</th>
                        <th>Código Barras</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productosCaducidad as $producto)
                    <tr class="table-warning">
                        <td>{{ $producto->fecha_registro }}</td>
                        <td>{{ $producto->mes }}</td>
                        <td>{{ $producto->fecha_caducidad }}</td>
                        <td>{{ $producto->codigo_barra }}</td>
                        <td>{{ $producto->descripcion }}</td>
                        <td>{{ $producto->cantidad }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Comparativa Total por Artículo -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-balance-scale me-2"></i>Comparativa Total por Artículo
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="tablaComparacion">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Artículo</th>
                        <th>Cant. Requerida/Art.</th>
                        <th>Total Esperado</th>
                        <th>Total Aportado</th>
                        <th>Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comparacionTotal as $item)
                    @php
                        $diferencia = $item->total_esperado_articulo - $item->total_aportado_articulo;
                        $claseDestaque = $diferencia > 0 ? 'table-warning' : '';
                    @endphp
                    <tr class="{{ $claseDestaque }}">
                        <td>{{ $item->mes }}</td>
                        <td>{{ $item->articulo }}</td>
                        <td>{{ $item->cant_requerida_articulo }}</td>
                        <td>{{ $item->total_esperado_articulo }}</td>
                        <td>{{ $item->total_aportado_articulo }}</td>
                        <td>
                            @if($diferencia > 0)
                                <span class="text-danger fw-bold">-{{ $diferencia }}</span>
                            @elseif($diferencia < 0)
                                <span class="text-success fw-bold">+{{ abs($diferencia) }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<!-- SheetJS para Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<!-- jsPDF para PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>

<script>
const indicadores = @json($indicadores);
const comparacionTotal = @json($comparacionTotal);
const productosCaducidad = @json($productosCaducidad);

function exportToExcel() {
    const fecha = new Date().toLocaleDateString().replace(/\//g, '-');
    const wb = XLSX.utils.book_new();

    // Hoja de indicadores
    const wsIndicadores = XLSX.utils.json_to_sheet(indicadores);
    XLSX.utils.book_append_sheet(wb, wsIndicadores, "Cumplimiento");

    // Hoja de comparación
    const wsComparacion = XLSX.utils.json_to_sheet(comparacionTotal);
    XLSX.utils.book_append_sheet(wb, wsComparacion, "Total por Artículo");

    // Hoja de productos caducidad
    const wsCaducidad = XLSX.utils.json_to_sheet(productosCaducidad);
    XLSX.utils.book_append_sheet(wb, wsCaducidad, "Productos Caducidad");

    XLSX.writeFile(wb, `alfoli_dashboard_${fecha}.xlsx`);
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const fecha = new Date().toLocaleDateString();

    doc.text(`Indicadores de Cumplimiento (${fecha})`, 14, 16);
    doc.autoTable({
        head: [['Hermano', 'Mes', 'Artículo', 'Cantidad', 'Aporte', 'Estado']],
        body: indicadores.map(item => [
            item.hermano, item.mes, item.articulo, 
            item.cant, item.aporte, item.estado
        ])
    });

    doc.addPage();
    doc.text(`Comparativa Total por Artículo (${fecha})`, 14, 16);
    doc.autoTable({
        head: [['Mes', 'Artículo', 'Cant. Requerida', 'Total Esperado', 'Total Aportado', 'Diferencia']],
        body: comparacionTotal.map(item => [
            item.mes, item.articulo, item.cant_requerida_articulo,
            item.total_esperado_articulo, item.total_aportado_articulo,
            (item.total_esperado_articulo - item.total_aportado_articulo)
        ])
    });

    doc.addPage();
    doc.text(`Productos Próximos a Caducar (${fecha})`, 14, 16);
    doc.autoTable({
        head: [['Fecha Registro', 'Mes', 'Fecha Caducidad', 'Código Barras', 'Descripción', 'Cantidad']],
        body: productosCaducidad.map(item => [
            item.fecha_registro, item.mes, item.fecha_caducidad,
            item.codigo_barra, item.descripcion, item.cantidad
        ])
    });

    doc.save(`alfoli_dashboard_${fecha}.pdf`);
}
</script>
@endpush
@endsection