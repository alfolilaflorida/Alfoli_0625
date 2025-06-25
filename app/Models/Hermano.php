<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hermano extends Model
{
    use HasFactory;

    protected $table = 'hermanos';

    protected $fillable = [
        'nombres',
        'apellidos',
        'telefono'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function detallesAlfoli()
    {
        return $this->hasMany(DetalleAlfoli::class, 'id_hermano');
    }

    public function getNombreCompletoAttribute()
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }

    public function scopeActivos($query)
    {
        return $query->whereNotNull('nombres');
    }
}