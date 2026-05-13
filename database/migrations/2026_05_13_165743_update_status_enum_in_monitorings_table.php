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
        Schema::table('monitorings', function (Blueprint $table) {
            $table->string('status')->change(); // Temporary change to string to avoid enum issues
        });

        Schema::table('monitorings', function (Blueprint $table) {
            \DB::statement("ALTER TABLE monitorings MODIFY COLUMN status ENUM('Aman', 'Panas', 'Dingin') DEFAULT 'Aman'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            \DB::statement("ALTER TABLE monitorings MODIFY COLUMN status ENUM('Aman', 'Tidak Aman') DEFAULT 'Aman'");
        });
    }
};
