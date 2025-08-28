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
        // Perbarui kolom status dengan enum baru
        Schema::table('peminjamen', function (Blueprint $table) {
            // Hapus default terlebih dahulu
            $table->dropColumn('status');
        });

        // Tambahkan kembali kolom status dengan enum yang diperluas
        Schema::table('peminjamen', function (Blueprint $table) {
            $table->enum('status', ['pending', 'dipinjam', 'dikembalikan', 'terlambat', 'ditolak', 'hilang'])
                  ->default('pending')
                  ->after('tanggal_kembali');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjamen', function (Blueprint $table) {
            //
        });
    }
};
