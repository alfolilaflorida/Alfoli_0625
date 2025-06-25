<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DetalleAlfoli extends Model
{
    use HasFactory;

    protected $table = 'detalle_alfoli';

    protected $fillable = [
        'id_hermano',
        'id_articulo',
        'cantidad',
        'fecha_caducidad',
        'fecha_registro',
        'id_usrregistra'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'fecha_caducidad' => 'date',
        'fecha_registro' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function hermano()
    {
        return $this->belongsTo(Hermano::class, 'id_hermano');
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'id_articulo');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usrregistra', 'nombre_usuario');
    }

    public function getEstadoCaducidadAttribute()
    {
        $hoy = Carbon::now();
        $fechaLimite = $hoy->copy()->addDays(59);

        if ($this->fecha_caducidad < $hoy) {
            return 'vencido';
        } elseif ($this->fecha_caducidad <= $fechaLimite) {
            return 'pronto_vencer';
        }

        return 'ok';
    }

    public function getDiasParaCaducidadAttribute()
    {
        return Carbon::now()->diffInDays($this->fecha_caducidad, false);
    }

    public function scopeVencidos($query)
    {
        return $query->where('fecha_caducidad', '<', Carbon::now());
    }

    public function scopeProntoAVencer($query, $dias = 59)
    {
        return $query->where('fecha_caducidad', '<=', Carbon::now()->addDays($dias))
                    ->where('fecha_caducidad', '>=', Carbon::now());
    }

    public function scopeDelMes($query, $mes = null)
    {
        $mes = $mes ?? Carbon::now()->month;
        return $query->whereMonth('fecha_registro', $mes);
    }
}