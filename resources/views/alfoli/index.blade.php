@extends('layouts.app')

@section('title', 'Detalle Ingreso Alfolí')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-table me-2"></i>Detalle de Ingreso Alfolí Mensual
    </h1>
    
    <div class="dropdown">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-download me-2"></i>Exportar
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="exportToExcel()">
                <i class="fas fa-file-excel me-2"></i>Excel (Filtrado)
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="exportToPDF()">
                <i class="fas fa-file-pdf me-2"></i>PDF (Filtrado)
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimir
            </a></li>
        </ul>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <select class="form-select" id="filtroVencimiento" onchange="filtrarTabla()">
                    <option value="todos">🧾 Mostrar Todos</option>
                    <option value="proximos">⚠️ Pronto a Vencer (menos de 60 días)</option>
                </select>
            </div>
            <div class="col-md-8">
                <input type="text" class="form-control" id="busquedaGeneral" 
                       placeholder="🔍 Buscar..." oninput="filtrarTabla()">
            </div>
        </div>
    </div>
</div>

<!-- Acciones -->
<div class="row mb-4">
    <div class="col-md-4">
        <a href="{{ route('alfoli.create') }}" class="btn btn-primary w-100">
            <i class="fas fa-circle-plus me-2"></i>Agregar Alfolí
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('articulos.create') }}" class="btn btn-success w-100">
            <i class="fas fa-cart-plus me-2"></i>Agregar Artículo
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('hermanos.create') }}" class="btn btn-info w-100">
            <i class="fas fa-users me-2"></i>Agregar Hermano
        </a>
    </div>
</div>

<!-- Contador de resultados -->
<div class="alert alert-info" id="resultsCount">
    <i class="fas fa-info-circle me-2"></i>Cargando registros...
</div>

<!-- Loading -->
<div class="loading-spinner" id="loader">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
    <p class="mt-2">Cargando registros, por favor espera...</p>
</div>

<!-- Tabla -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="tablaAlfoli">
                <thead>
                    <tr>
                        <th>Cantidad</th>
                        <th>Fecha Registro</th>
                        <th>Mes</th>
                        <th>F. Caducidad</th>
                        <th>Descripción</th>
                        <th>Nombre del Hermano</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llenará dinámicamente -->
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
let dataGlobal = [];
let datosFiltradosGlobal = [];

async function cargarDatos() {
    document.getElementById('loader').style.display = 'block';

    try {
        const response = await fetch('{{ route("alfoli.data") }}');
        const data = await response.json();
        dataGlobal = data;

        const fechaLimite = new Date();
        fechaLimite.setDate(fechaLimite.getDate() + 59);

        let registrosVencimiento = 0;

        data.forEach(item => {
            const fechaCaducidad = new Date(item.fecha_caducidad);
            const esProximoAVencer = fechaCaducidad <= fechaLimite;

            if (esProximoAVencer) registrosVencimiento++;
        });

        if (registrosVencimiento > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: `Existen ${registrosVencimiento} registro(s) con fecha de caducidad menor a 60 días.`,
                confirmButtonText: 'Entendido'
            });
        }

        filtrarTabla();
    } catch (error) {
        Swal.fire('Error', 'No se pudieron cargar los datos.', 'error');
    } finally {
        document.getElementById('loader').style.display = 'none';
    }
}

function filtrarTabla() {
    const filtroVencimiento = document.getElementById('filtroVencimiento').value;
    const textoBusqueda = document.getElementById('busquedaGeneral').value.toLowerCase();

    const fechaLimite = new Date();
    fechaLimite.setDate(fechaLimite.getDate() + 59);

    const tbody = document.querySelector('#tablaAlfoli tbody');
    tbody.innerHTML = '';

    datosFiltradosGlobal = dataGlobal.filter(item => {
        const fechaCaducidad = new Date(item.fecha_caducidad);
        const esProximoAVencer = fechaCaducidad <= fechaLimite;

        const cumpleVencimiento = filtroVencimiento === 'todos' || 
                                 (filtroVencimiento === 'proximos' && esProximoAVencer);

        const textoItem = `${item.codigo_barra} ${item.descripcion} ${item.nombre_hermano}`.toLowerCase();
        const cumpleBusqueda = textoItem.includes(textoBusqueda);

        return cumpleVencimiento && cumpleBusqueda;
    });

    actualizarContador(datosFiltradosGlobal.length);

    if (datosFiltradosGlobal.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay registros que coincidan con los filtros seleccionados.</td></tr>';
        return;
    }

    datosFiltradosGlobal.forEach(item => {
        const fechaCaducidad = new Date(item.fecha_caducidad);
        const esProximoAVencer = fechaCaducidad <= fechaLimite;
        const claseFila = esProximoAVencer ? 'table-warning' : '';

        tbody.innerHTML += `
            <tr class="${claseFila}">
                <td>${item.cantidad}</td>
                <td>${item.fecha_registro}</td>
                <td>${item.mes}</td>
                <td>${item.fecha_caducidad}</td>
                <td>${item.descripcion}</td>
                <td>${item.nombre_hermano}</td>
            </tr>
        `;
    });
}

function actualizarContador(cantidad) {
    const contador = document.getElementById('resultsCount');
    contador.innerHTML = `<i class="fas fa-info-circle me-2"></i>Mostrando ${cantidad} registro(s)`;
}

function exportToExcel() {
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.json_to_sheet(datosFiltradosGlobal);
    XLSX.utils.book_append_sheet(wb, ws, "Detalle Alfolí");
    XLSX.writeFile(wb, 'detalle_alfoli_filtrado.xlsx');
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text("Detalle Alfolí (Filtrado)", 14, 16);
    doc.autoTable({
        head: [['Cantidad', 'Fecha Registro', 'Mes', 'F. Caducidad', 'Descripción', 'Nombre del Hermano']],
        body: datosFiltradosGlobal.map(item => [
            item.cantidad,
            item.fecha_registro,
            item.mes,
            item.fecha_caducidad,
            item.descripcion,
            item.nombre_hermano
        ])
    });
    doc.save("detalle_alfoli_filtrado.pdf");
}

// Cargar datos al inicializar
cargarDatos();
</script>
@endpush
@endsection