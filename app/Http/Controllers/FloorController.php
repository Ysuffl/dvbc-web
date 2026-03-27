<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\Table;
use Illuminate\Support\Facades\DB;

class FloorController extends Controller
{
    // ─────────────────────────────────────────────
    // Floor Plan View
    // ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $areas = Area::where('is_active', true)
            ->orderBy('floor_number')
            ->orderBy('name')
            ->get();

        $selectedAreaId = $request->get('area', $areas->first()?->id);
        $selectedArea   = $areas->find($selectedAreaId);

        $tables = Table::where('area_fk_id', $selectedAreaId)
            ->orderBy('code')
            ->get();

        return view('admin.floor_management', compact('areas', 'tables', 'selectedArea', 'selectedAreaId'));
    }

    // ─────────────────────────────────────────────
    // AREA CRUD
    // ─────────────────────────────────────────────

    public function storeArea(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'description'  => 'nullable|string',
            'floor_number' => 'required|integer|min:1|max:99',
        ]);

        Area::create($request->only(['name', 'description', 'floor_number']));

        return back()->with('success', "Area \"{$request->name}\" berhasil ditambahkan.");
    }

    public function updateArea(Request $request, $id)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'description'  => 'nullable|string',
            'floor_number' => 'required|integer|min:1|max:99',
            'is_active'    => 'nullable|boolean',
        ]);

        $area = Area::findOrFail($id);
        $area->update([
            'name'         => $request->name,
            'description'  => $request->description,
            'floor_number' => $request->floor_number,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return back()->with('success', "Area \"{$area->name}\" berhasil diperbarui.");
    }

    public function destroyArea($id)
    {
        $area = Area::findOrFail($id);

        if ($area->tables()->count() > 0) {
            return back()->with('error', "Area masih memiliki meja. Pindahkan meja terlebih dahulu.");
        }

        $area->delete();
        return back()->with('success', 'Area berhasil dihapus.');
    }

    // ─────────────────────────────────────────────
    // TABLE CRUD
    // ─────────────────────────────────────────────

    public function storeTable(Request $request)
    {
        $request->validate([
            'code'         => 'required|string|max:20|unique:tables,code',
            'area_fk_id'   => 'required|exists:areas,id',
            'shape'        => 'required|in:rectangle,circle',
            'capacity'     => 'required|integer|min:1|max:99',
            'min_spending' => 'required|numeric|min:0',
            'x_pos'        => 'nullable|numeric',
            'y_pos'        => 'nullable|numeric',
        ]);

        $area = Area::findOrFail($request->area_fk_id);

        Table::create([
            'code'         => strtoupper($request->code),
            'shape'        => $request->shape,
            'capacity'     => $request->capacity,
            'min_spending' => $request->min_spending,
            'area_fk_id'   => $request->area_fk_id,
            'area_id'      => $area->name,           // sync legacy string field
            'x_pos'        => $request->x_pos ?? 50,
            'y_pos'        => $request->y_pos ?? 50,
            'status'       => 'available',
        ]);

        return back()->with('success', "Meja \"{$request->code}\" berhasil ditambahkan.");
    }

    public function updateTable(Request $request, $id)
    {
        $request->validate([
            'code'         => "required|string|max:20|unique:tables,code,{$id}",
            'area_fk_id'   => 'required|exists:areas,id',
            'shape'        => 'required|in:rectangle,circle',
            'capacity'     => 'required|integer|min:1|max:99',
            'min_spending' => 'required|numeric|min:0',
        ]);

        $table = Table::findOrFail($id);
        $area  = Area::findOrFail($request->area_fk_id);

        $table->update([
            'code'         => strtoupper($request->code),
            'shape'        => $request->shape,
            'capacity'     => $request->capacity,
            'min_spending' => $request->min_spending,
            'area_fk_id'   => $request->area_fk_id,
            'area_id'      => $area->name,           // sync legacy
        ]);

        return back()->with('success', "Meja \"{$table->code}\" berhasil diperbarui.");
    }

    public function destroyTable($id)
    {
        $table = Table::findOrFail($id);

        if ($table->bookings()->whereIn('status', ['pending','confirmed','arrived'])->exists()) {
            return back()->with('error', 'Meja masih memiliki booking aktif, tidak dapat dihapus.');
        }

        $table->delete();
        return back()->with('success', 'Meja berhasil dihapus.');
    }

    // ─────────────────────────────────────────────
    // SAVE LAYOUT (drag & drop koordinat)
    // ─────────────────────────────────────────────

    public function saveLayout(Request $request)
    {
        $payload = $request->validate([
            '*.id'    => 'required|integer|exists:tables,id',
            '*.x_pos' => 'required|numeric',
            '*.y_pos' => 'required|numeric',
        ]);

        DB::transaction(function () use ($payload) {
            foreach ($payload as $item) {
                Table::where('id', $item['id'])->update([
                    'x_pos' => $item['x_pos'],
                    'y_pos' => $item['y_pos'],
                ]);
            }
        });

        return response()->json(['status' => 'success', 'updated' => count($payload)]);
    }

    public function updateMinSpending(Request $request, $id)
    {
        $request->validate([
            'min_spending' => 'required|numeric|min:0',
        ]);

        $table = Table::findOrFail($id);
        $table->update(['min_spending' => $request->min_spending]);

        return back()->with('success', "Minimum cash meja {$table->code} diperbarui menjadi Rp " . number_format($request->min_spending, 0, ',', '.'));
    }

    public function bulkUpdateMinSpending(Request $request)
    {
        $request->validate([
            'table_ids'    => 'required|array',
            'table_ids.*'  => 'exists:tables,id',
            'min_spending' => 'required|numeric|min:0',
        ]);

        Table::whereIn('id', $request->table_ids)->update([
            'min_spending' => $request->min_spending
        ]);

        $count = count($request->table_ids);
        return back()->with('success', "Berhasil memperbarui {$count} meja secara massal.");
    }
}
