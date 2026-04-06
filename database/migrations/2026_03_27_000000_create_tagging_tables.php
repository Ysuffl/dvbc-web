<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // master_tag_groups: grup kategori tag (PURPOSE, PRODUCT, etc)
        Schema::create('master_tag_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->timestamps();
        });

        // master_tags: tag master untuk kategorisasi tipe kunjungan
        Schema::create('master_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_tag_group_id')->constrained('master_tag_groups')->onDelete('cascade');
            $table->string('name', 50);
            $table->string('abbreviation', 20)->nullable(); // Untuk header export
            $table->timestamps();

            // Satu group tidak boleh punya nama tag yang sama
            $table->unique(['master_tag_group_id', 'name'], 'uniq_master_tags_group_name');
        });

        // booking_tags: pivot many-to-many booking <-> master_tag
        Schema::create('booking_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('master_tags')->onDelete('cascade');
            $table->timestamps();

            // Satu booking tidak boleh punya tag yang sama dua kali
            $table->unique(['booking_id', 'tag_id'], 'uniq_booking_tag');
        });

        // Seed: Initial Groups & Tags
        $data = [
            'PURPOSE' => [
                'Dining' => 'pu_din', 
                'Family' => 'pu_fam', 
                'Celebration' => 'pu_celeb', 
                'Party' => 'pu_party', 
                'Corporate' => 'pu_corp', 
                'Community' => 'pu_comm',
                'Lunch' => 'pu_lunch'
            ],
            'PRODUCT' => [
                'Regular F&B' => 'pr_reg', 
                'AYCE' => 'pr_ayce', 
                'AYCD' => 'pr_aycd', 
                'Buffet' => 'pr_buff', 
                'Iftar Buffet' => 'pr_iftar', 
                'Alcohol' => 'pr_alc'
            ],
            'VALUE'   => [
                'VIP' => 'val_vip', 
                'High Spender' => 'val_hsp', 
                'Repeat' => 'val_rpt', 
                'First Timer' => 'val_fst', 
                'Inactive' => 'val_ina'
            ],
            'TIME'    => [
                'Weekday Day' => 'time_wdd', 
                'Weekday Night' => 'time_wdn', 
                'Weekend Day' => 'time_wed', 
                'Weekend Night' => 'time_wen'
            ],
        ];

        foreach ($data as $groupName => $tags) {
            $groupId = DB::table('master_tag_groups')->insertGetId([
                'name' => $groupName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($tags as $name => $abbr) {
                DB::table('master_tags')->insert([
                    'master_tag_group_id' => $groupId,
                    'name'                => $name,
                    'abbreviation'        => $abbr,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_tags');
        Schema::dropIfExists('master_tags');
        Schema::dropIfExists('master_tag_groups');
    }
};
