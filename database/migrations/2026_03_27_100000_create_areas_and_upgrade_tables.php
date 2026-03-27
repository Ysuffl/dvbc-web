<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Floor & Table Management
 *
 * Perubahan:
 * 1. Buat tabel `areas` sebagai master area/lantai
 * 2. Tambah kolom `area_id` (FK) + `min_spending` + `capacity` ke `tables`
 * 3. Remove kolom lama `area_id` (string)
 *
 * Flow:
 *   areas  →  tables.area_id (FK)
 *   tables.min_spending diakses Flutter saat form booking
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel areas
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                           // Nama area: "VIP Room", "Outdoor"
            $table->text('description')->nullable();               // Deskripsi opsional
            $table->unsignedTinyInteger('floor_number')->default(1); // Lantai berapa
            $table->boolean('is_active')->default(true);           // Bisa di-disable
            $table->timestamps();

            $table->index('is_active', 'idx_areas_active');
        });

        // 2. Seed area default (sesuai area_id string yang sudah ada)
        DB::table('areas')->insert([
            ['name' => 'Main Hall',    'description' => 'Area utama lantai 1', 'floor_number' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'VIP Room',     'description' => 'Area VIP eksklusif',   'floor_number' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rooftop',      'description' => 'Area outdoor lantai 2', 'floor_number' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Private Room', 'description' => 'Ruang private berbayar','floor_number' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Tambah kolom baru ke `tables`
        Schema::table('tables', function (Blueprint $table) {
            // FK ke areas — awalnya nullable agar bisa di-fill setelah area seed
            $table->foreignId('area_fk_id')
                  ->nullable()
                  ->constrained('areas')
                  ->nullOnDelete()
                  ->after('status');

            // Minimum cash yang harus dibayar tamu saat memesan meja ini
            $table->decimal('min_spending', 15, 2)->default(0)->after('area_fk_id');

            // Kapasitas meja (jumlah kursi)
            $table->unsignedTinyInteger('capacity')->default(4)->after('min_spending');
        });

        // 4. Migrasi data: set area_fk_id ke area pertama (Main Hall) untuk semua meja yang ada
        //    (karena area_id lama berupa string, relasi FK tidak bisa di-map otomatis)
        $mainHallId = DB::table('areas')->where('name', 'Main Hall')->value('id');
        if ($mainHallId) {
            DB::table('tables')->update(['area_fk_id' => $mainHallId]);
        }
    }

    public function down(): void
    {
        // Hapus kolom baru dulu sebelum hapus tabel areas
        Schema::table('tables', function (Blueprint $table) {
            $table->dropConstrainedForeignId('area_fk_id');
            $table->dropColumn(['min_spending', 'capacity']);
        });

        Schema::dropIfExists('areas');
    }
};
