<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // master_tags: tag master untuk kategorisasi tipe kunjungan
        Schema::create('master_tags', function (Blueprint $table) {
            $table->id();
            $table->string('group_name', 30); // PURPOSE, PRODUCT, VALUE, TIME
            $table->string('name', 50);
            $table->timestamps();

            // Satu group tidak boleh punya nama tag yang sama
            $table->unique(['group_name', 'name'], 'uniq_master_tags_group_name');
        });

        // booking_tags: pivot many-to-many booking <-> master_tag
        Schema::create('booking_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('tag_id')          // ← FIXED: hapus nullable()
                  ->constrained('master_tags')
                  ->onDelete('cascade');
            $table->timestamps();

            // Satu booking tidak boleh punya tag yang sama dua kali
            $table->unique(['booking_id', 'tag_id'], 'uniq_booking_tag');
        });

        // Seed: Initial Tags
        $tags = [
            'PURPOSE' => ['Dining', 'Family', 'Celebration', 'Party', 'Corporate', 'Community'],
            'PRODUCT' => ['Regular F&B', 'AYCE', 'AYCD', 'Buffet', 'Iftar Buffet', 'Alcohol'],
            'VALUE'   => ['VIP', 'High Spender', 'Repeat', 'First Timer', 'Inactive'],
            'TIME'    => ['Weekday Day', 'Weekday Night', 'Weekend Day', 'Weekend Night'],
        ];

        $rows = [];
        foreach ($tags as $group => $names) {
            foreach ($names as $name) {
                $rows[] = [
                    'group_name' => $group,
                    'name'       => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('master_tags')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_tags');
        Schema::dropIfExists('master_tags');
    }
};
