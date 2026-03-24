<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('icon')->nullable();
            $table->string('bg_color')->nullable();
            $table->string('text_color')->nullable();
            $table->timestamps();
        });

        Schema::create('master_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('min_spending', 15, 2)->default(0);
            $table->string('badge_color')->nullable();
            $table->timestamps();
        });

        // Insert Default Data
        DB::table('master_categories')->insert([
            ['name' => 'REGULER', 'icon' => 'user', 'bg_color' => 'bg-slate-50', 'text_color' => 'text-slate-500'],
            ['name' => 'EVENT', 'icon' => 'megaphone', 'bg_color' => 'bg-indigo-50/50', 'text_color' => 'text-indigo-600'],
            ['name' => 'PRIORITAS', 'icon' => 'crown', 'bg_color' => 'bg-amber-50/50', 'text_color' => 'text-amber-600'],
            ['name' => 'BIG_SPENDER', 'icon' => 'dollar-sign', 'bg_color' => 'bg-emerald-50/50', 'text_color' => 'text-emerald-600'],
            ['name' => 'DRINKER', 'icon' => 'glass-water', 'bg_color' => 'bg-blue-50/50', 'text_color' => 'text-blue-600'],
            ['name' => 'PARTY', 'icon' => 'sparkles', 'bg_color' => 'bg-purple-50/50', 'text_color' => 'text-purple-600'],
            ['name' => 'DINNER', 'icon' => 'utensils-crossed', 'bg_color' => 'bg-orange-50/50', 'text_color' => 'text-orange-600'],
            ['name' => 'LUNCH', 'icon' => 'utensils', 'bg_color' => 'bg-rose-50/50', 'text_color' => 'text-rose-600'],
            ['name' => 'FAMILY', 'icon' => 'users', 'bg_color' => 'bg-cyan-50/50', 'text_color' => 'text-cyan-600'],
            ['name' => 'YOUNGSTER', 'icon' => 'smile', 'bg_color' => 'bg-pink-50/50', 'text_color' => 'text-pink-600'],
        ]);

        DB::table('master_levels')->insert([
            ['name' => 'Bronze', 'min_spending' => 0, 'badge_color' => 'bg-orange-100 text-orange-800'],
            ['name' => 'Silver', 'min_spending' => 1000000, 'badge_color' => 'bg-slate-200 text-slate-800'],
            ['name' => 'Gold', 'min_spending' => 5000000, 'badge_color' => 'bg-yellow-200 text-yellow-800'],
            ['name' => 'Platinum', 'min_spending' => 20000000, 'badge_color' => 'bg-blue-100 text-blue-800'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_levels');
        Schema::dropIfExists('master_categories');
    }
};
