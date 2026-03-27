<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class DemographicController extends Controller
{
    public function index()
    {
        $availableYears = Booking::select(DB::raw('EXTRACT(YEAR FROM start_time) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
            
        if (empty($availableYears)) {
            $availableYears = [date('Y')];
        }

        $currentYear = request('year', $availableYears[0]);
        $defaultPrev = in_array($currentYear - 1, $availableYears) ? $currentYear - 1 : ($availableYears[1] ?? ($availableYears[0] - 1));
        $previousYear = request('compare_with', $defaultPrev);
        $years = [$previousYear, $currentYear];

        // 1. Age Segment
        $ageSegments = [
            '<18'     => ['min' => 0, 'max' => 17],
            '18-24'   => ['min' => 18, 'max' => 24],
            '25-34'   => ['min' => 25, 'max' => 34],
            '35-44'   => ['min' => 35, 'max' => 44],
            '45+'     => ['min' => 45, 'max' => 200],
            'Unknown' => ['min' => -1, 'max' => -1],
        ];

        $ageData = [];
        foreach ($ageSegments as $key => $range) {
            $ageData[$key] = [
                'name' => $key,
                'min' => $range['min'],
                'max' => $range['max'],
                $previousYear => 0,
                $currentYear => 0,
            ];
        }

        // Get customers with bookings in 2025 and 2026. We will count sum of pax
        $bookings = Booking::select(
            DB::raw('EXTRACT(YEAR FROM start_time) as year'),
            'customers.age',
            'customers.gender',
            DB::raw('SUM(bookings.pax) as total_pax')
        )
        ->join('customers', 'customers.id', '=', 'bookings.customer_id')
        ->whereIn(DB::raw('EXTRACT(YEAR FROM start_time)'), $years)
        ->groupBy('year', 'customers.age', 'customers.gender')
        ->get();

        $genders = [
            'Male'    => [$previousYear => 0, $currentYear => 0],
            'Female'  => [$previousYear => 0, $currentYear => 0],
            'Unknown' => [$previousYear => 0, $currentYear => 0],
        ];

        $totalPaxPerYear = [$previousYear => 0, $currentYear => 0];

        foreach ($bookings as $b) {
            if (!in_array($b->year, $years)) continue;
            
            $year = $b->year;
            $totalPaxPerYear[$year] += $b->total_pax;

            // Sort into age segments
            $age = $b->age;
            if ($age === null) {
                $ageData['Unknown'][$year] += $b->total_pax;
            } else {
                foreach ($ageData as $key => &$data) {
                    if ($key === 'Unknown') continue;
                    if ($age >= $data['min'] && $age <= $data['max']) {
                        $data[$year] += $b->total_pax;
                        break;
                    }
                }
            }

            // Genders
            $gender = strtolower($b->gender ?? '');
            if ($gender == 'male' || $gender == 'laki-laki') {
                $genders['Male'][$year] += $b->total_pax;
            } elseif ($gender == 'female' || $gender == 'perempuan') {
                $genders['Female'][$year] += $b->total_pax;
            } else {
                $genders['Unknown'][$year] = ($genders['Unknown'][$year] ?? 0) + $b->total_pax;
            }
        }

        // Calculate Percentages and Insights for Age
        foreach ($ageData as $key => &$data) {
            $data['prev_pct'] = $totalPaxPerYear[$previousYear] > 0 ? round(($data[$previousYear] / $totalPaxPerYear[$previousYear]) * 100) : 0;
            $data['curr_pct'] = $totalPaxPerYear[$currentYear] > 0 ? round(($data[$currentYear] / $totalPaxPerYear[$currentYear]) * 100) : 0;

            // Default insight logic based on image
            $diff = $data['curr_pct'] - $data['prev_pct'];
            
            if ($data['prev_pct'] > 0 && $data['curr_pct'] == 0) {
                $data['insight'] = '↓ Hilang';
                $data['insight_color'] = 'text-red-500';
            } elseif ($diff <= -5) {
                $data['insight'] = '↓ Turun';
                $data['insight_color'] = 'text-orange-500';
            } elseif ($diff >= 5 && $data['curr_pct'] >= 30) {
                $data['insight'] = '🔥 Naik & dominan';
                $data['insight_color'] = 'text-red-600 font-bold';
            } elseif ($diff >= 3) {
                $data['insight'] = '↑ Naik';
                $data['insight_color'] = 'text-green-600 font-bold';
            } else {
                $data['insight'] = '→ Stabil';
                $data['insight_color'] = 'text-slate-500';
            }
        }

        // Calculate Percentages and Insights for Gender
        foreach ($genders as $key => &$data) {
            $data['prev_pct'] = $totalPaxPerYear[$previousYear] > 0 ? round(($data[$previousYear] / $totalPaxPerYear[$previousYear]) * 100) : 0;
            $data['curr_pct'] = $totalPaxPerYear[$currentYear] > 0 ? round(($data[$currentYear] / $totalPaxPerYear[$currentYear]) * 100) : 0;
            
            $diff = $data['curr_pct'] - $data['prev_pct'];
            if ($diff <= -5) {
                $data['insight'] = '↓ Turun';
                $data['insight_color'] = 'text-orange-500';
            } elseif ($diff > 5) {
                $data['insight'] = '↑ Naik';
                $data['insight_color'] = 'text-green-600 font-bold';
            } else {
                $data['insight'] = '→ Stabil';
                $data['insight_color'] = 'text-slate-500';
            }
        }




        return view('demographics.index', compact('ageData', 'genders', 'currentYear', 'previousYear', 'availableYears'));
    }
}
