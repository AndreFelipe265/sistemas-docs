<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('nome')->after('id');
            $table->string('cpf', 20)->nullable()->after('nome');
            $table->string('email')->nullable()->after('cpf');
            $table->string('telefone')->nullable()->after('email');
            $table->string('endereco')->nullable()->after('telefone');
            $table->string('cidade')->nullable()->after('endereco');
            $table->string('estado', 2)->nullable()->after('cidade');
            $table->string('cep')->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'nome',
                'cpf',
                'email',
                'telefone',
                'endereco',
                'cidade',
                'estado',
                'cep',
            ]);
        });
    }
};