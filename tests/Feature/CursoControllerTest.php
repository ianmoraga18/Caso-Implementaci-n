<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_crea_un_curso_y_lo_guarda_en_la_base_de_datos(): void
    {
        $datos = [
            'sigla' => 'IF-7100',
            'nombre_curso' => 'Ingeniería de Software',
            'creditos' => 4,
        ];

        
        $respuesta = $this->postJson(route('cursos.store'), $datos);

      
        $respuesta->assertCreated()
            ->assertJsonFragment(['sigla' => 'IF-7100']);

   
        $this->assertDatabaseHas('cursos', $datos);
        $this->assertDatabaseCount('cursos', 1);
    }

    public function test_store_rechaza_un_curso_con_datos_invalidos(): void
    {
        $respuesta = $this->postJson(route('cursos.store'), [
            'sigla' => '',
            'nombre_curso' => '',
            'creditos' => 'muchos',
        ]);

        $respuesta->assertUnprocessable()
            ->assertJsonValidationErrors(['sigla', 'nombre_curso', 'creditos']);

        $this->assertDatabaseCount('cursos', 0);
    }

    public function test_store_rechaza_una_sigla_repetida(): void
    {
        $datos = ['sigla' => 'IF-7100', 'nombre_curso' => 'Ingeniería de Software', 'creditos' => 4];

        $this->postJson(route('cursos.store'), $datos)->assertCreated();
        $this->postJson(route('cursos.store'), $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sigla']);

        $this->assertDatabaseCount('cursos', 1);
    }
}