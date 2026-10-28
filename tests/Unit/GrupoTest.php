<?php

namespace Tests\Unit;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class GrupoTest extends TestCase
{
    use RefreshDatabase;

    private function crearDocente(int $codigo, string $nombre, string $apellidos): Docente
    {
        return Docente::create([
            'cod_empleado' => $codigo,
            'nombre_empleado' => $nombre,
            'apellidos_empleado' => $apellidos,
        ]);
    }

    private function crearCurso(): Curso
    {
        return Curso::create([
            'sigla' => 'IF-6100',
            'nombre_curso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);
    }

    public function test_un_grupo_contiene_toda_su_informacion(): void
    {
        $curso = $this->crearCurso();
        $docente1 = $this->crearDocente(1001, 'Laura', 'Mora Solís');
        $docente2 = $this->crearDocente(1002, 'Carlos', 'Vargas Rojas');
        $consejero = $this->crearDocente(1003, 'Marta', 'Jiménez Cruz');

        $grupo = Grupo::crearConDocentes([
            'numero_ciclo' => 2,
            'ano' => 2026,
            'numero_grupo' => 1,
            'abierto' => true,
            'curso_id' => $curso->id,
        ], [$docente1, $docente2]);

         for ($i = 1; $i <= 15; $i++) {
            $estudiante = Estudiante::create([
                'carne' => sprintf('B%05d', 10000 + $i),
                'nombre' => "Estudiante $i",
                'apellidos' => 'Apellido Prueba',
                'consejero_id' => $consejero->id,
            ]);

            $grupo->matricular($estudiante);
            $grupo->registrarNota($estudiante, 70 + $i); // nota final
        }

        $grupo = Grupo::with(['curso', 'docentes', 'estudiantes.consejero'])->find($grupo->id);

        $this->assertSame(2, $grupo->numero_ciclo);
        $this->assertSame(2026, $grupo->ano);
        $this->assertSame(1, $grupo->numero_grupo);
        $this->assertTrue($grupo->abierto);

         $this->assertSame('IF-6100', $grupo->curso->sigla);
        $this->assertSame('Análisis y Diseño de Sistemas', $grupo->curso->nombre_curso);
        $this->assertSame(4, $grupo->curso->creditos);

        $this->assertCount(2, $grupo->docentes);
        $this->assertEqualsCanonicalizing(
            [1001, 1002],
            $grupo->docentes->pluck('cod_empleado')->all()
        );

         $this->assertGreaterThanOrEqual(15, $grupo->estudiantes->count());
        $primero = $grupo->estudiantes->firstWhere('carne', 'B10001');
        $this->assertSame(71.0, $primero->pivot->nota);

        $this->assertSame('Jiménez Cruz', $primero->consejero->apellidos_empleado);

        $this->assertCount(15, $consejero->estudiantes);
    }

    public function test_la_nota_es_nula_hasta_que_termina_el_curso(): void
    {
        $curso = $this->crearCurso();
        $docente = $this->crearDocente(1001, 'Laura', 'Mora Solís');
        $estudiante = Estudiante::create([
            'carne' => 'B20001',
            'nombre' => 'Ana',
            'apellidos' => 'Pérez Lara',
            'consejero_id' => $docente->id,
        ]);

        $grupo = Grupo::crearConDocentes([
            'numero_ciclo' => 2, 'ano' => 2026, 'numero_grupo' => 1, 'curso_id' => $curso->id,
        ], [$docente]);

        $grupo->matricular($estudiante);
        $this->assertNull($grupo->estudiantes()->first()->pivot->nota);

        $grupo->registrarNota($estudiante, 88.5);
        $this->assertSame(88.5, $grupo->estudiantes()->first()->pivot->nota);
    }

    public function test_un_grupo_sin_docentes_no_se_puede_crear(): void
    {
        $curso = $this->crearCurso();

        $this->expectException(InvalidArgumentException::class);

        Grupo::crearConDocentes([
            'numero_ciclo' => 2, 'ano' => 2026, 'numero_grupo' => 1, 'curso_id' => $curso->id,
        ], []);
    }

    public function test_un_grupo_no_puede_tener_mas_de_dos_docentes(): void
    {
        $curso = $this->crearCurso();
        $docentes = [
            $this->crearDocente(1001, 'Laura', 'Mora Solís'),
            $this->crearDocente(1002, 'Carlos', 'Vargas Rojas'),
            $this->crearDocente(1003, 'Marta', 'Jiménez Cruz'),
        ];

        $this->expectException(InvalidArgumentException::class);

        Grupo::crearConDocentes([
            'numero_ciclo' => 2, 'ano' => 2026, 'numero_grupo' => 1, 'curso_id' => $curso->id,
        ], $docentes);
    }
}