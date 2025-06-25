@extends('layouts.app')

@section('title', 'Cambiar Contraseña')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h4 class="mb-0">
                    <i class="fas fa-key me-2"></i>Es necesario cambiar tu contraseña
                </h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Por seguridad, debes cambiar tu contraseña antes de continuar.
                </div>

                <form id="formCambiarPassword">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="nueva_clave" class="form-label">Nueva Contraseña</label>
                        <input type="password" 
                               class="form-control" 
                               id="nueva_clave" 
                               name="nueva_clave" 
                               required 
                               minlength="8">
                        <div class="form-text">La contraseña debe tener al menos 8 caracteres.</div>
                    </div>

                    <div class="mb-3">
                        <label for="nueva_clave_confirmation" class="form-label">Confirmar Contraseña</label>
                        <input type="password" 
                               class="form-control" 
                               id="nueva_clave_confirmation" 
                               name="nueva_clave_confirmation" 
                               required 
                               minlength="8">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Actualizar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('formCambiarPassword').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const nuevaClave = document.getElementById('nueva_clave').value;
    const confirmarClave = document.getElementById('nueva_clave_confirmation').value;
    
    if (nuevaClave !== confirmarClave) {
        Swal.fire('Error', 'Las contraseñas no coinciden.', 'error');
        return;
    }
    
    if (nuevaClave.length < 8) {
        Swal.fire('Error', 'La contraseña debe tener al menos 8 caracteres.', 'error');
        return;
    }
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('{{ route("password.change") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire('Éxito', result.message, 'success').then(() => {
                @if(auth()->user()->isAdmin())
                    window.location.href = '{{ route("dashboard") }}';
                @elseif(auth()->user()->isEditor())
                    window.location.href = '{{ route("alfoli.index") }}';
                @else
                    window.location.href = '{{ route("dashboard") }}';
                @endif
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Ocurrió un error al actualizar la contraseña.', 'error');
    }
});
</script>
@endpush
@endsection