<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docente extends Model
{
    protected $fillable = ['cod_empleado', 'nombre_empleado', 'apellidos_empleado'];

   public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'consejero_id');
    }

    }