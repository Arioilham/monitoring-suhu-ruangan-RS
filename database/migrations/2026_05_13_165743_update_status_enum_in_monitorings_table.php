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
        // Pastikan kita bisa mengubah kolom (membutuhkan doctrine/dbal)
        // Jika menggunakan MySQL:
        Schema::table('monitorings', function (Blueprint $table) {
            $table->string('status', 20)->change(); // Ubah ke string dulu agar aman
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
            $table->string('status', 20)->change();
        });
        
        \DB::statement("ALTER TABLE monitorings MODIFY COLUMN status ENUM('Aman', 'Tidak Aman') DEFAULT 'Aman'");
    }
};
