<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Booking;
use App\Models\MasterLevel;
use App\Models\MasterTag;
use Illuminate\Support\Facades\DB;
use Shuchkin\SimpleXLSXGen;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::addSelect([
                'visits_count' => Booking::selectRaw('count(distinct start_time)')
                    ->whereColumn('customer_id', 'customers.id'),
            ])
            ->selectRaw('*, (SELECT count(distinct start_time) FROM bookings WHERE customer_id = customers.id) + total_visits as total_combined_visits')
            ->withSum('bookings as total_spent', 'billed_price')
            ->with([
                'masterLevel',
                'bookings' => fn($q) => $q->orderBy('start_time', 'desc')->with('tableModel'),
            ]);

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
            });
        }

        $customers = $query->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        // ── Bulk tag query (fix N+1) ─────────────────────────────────────
        // Satu query untuk mengambil top-3 tag per customer sekaligus,
        // bukan memanggil topTags() di setiap baris loop.
        $customerIds = $customers->pluck('id');

        $rawTopTags = DB::table('master_tags')
            ->join('booking_tags', 'master_tags.id', '=', 'booking_tags.tag_id')
            ->join('bookings', 'booking_tags.booking_id', '=', 'bookings.id')
            ->join('master_tag_groups', 'master_tags.master_tag_group_id', '=', 'master_tag_groups.id')
            ->whereIn('bookings.customer_id', $customerIds)
            ->select(
                'bookings.customer_id',
                'master_tags.id',
                'master_tags.name',
                'master_tag_groups.name as group_name',
                DB::raw('count(*) as tag_count')
            )
            ->groupBy('bookings.customer_id', 'master_tags.id', 'master_tags.name', 'master_tag_groups.name')
            ->orderBy('bookings.customer_id')
            ->orderByDesc('tag_count')
            ->get()
            ->groupBy('customer_id')
            ->map(fn($tags) => $tags->take(3)); // Top 3 per customer


        $levels = MasterLevel::orderBy('min_spending', 'asc')->get();

        $lifetimeRevenue = Booking::whereIn(
            DB::raw('LOWER(CAST(status AS TEXT))'), 
            ['confirmed', 'completed', 'billed', 'ok', 'finished', 'done', 'paid']
        )->sum('billed_price') + Customer::sum('total_spending');

        $allCustomersForExport = Customer::addSelect([
                'visits_count' => Booking::selectRaw('count(distinct start_time)')
                    ->whereColumn('customer_id', 'customers.id'),
            ])
            ->withSum('bookings as total_spent', 'billed_price')
            ->orderBy('name')
            ->get();

        return view('admin.customers', compact(
            'customers', 'lifetimeRevenue', 'allCustomersForExport',
            'levels', 'rawTopTags'
        ));
    }

    public function export(Request $request)
    {
        $format = $request->query('format', 'csv');

        $customers = Customer::withCount(['bookings as visits_count' => function($query) {
            $query->select(DB::raw('count(distinct start_time)'));
        }])
        ->with('bookings.tags')
        ->orderBy('name')
        ->get();

        $allMasterTags = MasterTag::all();
        
        $header = [
            'name', 'phone', 'gender', 'age_range', 'nat', 'total_spend', 'total_visit', 
            'date', 'time_in', 'time_out', 'total_pax'
        ];
        
        foreach ($allMasterTags as $tag) {
            $header[] = $tag->abbreviation ?: strtolower(str_replace(' ', '_', $tag->name));
        }

        $exportData = [];
        $exportData[] = $header;

        foreach ($customers as $customer) {
            $ownedTagIds = [];
            $latestBooking = null;
            if ($customer->bookings) {
                $latestBooking = $customer->bookings->sortByDesc('start_time')->first();
                foreach($customer->bookings as $b) {
                    if ($b->tags) {
                        foreach($b->tags as $t) {
                            $ownedTagIds[] = $t->id;
                        }
                    }
                }
            }
            $ownedTagIds = array_unique($ownedTagIds);
            
            $hasTag = function($tagId) use ($ownedTagIds) {
                return in_array($tagId, $ownedTagIds) ? 1 : 0;
            };

            $totalSpend = (int) ($customer->total_spending + ($customer->bookings ? $customer->bookings->sum('billed_price') : 0));
            $formattedSpend = "Rp " . number_format($totalSpend, 0, ',', '.');

            $row = [
                $customer->name,
                $customer->phone,
                strtoupper($customer->gender ?: 'MALE'),
                $customer->age ?: '',
                $customer->nat ?: 'INA',
                $formattedSpend,
                $customer->total_visits ?: ($customer->visits_count ?? 0),
                $latestBooking ? \Carbon\Carbon::parse($latestBooking->start_time)->format('Y-m-d') : '',
                $latestBooking ? \Carbon\Carbon::parse($latestBooking->start_time)->format('H:i') : '',
                $latestBooking ? \Carbon\Carbon::parse($latestBooking->end_time)->format('H:i') : '',
                $latestBooking ? $latestBooking->pax : 1
            ];
            
            foreach ($allMasterTags as $tag) {
                $row[] = $hasTag($tag->id);
            }
            $exportData[] = $row;
        }

        if ($format === 'xlsx') {
            $xlsx = SimpleXLSXGen::fromArray($exportData);
            return response()->streamDownload(function() use ($xlsx) {
                echo $xlsx;
            }, "customers_database_" . date('Y-m-d') . ".xlsx", [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        // Default CSV
        $filename = "customers_database_" . date('Y-m-d') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($exportData) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM for UTF-8
            foreach ($exportData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $data = $request->input('data', []);
        
        if (empty($data)) {
            return response()->json(['status' => 'error', 'message' => 'No data received.'], 400);
        }

        DB::beginTransaction();
        try {
            // Fetch levels once for calculation
            $levels = MasterLevel::orderBy('min_spending', 'desc')->get();
            $masterTagsById = DB::table('master_tags')->pluck('id')->toArray();
            $masterTagsByName = DB::table('master_tags')->pluck('id', 'name')->toArray();
            
            $allTags = MasterTag::all();
            $tagMappings = [];
            
            foreach($allTags as $tag) {
                // Determine column name from abbreviation or name
                $col = $tag->abbreviation ?: strtolower(str_replace(' ', '_', $tag->name));
                $tagMappings[$col] = $tag->id;
            }

            // Get or create a fallback table for imported bookings
            $fallbackTableId = DB::table('tables')->value('id');
            if (!$fallbackTableId) {
                $fallbackTableId = DB::table('tables')->insertGetId([
                    'code' => 'IMPORT', 'x_pos' => 50, 'y_pos' => 50, 
                    'shape' => 'rect', 'status' => 'available', 'area_id' => '1',
                    'created_at' => now(), 'updated_at' => now()
                ]);
            }

            foreach ($data as $row) {
                // Bersihkan baris dari spasi berlebih
                $row = array_map(fn($val) => is_string($val) ? trim($val) : $val, $row);
                
                if (empty($row['name'])) continue;

                // Clean and Determine Spending (handling Excel number formatting like "1,660,120" or "Rp1.660.120,00" or "1660120.0")
                $spendVal = !empty($row['total_spend']) ? $row['total_spend'] : (!empty($row['total_spending']) ? $row['total_spending'] : 0);
                if (is_string($spendVal)) {
                    $spendVal = preg_replace("/[\.,]00?$/", "", trim($spendVal));
                    $spendVal = preg_replace("/[^\d]/", "", $spendVal);
                }
                $totalSpending = (float)$spendVal;

                $totalVisits = !empty($row['total_visit']) ? (int)$row['total_visit'] : (!empty($row['total_visits']) ? (int)$row['total_visits'] : 0);
                
                // Determine Age (mapping from template's age_range)
                $age = !empty($row['age_range']) ? (string)$row['age_range'] : (!empty($row['age']) ? (string)$row['age'] : null);
                
                if ($levels->isEmpty()) {
                    throw new \Exception("Master Levels are not configured. Please add at least one level in Master Data.");
                }

                // Determine Level ID by Spending
                $lowestLevelId = $levels->last()->id; // $levels is ordered desc, so last is lowest
                $levelId = $lowestLevelId; 
                foreach ($levels as $lv) {
                    if ($totalSpending >= $lv->min_spending) {
                        $levelId = $lv->id;
                        break;
                    }
                }
                // Gender normalization: Ensure only MALE or FEMALE is stored (matches DB ENUM)
                $rawGender = strtoupper(str_replace([' ', ';', ':', '.', ','], '', (string)($row['gender'] ?? '')));
                $normalizedGender = 'MALE'; // Default
                if (str_starts_with($rawGender, 'F') || str_contains($rawGender, 'FEMALE') || str_contains($rawGender, 'WANITA') || str_contains($rawGender, 'PEREMPUAN')) {
                    $normalizedGender = 'FEMALE';
                } elseif (str_starts_with($rawGender, 'M') || str_contains($rawGender, 'MALE') || str_contains($rawGender, 'PRIA') || str_contains($rawGender, 'LAKI')) {
                    $normalizedGender = 'MALE';
                }
                // Update or Create logic
                $customerData = [
                    'name'           => $row['name'],
                    'age'            => $age,
                    'gender'         => $normalizedGender,
                    'nat'            => !empty($row['nat']) ? strtoupper(trim($row['nat'])) : null,
                    'total_spending' => $totalSpending,
                    'total_visits'   => $totalVisits,
                    'master_level_id'=> $levelId,
                    'last_visit'     => date('Y-m-d H:i:s'),
                ];

                // Normalize phone: Smart Splitting for multiple numbers (e.g. "6281 / 6289")
                $rawPhone = (string)($row['phone'] ?? '');
                $phoneParts = preg_split('/[\/\|,]/', $rawPhone);
                $cleanedPhones = [];
                foreach ($phoneParts as $part) {
                    $clean = preg_replace('/[^\d]/', '', trim($part));
                    if (!empty($clean)) {
                        $cleanedPhones[] = $clean;
                    }
                }
                
                // Recombine with standard separator for storage
                $phone = !empty($cleanedPhones) ? implode(' / ', $cleanedPhones) : null;

                if ($phone) {
                    $customer = Customer::updateOrCreate(['phone' => $phone], $customerData);
                } else {
                    $customer = Customer::create($customerData);
                }

                // --------- VISIT & TAG INJECTION ------------
                // Always create a booking to record the "Last Visit" from Excel, even if spending is 0
                $d = date('Y-m-d');
                if (!empty($row['date'])) {
                    if (is_numeric($row['date'])) {
                        $d = date('Y-m-d', ($row['date'] - 25569) * 86400);
                    } else {
                        $d = date('Y-m-d', strtotime($row['date']));
                    }
                }

                $tIn = "10:00:00";
                if (!empty($row['time_in'])) {
                    if (is_numeric($row['time_in'])) {
                        $tIn = date('H:i:s', $row['time_in'] * 86400);
                    } else {
                        $tIn = date('H:i:s', strtotime($row['time_in']));
                    }
                }

                $tOut = "12:00:00";
                if (!empty($row['time_out'])) {
                    if (is_numeric($row['time_out'])) {
                        $tOut = date('H:i:s', $row['time_out'] * 86400);
                    } else {
                        $tOut = date('H:i:s', strtotime($row['time_out']));
                    }
                }
                
                $startDate = $d . ' ' . $tIn;
                $endDate = $d . ' ' . $tOut;

                // Create the booking entry (billed_price 0 because total_spent is stored in customer record)
                $bookingId = DB::table('bookings')->insertGetId([
                    'customer_id'  => $customer->id,
                    'table_id'     => $fallbackTableId,
                    'pax'          => !empty($row['total_pax']) ? (int)$row['total_pax'] : (!empty($row['toal_pax']) ? (int)$row['toal_pax'] : 2),
                    'start_time'   => $startDate,
                    'end_time'     => $endDate,
                    'status'       => 'completed',
                    'billed_at'    => $startDate,
                    'billed_price' => 0, 

                    'notes'        => 'Imported from historical template',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                // Collect and Insert tags if any are detected
                $detectedTags = [];
                foreach ($tagMappings as $col => $tagId) {
                    if (isset($row[$col]) && $row[$col] != '' && $row[$col] != '0') {
                        $detectedTags[] = $tagId;
                    }
                }
                
                if (!empty($detectedTags)) {
                    $pivotData = array_map(fn($tid) => [
                        'booking_id' => $bookingId,
                        'tag_id'     => $tid,
                        'created_at' => now(),
                        'updated_at' => now()
                    ], array_unique($detectedTags));
                    DB::table('booking_tags')->insertOrIgnore($pivotData);
                }
                
                // Karena kita menambah 1 dummy booking, perhitungan total trips di dashboard
                    // akan jadi `total_visits + hitung_booking`. Agar tidak double-count visit,
                    // kurangi total_visits di profil sebanyak 1. (Minimal 0)
                    if ($customer->total_visits > 0) {
                        $customer->decrement('total_visits');
                    }
                }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Data imported successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'phone'  => 'nullable|string|max:100',
            'gender' => 'nullable|string|max:20',
            'age'    => 'nullable|string',
            'nat'    => 'nullable|string|max:10',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->update($request->only(['name', 'phone', 'gender', 'age', 'nat']));

        return back()->with('success', 'Customer updated successfully.');
    }
}

