<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('grupos', function (Blueprint $table) {
        $table->id();
        $table->unsignedTinyInteger('numero_ciclo');
        $table->unsignedSmallInteger('ano');
        $table->unsignedSmallInteger('numero_grupo');
        $table->boolean('abierto')->default(true);
        
        $table->foreignId('curso_id')->constrained('cursos');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
