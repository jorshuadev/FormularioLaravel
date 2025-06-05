<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->enum('tipo_documento', ['cedula', 'pasaporte', 'otros']);
            $table->string('nro_documento', 100)->unique();
            $table->string('correo_electronico');
            $table->string('telefono', 20);
            $table->string('ip')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('registro_via', 10)->default('web'); // ✅ Cambiado a VARCHAR(10)
            $table->boolean('notificacion_via_correo')->default(false);
            $table->boolean('notificacion_via_sms')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};