<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre_usuario',
        'nombre_completo',
        'email',
        'clave_hash',
        'rol',
        'activo',
        'cambiar_password',
        'creado_por'
    ];

    protected $hidden = [
        'clave_hash',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'activo' => 'boolean',
        'cambiar_password' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    // Sobrescribir el campo de contraseña para Laravel Auth
    public function getAuthPassword()
    {
        return $this->clave_hash;
    }

    // Sobrescribir el campo de usuario para Laravel Auth
    public function getAuthIdentifierName()
    {
        return 'nombre_usuario';
    }

    public function isAdmin()
    {
        return $this->rol === 'admin';
    }

    public function isEditor()
    {
        return $this->rol === 'editor';
    }

    public function isVisualizador()
    {
        return $this->rol === 'visualizador';
    }

    public function canManage()
    {
        return in_array($this->rol, ['admin', 'editor']);
    }

    public function canView()
    {
        return in_array($this->rol, ['admin', 'editor', 'visualizador']);
    }

    public function getRolDisplayAttribute()
    {
        return match($this->rol) {
            'admin' => 'Administrador',
            'editor' => 'Moderador',
            'visualizador' => 'Consultas',
            default => 'Usuario'
        };
    }

    public function getEstadoDisplayAttribute()
    {
        return $this->activo ? 'Activo' : 'Inactivo';
    }
}