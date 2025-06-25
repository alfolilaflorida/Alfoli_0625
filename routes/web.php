<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AlfoliController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\HermanoController;
use App\Http\Controllers\ProductosVencimientoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\AlertaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rutas de autenticación
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Cambio de contraseña obligatorio
Route::middleware('auth')->group(function () {
    Route::get('/cambiar-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/cambiar-password', [AuthController::class, 'changePassword']);
});

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    
    // Dashboard - Acceso para admin, editor, visualizador
    Route::middleware(['role:admin,editor,visualizador'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/export-excel', [DashboardController::class, 'exportExcel'])->name('dashboard.export.excel');
        Route::get('/dashboard/export-pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.export.pdf');
    });

    // Gestión de Alfolí - Acceso para admin y editor
    Route::middleware(['role:admin,editor'])->group(function () {
        Route::get('/alfoli', [AlfoliController::class, 'index'])->name('alfoli.index');
        Route::get('/alfoli/data', [AlfoliController::class, 'getData'])->name('alfoli.data');
        Route::get('/alfoli/crear', [AlfoliController::class, 'create'])->name('alfoli.create');
        Route::post('/alfoli', [AlfoliController::class, 'store'])->name('alfoli.store');
        
        // Artículos
        Route::get('/articulos/crear', [ArticuloController::class, 'create'])->name('articulos.create');
        Route::post('/articulos', [ArticuloController::class, 'store'])->name('articulos.store');
        
        // Hermanos
        Route::get('/hermanos/crear', [HermanoController::class, 'create'])->name('hermanos.create');
        Route::post('/hermanos', [HermanoController::class, 'store'])->name('hermanos.store');
        
        // Productos Vencimiento
        Route::get('/productos-vencimiento', [ProductosVencimientoController::class, 'index'])->name('productos.vencimiento.index');
        Route::get('/productos-vencimiento/data', [ProductosVencimientoController::class, 'getData'])->name('productos.vencimiento.data');
        Route::put('/productos-vencimiento', [ProductosVencimientoController::class, 'update'])->name('productos.vencimiento.update');
        Route::delete('/productos-vencimiento', [ProductosVencimientoController::class, 'destroy'])->name('productos.vencimiento.destroy');
    });

    // Gestión de Usuarios - Solo admin
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('usuarios', UserController::class);
        Route::post('/usuarios/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('usuarios.toggle-status');
        Route::post('/usuarios/{user}/reset-password', [UserController::class, 'resetPassword'])->name('usuarios.reset-password');
    });

    // Notificaciones - Acceso para admin, editor, visualizador
    Route::middleware(['role:admin,editor,visualizador'])->group(function () {
        Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
        Route::post('/notificaciones/enviar', [NotificacionController::class, 'enviar'])->name('notificaciones.enviar');
    });

    // Alertas - Acceso para admin, editor, visualizador
    Route::middleware(['role:admin,editor,visualizador'])->group(function () {
        Route::get('/alertas', [AlertaController::class, 'index'])->name('alertas.index');
        Route::post('/alertas', [AlertaController::class, 'store'])->name('alertas.store');
    });

    // Página de acceso denegado
    Route::get('/acceso-denegado', function () {
        return view('errors.access-denied');
    })->name('access.denied');
});