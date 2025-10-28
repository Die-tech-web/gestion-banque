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
        DB::statement('ALTER TABLE oauth_access_tokens DROP CONSTRAINT IF EXISTS oauth_access_tokens_user_id_foreign');
        DB::statement('DROP INDEX IF EXISTS oauth_access_tokens_user_id_index');
        DB::statement('ALTER TABLE oauth_access_tokens ALTER COLUMN user_id TYPE UUID USING user_id::text::uuid');
        DB::statement('CREATE INDEX oauth_access_tokens_user_id_index ON oauth_access_tokens (user_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->index('user_id');
        });
    }
};
