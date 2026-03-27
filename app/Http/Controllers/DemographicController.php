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
        $availableYears = Customer::select(DB::raw('EXTRACT(YEAR FROM created_at) as year'))
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
        // ... ageSegments definition ...
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

        // Get customers directly for demographics regardless of bookings
        $bookings = Customer::select(
            DB::raw('EXTRACT(YEAR FROM created_at) as year'),
            'age',
            'gender',
            DB::raw('COUNT(id) as total_cust')
        )
        ->whereIn(DB::raw('EXTRACT(YEAR FROM created_at)'), $years)
        ->groupBy('year', 'age', 'gender')
        ->get();

        $genders = [
            'Male'    => [$previousYear => 0, $currentYear => 0],
            'Female'  => [$previousYear => 0, $currentYear => 0],
            'Unknown' => [$previousYear => 0, $currentYear => 0],
        ];

        $totalCustPerYear = [$previousYear => 0, $currentYear => 0];

        foreach ($bookings as $b) {
            if (!in_array($b->year, $years)) continue;
            
            $year = $b->year;
            $totalCustPerYear[$year] += $b->total_cust;

            // Sort into age segments
            $age = $b->age;
            if ($age === null) {
                $ageData['Unknown'][$year] += $b->total_cust;
            } else {
                foreach ($ageData as $key => &$data) {
                    if ($key === 'Unknown') continue;
                    if ($age >= $data['min'] && $age <= $data['max']) {
                        $data[$year] += $b->total_cust;
                        break;
                    }
                }
            }

            // Genders
            $gender = strtolower($b->gender ?? '');
            if ($gender == 'male' || $gender == 'laki-laki') {
                $genders['Male'][$year] += $b->total_cust;
            } elseif ($gender == 'female' || $gender == 'perempuan') {
                $genders['Female'][$year] += $b->total_cust;
            } else {
                $genders['Unknown'][$year] = ($genders['Unknown'][$year] ?? 0) + $b->total_cust;
            }
        }

        // Calculate Percentages and Insights for Age
        foreach ($ageData as $key => &$data) {
            $data['prev_pct'] = $totalCustPerYear[$previousYear] > 0 ? round(($data[$previousYear] / $totalCustPerYear[$previousYear]) * 100) : 0;
            $data['curr_pct'] = $totalCustPerYear[$currentYear] > 0 ? round(($data[$currentYear] / $totalCustPerYear[$currentYear]) * 100) : 0;

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
            $data['prev_pct'] = $totalCustPerYear[$previousYear] > 0 ? round(($data[$previousYear] / $totalCustPerYear[$previousYear]) * 100) : 0;
            $data['curr_pct'] = $totalCustPerYear[$currentYear] > 0 ? round(($data[$currentYear] / $totalCustPerYear[$currentYear]) * 100) : 0;
            
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




        if (request('export')) {
            $data = [
                ['Demographics & Insights Report'],
                ['Visitor Segments Comparison (' . $previousYear . ' vs ' . $currentYear . ')'],
                ['Date Generated: ' . now()->format('d M Y H:i')],
                [''],
                ['3. Age Segment (Umur Tamu)'],
                ['Age', (string)$previousYear, '%', (string)$currentYear, '%', 'Insight']
            ];

            foreach ($ageData as $age => $row) {
                $data[] = [
                    $age,
                    $row[$previousYear],
                    $row['prev_pct'] . '%',
                    $row[$currentYear],
                    $row['curr_pct'] . '%',
                    strip_tags($row['insight'])
                ];
            }

            $data[] = [''];
            $data[] = ['Gender Dynamics (Tamu Berdasarkan Gender)'];
            $data[] = ['Gender', (string)$previousYear, '%', (string)$currentYear, '%', 'Insight'];

            foreach ($genders as $gender => $row) {
                $data[] = [
                    $gender,
                    $row[$previousYear],
                    $row['prev_pct'] . '%',
                    $row[$currentYear],
                    $row['curr_pct'] . '%',
                    strip_tags($row['insight'])
                ];
            }

            $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);
            return response()->streamDownload(function() use ($xlsx) {
                echo $xlsx;
            }, 'demographics_report_' . $currentYear . '.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]);
        }

        return view('demographics.index', compact('ageData', 'genders', 'currentYear', 'previousYear', 'availableYears'));
    }
}
