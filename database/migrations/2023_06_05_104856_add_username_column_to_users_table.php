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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('username')->unique()->after('name')->nullable();
            $table->string('google_id')->unique()->nullable();
            $table->char('gender')->nullable();
            $table->string('photo')->nullable();
            $table->string('avatar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('username');
            $table->dropColumn('google_id');
            $table->dropColumn('gender');
            $table->dropColumn('photo');
            $table->dropColumn('avatar');
        });
    }
};
