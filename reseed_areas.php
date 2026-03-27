<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Null-out FK agar bisa delete areas
DB::table('tables')->update(['area_fk_id' => null]);
DB::table('areas')->delete();
DB::statement("SELECT setval('areas_id_seq', 1, false)");

// Insert area berdasarkan nama asli dari database sebelum di-null
$knownAreas = [
    ['name' => 'CUP ARENA',         'description' => null, 'floor_number' => 1],
    ['name' => 'MOON AREA',         'description' => null, 'floor_number' => 1],
    ['name' => 'POOL AREA',         'description' => null, 'floor_number' => 1],
    ['name' => 'VIP CABANA & STAR', 'description' => null, 'floor_number' => 1],
    ['name' => 'VIP_OTIC',          'description' => null, 'floor_number' => 1],
];

$now = now();
foreach ($knownAreas as $area) {
    DB::table('areas')->insert(array_merge($area, [
        'is_active'  => true,
        'created_at' => $now,
        'updated_at' => $now,
    ]));
}

// Update FK based on existing area_id string
$areas = DB::table('areas')->get();
foreach ($areas as $area) {
    DB::table('tables')->where('area_id', $area->name)->update(['area_fk_id' => $area->id]);
}

$areaCount  = DB::table('areas')->count();
$tableCount = DB::table('tables')->whereNotNull('area_fk_id')->count();
echo "OK Areas: $areaCount | Tables assigned: $tableCount\n";
foreach (DB::table('areas')->get() as $a) {
    $t = DB::table('tables')->where('area_fk_id', $a->id)->count();
    echo "  [{$a->id}] {$a->name} -> $t tables\n";
}
