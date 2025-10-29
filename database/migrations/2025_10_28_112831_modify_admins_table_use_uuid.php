<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE admins ALTER COLUMN id DROP DEFAULT');
        DB::statement('ALTER TABLE admins ALTER COLUMN id TYPE UUID USING id::text::uuid');
        DB::statement('ALTER TABLE admins ALTER COLUMN id SET DEFAULT gen_random_uuid()');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropPrimary('id');
            $table->uuid('id')->change(); // Revert to uuid, not bigIncrements
            $table->primary('id');
        });
    }
};
