<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Générer des UUIDs pour les comptes existants
        $comptes = DB::table('comptes')->get();

        foreach ($comptes as $compte) {
            $newUuid = (string) Str::uuid();

            // Mettre à jour le compte avec le nouvel UUID
            DB::table('comptes')
                ->where('id', $compte->id)
                ->update(['id' => $newUuid]);

            // Mettre à jour les références dans transactions
            DB::table('transactions')
                ->where('compte_id', $compte->id)
                ->update(['compte_id' => $newUuid]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cette migration n'est pas réversible car elle change les IDs
        // En production, il faudrait une stratégie de sauvegarde des anciens IDs
    }
};