<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grupo extends Model
{
    public const MAXIMO_DOCENTES = 2;
    public const MINIMO_ESTUDIANTES = 15;

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
        return $this->belongsToMany(Estudiante::class, 'estudiantes_matriculados')
            ->using(EstudianteMatriculado::class)
            ->withPivot('nota');
    }

     public function asignarDocente(Docente $docente): void
    {
        if ($this->docentes()->count() >= self::MAXIMO_DOCENTES) {
            throw new DomainException('Un grupo no puede tener más de 2 docentes.');
        }

        $this->docentes()->attach($docente->id);
    }

    public function matricular(Estudiante $estudiante): void
    {
        $this->estudiantes()->attach($estudiante->id);
    }

    public function puedeAbrirse(): bool
    {
        return $this->estudiantes()->count() >= self::MINIMO_ESTUDIANTES
            && $this->docentes()->count() >= 1;
    }

    public function abrir(): void
    {
        if (! $this->puedeAbrirse()) {
            throw new DomainException('El grupo necesita al menos 15 estudiantes y 1 docente para abrirse.');
        }

        $this->update(['abierto' => true]);
    }
}