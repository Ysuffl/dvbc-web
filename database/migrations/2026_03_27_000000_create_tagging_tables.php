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
        Schema::create('master_tags', function (Blueprint $table) {
            $table->id();
            $table->string('group_name'); // PURPOSE, PRODUCT, VALUE, TIME
            $table->string('name');
            $table->timestamps();
            
            $table->unique(['group_name', 'name']);
        });

        Schema::create('booking_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->nullable()->constrained('master_tags')->onDelete('cascade');
            // Alternatively, some tags might be free-text? 
            // Based on the user request, they want "master tagging data" so we stick to IDs.
            $table->timestamps();
        });

        // Initial Data for Master Tagging
        $tags = [
            'PURPOSE' => ['Dining', 'Family', 'Celebration', 'Party', 'Corporate', 'Community'],
            'PRODUCT' => ['Regular F&B', 'AYCE', 'AYCD', 'Buffet', 'Iftar Buffet', 'Alcohol'],
            'VALUE' => ['VIP', 'High Spender', 'Repeat', 'First Timer', 'Inactive'],
            'TIME' => ['Weekday Day', 'Weekday Night', 'Weekend Day', 'Weekend Night'],
        ];

        foreach ($tags as $group => $names) {
            foreach ($names as $name) {
                DB::table('master_tags')->insert([
                    'group_name' => $group,
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_tags');
        Schema::dropIfExists('master_tags');
    }
};
