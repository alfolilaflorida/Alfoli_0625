<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    use HasFactory;

    protected $table = 'articulos';

    protected $fillable = [
        'codigo_barra',
        'descripcion',
        'cantidad',
        'mes_articulo'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function detallesAlfoli()
    {
        return $this->hasMany(DetalleAlfoli::class, 'id_articulo');
    }

    public function scopeDelMes($query, $mes)
    {
        return $query->where('mes_articulo', $mes);
    }

    public function scopeDelMesActual($query)
    {
        $mesActual = ucfirst(now()->locale('es')->translatedFormat('F'));
        return $query->where('mes_articulo', $mesActual);
    }
}