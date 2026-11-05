<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);


function crearCurso(): Curso
{
    return Curso::create([
        'sigla' => 'IF6100',
        'nombre_curso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);
}

function crearDocente(int $codigo, string $nombre, string $apellidos): Docente
{
    return Docente::create([
        'cod_empleado' => $codigo,
        'nombre_empleado' => $nombre,
        'apellidos_empleado' => $apellidos,
    ]);
}


function crearGrupo01(Curso $curso, array $atributos = []): Grupo
{
    return Grupo::create(array_merge([
        'numero_ciclo' => 2,
        'ano' => 2026,
        'numero_grupo' => 1,
        'abierto' => false,
        'curso_id' => $curso->id,
    ], $atributos));
}



test('CP-01: registra un grupo con toda su información y su curso', function () {
    $curso = crearCurso();

    $id = crearGrupo01($curso, ['abierto' => true])->id;

    $grupo = Grupo::find($id);

    expect($grupo->numero_ciclo)->toBe(2)
        ->and($grupo->ano)->toBe(2026)
        ->and($grupo->numero_grupo)->toBe(1)
        ->and($grupo->abierto)->toBeTrue()          // booleano, no 1
        ->and($grupo->curso->sigla)->toBe('IF6100')
        ->and($grupo->curso->creditos)->toBe(4)
        ->and(Grupo::count())->toBe(1);
});

test('CP-02: un curso tiene varios grupos y se distinguen los abiertos', function () {
    $curso = crearCurso();

    crearGrupo01($curso, ['numero_grupo' => 1, 'abierto' => true]);
    crearGrupo01($curso, ['numero_grupo' => 2, 'abierto' => true]);
    crearGrupo01($curso, ['numero_grupo' => 3, 'abierto' => false]);

    $grupos = $curso->grupos;
    $abiertos = $grupos->where('abierto', true);

    expect($grupos)->toHaveCount(3)
        ->and($abiertos)->toHaveCount(2)
        ->and($abiertos->pluck('numero_grupo')->sort()->values()->all())->toBe([1, 2])
        ->and(Grupo::pluck('curso_id')->unique()->all())->toBe([$curso->id]);
});

test('CP-03: un grupo acepta dos docentes', function () {
    $curso = crearCurso();
    $docente1001 = crearDocente(1001, 'Ana', 'Mora Vargas');
    $docente1002 = crearDocente(1002, 'Luis', 'Rojas Solano');
    $grupo = crearGrupo01($curso);

    $grupo->asignarDocente($docente1001);
    $grupo->asignarDocente($docente1002);

    expect($grupo->docentes)->toHaveCount(2)
        ->and($grupo->docentes->pluck('cod_empleado')->sort()->values()->all())->toBe([1001, 1002])
        ->and($docente1001->grupos->pluck('id')->all())->toContain($grupo->id)
        ->and(DB::table('grupo_docente')->count())->toBe(2);
});

test('CP-04: un grupo rechaza un tercer docente', function () {
    $curso = crearCurso();
    $docente1001 = crearDocente(1001, 'Ana', 'Mora Vargas');
    $docente1002 = crearDocente(1002, 'Luis', 'Rojas Solano');
    $docente1003 = crearDocente(1003, 'Carla', 'Jiménez Araya');
    $grupo = crearGrupo01($curso);
    $grupo->asignarDocente($docente1001);
    $grupo->asignarDocente($docente1002);

    expect(fn () => $grupo->asignarDocente($docente1003))->toThrow(DomainException::class);

    expect($grupo->docentes()->count())->toBe(2)
        ->and(DB::table('grupo_docente')->where('docente_id', $docente1003->id)->exists())->toBeFalse();
});

test('CP-05: un grupo con 14 estudiantes no puede abrirse', function () {
    $curso = crearCurso();
    $docente1001 = crearDocente(1001, 'Ana', 'Mora Vargas');
    $grupo = crearGrupo01($curso);
    $grupo->asignarDocente($docente1001);

    Estudiante::factory()->count(14)->for($docente1001, 'consejero')->create()
        ->each(fn ($estudiante) => $grupo->matricular($estudiante));

    expect($grupo->puedeAbrirse())->toBeFalse();
    expect(fn () => $grupo->abrir())->toThrow(DomainException::class);
    expect($grupo->fresh()->abierto)->toBeFalse();
});

test('CP-06: un grupo con 15 estudiantes y un docente se abre', function () {
    $curso = crearCurso();
    $docente1001 = crearDocente(1001, 'Ana', 'Mora Vargas');
    $grupo = crearGrupo01($curso);
    $grupo->asignarDocente($docente1001);

    Estudiante::factory()->count(15)->for($docente1001, 'consejero')->create()
        ->each(fn ($estudiante) => $grupo->matricular($estudiante));

    expect($grupo->puedeAbrirse())->toBeTrue();
    expect(fn () => $grupo->abrir())->not->toThrow(DomainException::class);
    expect($grupo->fresh()->abierto)->toBeTrue();
});