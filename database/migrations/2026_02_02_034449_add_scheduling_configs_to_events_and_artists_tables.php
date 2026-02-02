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
        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('start_time')->nullable()->after('description');
            $table->integer('performance_duration')->default(5)->after('start_time');
            $table->integer('break_duration')->default(2)->after('performance_duration');
        });

        Schema::table('artists', function (Blueprint $table) {
            $table->integer('lineup_order')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'performance_duration', 'break_duration']);
        });

        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn('lineup_order');
        });
    }
};
