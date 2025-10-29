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
        Schema::create('archived_comptes', function (Blueprint $table) {
            $table->id();
            $table->uuid('original_id'); // ID original du compte
            $table->string('numeroCompte');
            $table->uuid('client_id');
            $table->enum('type', ['courant', 'epargne', 'cheque']);
            $table->string('devise')->default('XOF');
            $table->date('dateCreation');
            $table->enum('statut', ['actif', 'ferme', 'suspendu', 'bloque']);
            $table->string('motifBlocage')->nullable();
            $table->timestamp('dateBlocage')->nullable();
            $table->timestamp('dateDeblocagePrevue')->nullable();
            $table->timestamp('derniereModification');
            $table->integer('version')->default(1);
            $table->timestamp('dateFermeture')->nullable();
            $table->timestamp('archived_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_comptes');
    }
};
