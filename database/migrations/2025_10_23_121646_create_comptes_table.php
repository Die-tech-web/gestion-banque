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
        Schema::create('comptes', function (Blueprint $table) {
            $table->id();
            $table->string('numeroCompte')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['courant', 'epargne', 'cheque']);
            $table->string('devise')->default('XOF');
            $table->date('dateCreation');
            $table->enum('statut', ['actif', 'ferme', 'suspendu', 'bloque'])->default('actif');
            $table->string('motifBlocage')->nullable();
            $table->timestamp('derniereModification')->useCurrent();
            $table->integer('version')->default(1);
            $table->boolean('archived')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
