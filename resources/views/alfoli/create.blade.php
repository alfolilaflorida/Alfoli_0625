@extends('layouts.app')

@section('title', 'Agregar Alfolí')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-circle-plus me-2"></i>Agregar Alfolí
                </h4>
            </div>
            <div class="card-body">
                <form action="{{ route('alfoli.store') }}" method="POST" id="formAlfoli">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="hermano" class="form-label">Nombre del Hermano</label>
                        <select name="hermano" id="hermano" class="form-select @error('hermano') is-invalid @enderror" required>
                            <option value="">Seleccione</option>
                            @foreach($hermanos as $hermano)
                                <option value="{{ $hermano->id }}" {{ old('hermano') == $hermano->id ? 'selected' : '' }}>
                                    {{ $hermano->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                        @error('hermano')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="articulo" class="form-label">Artículo</label>
                        <select name="articulo" id="articulo" class="form-select @error('articulo') is-invalid @enderror" required>
                            <option value="">Seleccione</option>
                            @forelse($articulos as $articulo)
                                <option value="{{ $articulo->id }}" {{ old('articulo') == $articulo->id ? 'selected' : '' }}>
                                    {{ $articulo->descripcion }}
                                </option>
                            @empty
                                <option value="">No hay artículos para este mes</option>
                            @endforelse
                        </select>
                        @error('articulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad (1-9)</label>
                        <input type="number" 
                               name="cantidad" 
                               id="cantidad" 
                               class="form-control @error('cantidad') is-invalid @enderror" 
                               min="1" 
                               max="9" 
                               value="{{ old('cantidad') }}"
                               placeholder="Ingrese la cantidad" 
                               required>
                        @error('cantidad')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="fecha_caducidad" class="form-label">Fecha de Caducidad</label>
                        <input type="date" 
                               name="fecha_caducidad" 
                               id="fecha_caducidad" 
                               class="form-control @error('fecha_caducidad') is-invalid @enderror" 
                               value="{{ old('fecha_caducidad') }}"
                               required>
                        @error('fecha_caducidad')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">La fecha debe ser al menos 60 días posterior a hoy.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" name="guardar" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar
                        </button>
                        <button type="submit" name="guardar_y_agregar" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Guardar y Agregar Otro
                        </button>
                        <a href="{{ route('alfoli.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Establecer fecha mínima (60 días desde hoy)
document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.getElementById('fecha_caducidad');
    const fechaMinima = new Date();
    fechaMinima.setDate(fechaMinima.getDate() + 60);
    
    const year = fechaMinima.getFullYear();
    const month = String(fechaMinima.getMonth() + 1).padStart(2, '0');
    const day = String(fechaMinima.getDate()).padStart(2, '0');
    
    fechaInput.min = `${year}-${month}-${day}`;
});

// Validación del formulario
document.getElementById('formAlfoli').addEventListener('submit', function(e) {
    const cantidad = document.getElementById('cantidad').value;
    const fechaCaducidad = new Date(document.getElementById('fecha_caducidad').value);
    const fechaMinima = new Date();
    fechaMinima.setDate(fechaMinima.getDate() + 59);

    if (cantidad < 1 || cantidad > 9) {
        e.preventDefault();
        Swal.fire('Error', 'La cantidad debe estar entre 1 y 9.', 'error');
        return;
    }

    if (fechaCaducidad <= fechaMinima) {
        e.preventDefault();
        Swal.fire('Error', 'La fecha de caducidad debe ser al menos 60 días posterior a hoy.', 'error');
        return;
    }
});
</script>
@endpush
@endsection