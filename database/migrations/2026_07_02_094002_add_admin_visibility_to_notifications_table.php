<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->boolean('is_admin_visible')->default(false)->after('is_read')->index();
            $table->string('admin_context')->nullable()->after('is_admin_visible')->index();
            $table->string('admin_url')->nullable()->after('admin_context');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('notifications')->whereNull('user_id')->delete();

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['is_admin_visible', 'admin_context', 'admin_url']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
