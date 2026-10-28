<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Grupo extends Model
{
    protected $fillable = ['numero_ciclo', 'ano', 'numero_grupo', 'abierto', 'curso_id'];

    protected $casts = [
        'abierto' => 'boolean',
    ];

     public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'grupo_docente');
    }

    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_matriculado')
            ->using(EstudianteMatriculado::class)
            ->withPivot('nota');
    }

    public static function crearConDocentes(array $datos, array $docentes): self
    {
        if (count($docentes) < 1 || count($docentes) > 2) {
            throw new InvalidArgumentException('Un grupo debe ser impartido por 1 o 2 docentes.');
        }

        return DB::transaction(function () use ($datos, $docentes) {
            $grupo = self::create($datos);

            foreach ($docentes as $docente) {
                $grupo->docentes()->attach($docente->id);
            }

            return $grupo;
        });
    }

    public function matricular(Estudiante $estudiante): void
    {
        $this->estudiantes()->attach($estudiante->id);
    }

    public function registrarNota(Estudiante $estudiante, float $nota): void
    {
        $this->estudiantes()->updateExistingPivot($estudiante->id, ['nota' => $nota]);
    }
}