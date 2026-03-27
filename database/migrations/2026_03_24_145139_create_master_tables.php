<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // master_categories: untuk UI management kategori (icon, warna)
        Schema::create('master_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('icon')->nullable();
            $table->string('bg_color')->nullable();
            $table->string('text_color')->nullable();
            $table->timestamps();
        });

        // master_levels: loyalitas pelanggan berdasarkan total spending
        Schema::create('master_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('min_spending', 15, 2)->default(0);
            $table->string('badge_color')->nullable();
            $table->timestamps();
        });

        // Seed: Default Categories
        DB::table('master_categories')->insert([
            ['name' => 'REGULER',    'icon' => 'user',            'bg_color' => 'bg-slate-50',     'text_color' => 'text-slate-500',  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'EVENT',      'icon' => 'megaphone',       'bg_color' => 'bg-indigo-50',    'text_color' => 'text-indigo-600', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PRIORITAS',  'icon' => 'crown',           'bg_color' => 'bg-amber-50',     'text_color' => 'text-amber-600',  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BIG_SPENDER','icon' => 'dollar-sign',     'bg_color' => 'bg-emerald-50',   'text_color' => 'text-emerald-600','created_at' => now(), 'updated_at' => now()],
            ['name' => 'DRINKER',    'icon' => 'glass-water',     'bg_color' => 'bg-blue-50',      'text_color' => 'text-blue-600',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PARTY',      'icon' => 'sparkles',        'bg_color' => 'bg-purple-50',    'text_color' => 'text-purple-600', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DINNER',     'icon' => 'utensils-crossed','bg_color' => 'bg-orange-50',    'text_color' => 'text-orange-600', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'LUNCH',      'icon' => 'utensils',        'bg_color' => 'bg-rose-50',      'text_color' => 'text-rose-600',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'FAMILY',     'icon' => 'users',           'bg_color' => 'bg-cyan-50',      'text_color' => 'text-cyan-600',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'YOUNGSTER',  'icon' => 'smile',           'bg_color' => 'bg-pink-50',      'text_color' => 'text-pink-600',   'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed: Default Levels (urut dari lowest — id 1 = Bronze)
        DB::table('master_levels')->insert([
            ['name' => 'Bronze',   'min_spending' => 0,         'badge_color' => 'bg-orange-100 text-orange-800', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Silver',   'min_spending' => 1000000,   'badge_color' => 'bg-slate-200 text-slate-800',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gold',     'min_spending' => 5000000,   'badge_color' => 'bg-yellow-200 text-yellow-800', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Platinum', 'min_spending' => 20000000,  'badge_color' => 'bg-blue-100 text-blue-800',     'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('master_levels');
        Schema::dropIfExists('master_categories');
    }
};
