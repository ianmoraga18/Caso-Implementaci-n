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
       Schema::create('estudiante_matriculado', function (Blueprint $table) {
        $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
        $table->foreignId('estudiante_id')->constrained('estudiantes');
        
        $table->float('nota')->nullable();
        $table->primary(['grupo_id', 'estudiante_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiante_matriculado');
    }
};
