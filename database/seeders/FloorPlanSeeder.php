<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FloorPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET session_replication_role = \'replica\';');
        
        DB::table('bookings')->truncate();
        DB::table('tables')->truncate();
        DB::table('areas')->truncate();
        
        DB::statement('SET session_replication_role = \'origin\';');

        $floorNames = [
            "VIP_OTIC" => ["description" => "Original VIP Area", "floor" => 1],
            "MOON AREA" => ["description" => "Moon Themed Area", "floor" => 1],
            "POOL AREA" => ["description" => "Poolside Tables", "floor" => 1],
            "CUP ARENA" => ["description" => "Cup Arena Section", "floor" => 2],
            "VIP CABANA & STAR" => ["description" => "Premium Cabana & Star Area", "floor" => 2]
        ];

        $areaIds = [];
        foreach ($floorNames as $name => $meta) {
            $areaId = DB::table('areas')->insertGetId([
                'name' => $name,
                'description' => $meta['description'],
                'floor_number' => $meta['floor'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $areaIds[$name] = $areaId;
        }

        $allTables = [];

        // 1. VIP_OTIC
        $otic_tables = [
            ["code" => "09", "x" => 150, "y" => 200, "shape" => "rectangle"],
            ["code" => "010", "x" => 260, "y" => 200, "shape" => "rectangle"],
            ["code" => "011", "x" => 370, "y" => 200, "shape" => "rectangle"],
            ["code" => "08", "x" => 260, "y" => 350, "shape" => "rectangle"],
            ["code" => "06", "x" => 150, "y" => 400, "shape" => "rectangle"],
            ["code" => "07", "x" => 260, "y" => 480, "shape" => "rectangle"],
            ["code" => "05", "x" => 150, "y" => 530, "shape" => "rectangle"],
            ["code" => "R5", "x" => 450, "y" => 480, "shape" => "rectangle"],
            ["code" => "R6", "x" => 750, "y" => 480, "shape" => "rectangle"],
            ["code" => "R7", "x" => 860, "y" => 480, "shape" => "rectangle"],
            ["code" => "R4", "x" => 500, "y" => 590, "shape" => "rectangle"],
            ["code" => "R3", "x" => 610, "y" => 590, "shape" => "rectangle"],
            ["code" => "R2", "x" => 750, "y" => 590, "shape" => "rectangle"],
            ["code" => "R1", "x" => 860, "y" => 590, "shape" => "rectangle"],
            ["code" => "M1", "x" => 1020, "y" => 200, "shape" => "rectangle"],
            ["code" => "M2", "x" => 1020, "y" => 300, "shape" => "rectangle"],
            ["code" => "M3", "x" => 1020, "y" => 400, "shape" => "rectangle"],
            ["code" => "M4", "x" => 1020, "y" => 500, "shape" => "rectangle"],
            ["code" => "M5", "x" => 1020, "y" => 600, "shape" => "rectangle"],
            ["code" => "01", "x" => 1150, "y" => 450, "shape" => "rectangle"],
            ["code" => "02", "x" => 1260, "y" => 450, "shape" => "rectangle"],
            ["code" => "03", "x" => 1350, "y" => 360, "shape" => "circle"],
            ["code" => "04", "x" => 1330, "y" => 550, "shape" => "rectangle"],
        ];
        foreach ($otic_tables as $t) {
            $allTables[] = array_merge($t, ["area" => "VIP_OTIC"]);
        }

        // 2. MOON AREA
        // Top Row: BS10 to BS1
        for ($i = 1; $i <= 10; $i++) {
            $allTables[] = ["code" => "BS" . (11 - $i), "x" => 250 + ($i * 100), "y" => 150, "shape" => "rectangle", "area" => "MOON AREA"];
        }
        // Row D1 to D8
        for ($i = 1; $i <= 8; $i++) {
            $allTables[] = ["code" => "D" . $i, "x" => 50 + ($i * 80), "y" => 320, "shape" => "rectangle", "area" => "MOON AREA"];
        }
        // Circle/Scattered D9 to D16
        $moon_scattered = [
            ["code" => "D10", "x" => 150, "y" => 500, "shape" => "rectangle"],
            ["code" => "D11", "x" => 300, "y" => 500, "shape" => "rectangle"],
            ["code" => "D12", "x" => 380, "y" => 650, "shape" => "rectangle"],
            ["code" => "D13", "x" => 300, "y" => 800, "shape" => "rectangle"],
            ["code" => "D14", "x" => 150, "y" => 800, "shape" => "rectangle"],
            ["code" => "D15", "x" => 30, "y" => 800, "shape" => "rectangle"],
            ["code" => "D9", "x" => 100, "y" => 650, "shape" => "rectangle"],
            ["code" => "D16", "x" => 30, "y" => 650, "shape" => "rectangle"],
        ];
        foreach ($moon_scattered as $t) {
            $allTables[] = array_merge($t, ["area" => "MOON AREA"]);
        }
        // Grid D20 to D30 (Right side)
        $grid_x_start = 1000;
        for ($r = 0; $r < 4; $r++) {
            $cols = $r < 3 ? 3 : 2;
            for ($c = 0; $c < $cols; $c++) {
                $idx = 20 + ($r * 3) + $c;
                if ($idx > 30) continue;
                $allTables[] = ["code" => "D" . $idx, "x" => $grid_x_start + ($c * 120), "y" => 420 + ($r * 150), "shape" => "rectangle", "area" => "MOON AREA"];
            }
        }

        // 3. POOL AREA
        // S1 and S2
        $allTables[] = ["code" => "S1", "x" => 100, "y" => 100, "shape" => "rectangle", "area" => "POOL AREA"];
        $allTables[] = ["code" => "S2", "x" => 220, "y" => 100, "shape" => "rectangle", "area" => "POOL AREA"];
        // Row S3 to S7
        for ($i = 3; $i <= 7; $i++) {
            $allTables[] = ["code" => "S" . $i, "x" => 100 + (($i - 3) * 120), "y" => 280, "shape" => "rectangle", "area" => "POOL AREA"];
        }
        // Row S14 to S8
        $s8_row = ["S14", "S13", "S12", "S11", "S10", "S9", "S8"];
        foreach ($s8_row as $i => $code) {
            $allTables[] = ["code" => $code, "x" => 100 + ($i * 80), "y" => 450, "shape" => "rectangle", "area" => "POOL AREA"];
        }
        // Bottom Row S20 to S26
        for ($i = 20; $i <= 26; $i++) {
            $allTables[] = ["code" => "S" . $i, "x" => 100 + (($i - 20) * 80), "y" => 580, "shape" => "rectangle", "area" => "POOL AREA"];
        }
        // PB Series
        for ($i = 1; $i <= 4; $i++) {
            $allTables[] = ["code" => "PB" . $i, "x" => 750 + (($i - 1) * 60), "y" => 480, "shape" => "rectangle", "area" => "POOL AREA"];
        }
        for ($i = 5; $i <= 8; $i++) {
            $allTables[] = ["code" => "PB" . $i, "x" => 750 + (($i - 5) * 60), "y" => 580, "shape" => "rectangle", "area" => "POOL AREA"];
        }

        // 4. CUP ARENA
        // Vertical Row CB9 to CB1
        for ($i = 1; $i <= 9; $i++) {
            $allTables[] = ["code" => "CB" . (10 - $i), "x" => 120, "y" => 100 + ($i * 80), "shape" => "rectangle", "area" => "CUP ARENA"];
        }
        // Diamond
        $allTables[] = ["code" => "W1", "x" => 450, "y" => 300, "shape" => "rectangle", "area" => "CUP ARENA"];
        $allTables[] = ["code" => "W2", "x" => 415, "y" => 400, "shape" => "rectangle", "area" => "CUP ARENA"];
        $allTables[] = ["code" => "W3", "x" => 380, "y" => 300, "shape" => "rectangle", "area" => "CUP ARENA"];
        $allTables[] = ["code" => "W4", "x" => 415, "y" => 200, "shape" => "rectangle", "area" => "CUP ARENA"];

        // 5. VIP CABANA & STAR
        $vc_star = [
            ["code" => "VC1", "x" => 200, "y" => 80, "shape" => "rectangle"],
            ["code" => "VC2", "x" => 650, "y" => 80, "shape" => "rectangle"],
            ["code" => "VC3", "x" => 1200, "y" => 80, "shape" => "rectangle"],
            ["code" => "VC4", "x" => 1650, "y" => 80, "shape" => "rectangle"],
            
            ["code" => "P10", "x" => 50, "y" => 250, "shape" => "circle"],
            ["code" => "P9", "x" => 50, "y" => 550, "shape" => "circle"],
            ["code" => "P8", "x" => 180, "y" => 950, "shape" => "circle"],
            ["code" => "P7", "x" => 350, "y" => 950, "shape" => "circle"],
        ];
        foreach ($vc_star as $t) {
            $allTables[] = array_merge($t, ["area" => "VIP CABANA & STAR"]);
        }
        for ($i = 1; $i <= 6; $i++) {
            $allTables[] = ["code" => "P" . (7 - $i), "x" => 400 + ($i * 220), "y" => 950, "shape" => "circle", "area" => "VIP CABANA & STAR"];
        }
        for ($i = 8; $i <= 12; $i++) {
            $allTables[] = ["code" => "TG" . $i, "x" => 100 + ((13 - $i) * 130), "y" => 300, "shape" => "rectangle", "area" => "VIP CABANA & STAR"];
        }
        for ($i = 3; $i <= 6; $i++) {
            $allTables[] = ["code" => "TG" . $i, "x" => 100 + ((7 - $i) * 130), "y" => 480, "shape" => "rectangle", "area" => "VIP CABANA & STAR"];
        }
        
        $vc_star2 = [
            ["code" => "TG7", "x" => 750, "y" => 300, "shape" => "rectangle"],
            ["code" => "TG1", "x" => 750, "y" => 480, "shape" => "rectangle"],
            ["code" => "TG2", "x" => 620, "y" => 480, "shape" => "rectangle"],
        ];
        foreach ($vc_star2 as $t) {
            $allTables[] = array_merge($t, ["area" => "VIP CABANA & STAR"]);
        }

        for ($i = 1; $i <= 8; $i++) {
            $row = floor(($i - 1) % 4);
            $col = $i <= 4 ? 0 : 1;
            $allTables[] = ["code" => "SB" . $i, "x" => 950 + ($col * 150), "y" => 150 + ($row * 180), "shape" => "rectangle", "area" => "VIP CABANA & STAR"];
        }

        $g_series = [
            ["code" => "G9", "x" => 830, "y" => 300, "shape" => "rectangle"],
            ["code" => "G8", "x" => 830, "y" => 480, "shape" => "rectangle"],
            ["code" => "G1", "x" => 1250, "y" => 380, "shape" => "rectangle"],
            ["code" => "G2", "x" => 1250, "y" => 580, "shape" => "rectangle"],
            ["code" => "G3", "x" => 1250, "y" => 780, "shape" => "rectangle"],
            ["code" => "G4", "x" => 1050, "y" => 780, "shape" => "rectangle"],
            ["code" => "G5", "x" => 830, "y" => 700, "shape" => "rectangle"],
            ["code" => "G6", "x" => 620, "y" => 700, "shape" => "rectangle"],
            ["code" => "G7", "x" => 410, "y" => 700, "shape" => "rectangle"],
        ];
        foreach ($g_series as $t) {
            $allTables[] = array_merge($t, ["area" => "VIP CABANA & STAR"]);
        }

        for ($i = 1; $i <= 10; $i++) {
            $row = floor(($i - 1) / 3);
            $col = ($i - 1) % 3;
            if ($i == 10) {
                $allTables[] = ["code" => "B10", "x" => 1900, "y" => 320, "shape" => "rectangle", "area" => "VIP CABANA & STAR"];
            } else {
                $allTables[] = ["code" => "B" . $i, "x" => 1900 + ($col * 220), "y" => 320 + ($row * 320), "shape" => "rectangle", "area" => "VIP CABANA & STAR"];
            }
        }
        for ($i = 10; $i <= 13; $i++) {
            $allTables[] = ["code" => "CB" . $i, "x" => 2700, "y" => 600 + ((13 - $i) * 180), "shape" => "rectangle", "area" => "VIP CABANA & STAR"];
        }

        // Simpan semua tables ke database
        foreach ($allTables as $t) {
            DB::table('tables')->insert([
                'code' => $t['code'],
                'area_id' => $t['area'], 
                'area_fk_id' => $areaIds[$t['area']],
                'shape' => $t['shape'],
                'x_pos' => $t['x'],
                'y_pos' => $t['y'],
                'capacity' => strpos($t['code'], 'VC') !== false ? 10 : (strpos($t['code'], 'P') !== false ? 6 : 4), 
                'min_spending' => 0, 
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
