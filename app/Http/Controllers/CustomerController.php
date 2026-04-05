<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Booking;
use App\Models\MasterCategory;
use App\Models\MasterLevel;
use Illuminate\Support\Facades\DB;
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

        $customers = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // ── Bulk tag query (fix N+1) ─────────────────────────────────────
        // Satu query untuk mengambil top-3 tag per customer sekaligus,
        // bukan memanggil topTags() di setiap baris loop.
        $customerIds = $customers->pluck('id');

        $rawTopTags = DB::table('master_tags')
            ->join('booking_tags', 'master_tags.id', '=', 'booking_tags.tag_id')
            ->join('bookings', 'booking_tags.booking_id', '=', 'bookings.id')
            ->whereIn('bookings.customer_id', $customerIds)
            ->select(
                'bookings.customer_id',
                'master_tags.id',
                'master_tags.name',
                'master_tags.group_name',
                DB::raw('count(*) as tag_count')
            )
            ->groupBy('bookings.customer_id', 'master_tags.id', 'master_tags.name', 'master_tags.group_name')
            ->orderBy('bookings.customer_id')
            ->orderByDesc('tag_count')
            ->get()
            ->groupBy('customer_id')
            ->map(fn($tags) => $tags->take(3)); // Top 3 per customer

        // ── Category & Level config dari DB (bukan hardcode) ────────────
        $categoryMap = MasterCategory::all()
            ->keyBy(fn($c) => strtoupper($c->name));

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
            'levels', 'categoryMap', 'rawTopTags'
        ));
    }

    public function export()
    {
        $customers = Customer::withCount(['bookings as visits_count' => function($query) {
            $query->select(DB::raw('count(distinct start_time)'));
        }])
        ->with('bookings.tags')
        ->orderBy('name')
        ->get();

        $filename = "customers_database_" . date('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fputs($file, "\xEF\xBB\xBF");

            // Header
            fputcsv($file, [
                'name', 'phone', 'gender', 'age_range', 'total_spend', 'total_visit', 
                'date', 'time_in', 'time_out', 'toal_pax',
                'pu_din', 'pu_fam', 'pu_lunch', 'pu_party', 'pu_celeb', 'pu_comm', 'pu_corp',
                'pr_reg', 'pr_ayce', 'pr_aycd', 'pr_alc', 'pr_buff', 'pr_iftar',
                'time_wdd', 'time_wdn', 'time_wed', 'time_wen'
            ]);

            foreach ($customers as $customer) {
                $ownedTagNames = [];
                $latestBooking = null;
                if ($customer->bookings) {
                    $latestBooking = $customer->bookings->sortByDesc('start_time')->first();
                    foreach($customer->bookings as $b) {
                        if ($b->tags) {
                            foreach($b->tags as $t) {
                                $ownedTagNames[] = $t->name;
                            }
                        }
                    }
                }
                $ownedTagNames = array_unique($ownedTagNames);
                
                $hasTag = function($tagName) use ($ownedTagNames) {
                    return in_array($tagName, $ownedTagNames) ? 1 : 0;
                };

                $totalSpend = (int) ($customer->total_spending + ($customer->bookings ? $customer->bookings->sum('billed_price') : 0));

                fputcsv($file, [
                    $customer->name,
                    $customer->phone,
                    strtoupper($customer->gender ?: 'MALE'),
                    $customer->age ?: '',
                    $totalSpend,
                    $customer->total_visits ?: ($customer->visits_count ?? 0),
                    $latestBooking ? \Carbon\Carbon::parse($latestBooking->start_time)->format('Y-m-d') : '',
                    $latestBooking ? \Carbon\Carbon::parse($latestBooking->start_time)->format('H:i') : '',
                    $latestBooking ? \Carbon\Carbon::parse($latestBooking->end_time)->format('H:i') : '',
                    $latestBooking ? $latestBooking->pax : 1,
                    $hasTag('Dining'), $hasTag('Family'), $hasTag('Lunch'), $hasTag('Party'), $hasTag('Celebration'), $hasTag('Community'), $hasTag('Corporate'),
                    $hasTag('Regular F&B'), $hasTag('AYCE'), $hasTag('AYCD'), $hasTag('Alcohol'), $hasTag('Buffet'), $hasTag('Iftar Buffet'),
                    $hasTag('Weekday Day'), $hasTag('Weekday Night'), $hasTag('Weekend Day'), $hasTag('Weekend Night')
                ]);
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
            $masterTagsByName = DB::table('master_tags')->pluck('id', 'name')->toArray();
            
            $tagMappings = [
                'pu_din' => 'Dining', 'pu_fam' => 'Family', 'pu_lunch' => 'Lunch', 
                'pu_celeb' => 'Celebration', 'pu_party' => 'Party', 'pu_corp' => 'Corporate', 
                'pu_comm' => 'Community', 'pr_reg' => 'Regular F&B', 'pr_ayce' => 'AYCE', 
                'pr_aycd' => 'AYCD', 'pr_buff' => 'Buffet', 'pr_iftar' => 'Iftar Buffet', 
                'pr_alc' => 'Alcohol', 'time_wdd' => 'Weekday Day', 'time_wdn' => 'Weekday Night', 
                'time_wed' => 'Weekend Day', 'time_wen' => 'Weekend Night'
            ];

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
                    'total_spending' => $totalSpending,
                    'total_visits'   => $totalVisits,
                    'master_level_id'=> $levelId,
                    'last_visit'     => date('Y-m-d H:i:s'),
                ];

                // Normalize phone: Remove all non-digit characters like spaces and dashes
                $phone = !empty($row['phone']) ? preg_replace('/[^\d]/', '', (string)$row['phone']) : null;
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
                    'pax'          => !empty($row['toal_pax']) ? (int)$row['toal_pax'] : 2,
                    'start_time'   => $startDate,
                    'end_time'     => $endDate,
                    'status'       => 'completed',
                    'billed_at'    => $startDate,
                    'billed_price' => 0, 
                    'category'     => 'reguler',
                    'notes'        => 'Imported from historical template',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                // Collect and Insert tags if any are detected
                $detectedTags = [];
                foreach ($tagMappings as $col => $tagName) {
                    // Check if column exists and has a value that isn't empty or 0
                    if (isset($row[$col]) && $row[$col] != '' && $row[$col] != '0') {
                        if (isset($masterTagsByName[$tagName])) {
                            $detectedTags[] = $masterTagsByName[$tagName];
                        }
                    }
                }

                if (!empty($detectedTags)) {
                    $bookingTagsData = [];
                    foreach (array_unique($detectedTags) as $tagId) {
                        $bookingTagsData[] = [
                            'booking_id' => $bookingId,
                            'tag_id'     => $tagId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    if (!empty($bookingTagsData)) {
                        // Ignore duplicates/errors with insertOrIgnore just in case
                        DB::table('booking_tags')->insertOrIgnore($bookingTagsData);
                    }
                    
                    // Karena kita menambah 1 dummy booking, perhitungan total trips di dashboard
                    // akan jadi `total_visits + hitung_booking`. Agar tidak double-count visit,
                    // kurangi total_visits di profil sebanyak 1. (Minimal 0)
                    if ($customer->total_visits > 0) {
                        $customer->decrement('total_visits');
                    }
                }
                // ----------------------------------------------------
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
            'phone'  => 'nullable|string|max:20',
            'gender' => 'nullable|string|max:20',
            'age'    => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->update($request->only(['name', 'phone', 'gender', 'age']));

        return back()->with('success', 'Customer updated successfully.');
    }
}

