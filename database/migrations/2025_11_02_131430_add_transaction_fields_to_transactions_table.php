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
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('statut', ['EN_ATTENTE', 'VALIDEE', 'REJETEE'])->default('EN_ATTENTE');
            $table->uuid('cree_par')->nullable();
            $table->foreign('cree_par')->references('id')->on('users')->onDelete('set null');
            $table->string('numero_transaction')->nullable()->unique();
            $table->decimal('solde_avant', 15, 2)->default(0);
            $table->decimal('solde_apres', 15, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['cree_par']);
            $table->dropColumn(['statut', 'cree_par', 'numero_transaction', 'solde_avant', 'solde_apres']);
        });
    }
};
