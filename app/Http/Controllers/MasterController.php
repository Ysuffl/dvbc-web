<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterCategory;
use App\Models\MasterLevel;
use App\Models\BroadcastTemplate;

class MasterController extends Controller
{
    public function index()
    {
        $categories = MasterCategory::all();
        $levels = MasterLevel::orderBy('min_spending', 'asc')->get();
        $templates = BroadcastTemplate::orderBy('created_at', 'desc')->get();
        return view('admin.master', compact('categories', 'levels', 'templates'));
    }

    // --- Category Methods ---
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:master_categories,name',
            'icon' => 'nullable|string',
            'bg_color' => 'nullable|string',
            'text_color' => 'nullable|string',
        ]);
        $validated['name'] = strtoupper(str_replace(' ', '_', $validated['name']));

        MasterCategory::create($validated);
        return redirect()->back()->with('success', 'Category added successfully');
    }

    public function updateCategory(Request $request, $id)
    {
        $category = MasterCategory::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|unique:master_categories,name,' . $id,
            'icon' => 'nullable|string',
            'bg_color' => 'nullable|string',
            'text_color' => 'nullable|string',
        ]);
        $validated['name'] = strtoupper(str_replace(' ', '_', $validated['name']));

        $category->update($validated);
        return redirect()->back()->with('success', 'Category updated successfully');
    }

    public function destroyCategory($id)
    {
        MasterCategory::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Category deleted successfully');
    }

    // --- Level Methods ---
    public function storeLevel(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:master_levels,name',
            'min_spending' => 'required|numeric|min:0',
            'badge_color' => 'nullable|string',
        ]);

        MasterLevel::create($validated);
        return redirect()->back()->with('success', 'Level added successfully');
    }

    public function updateLevel(Request $request, $id)
    {
        $level = MasterLevel::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|unique:master_levels,name,' . $id,
            'min_spending' => 'required|numeric|min:0',
            'badge_color' => 'nullable|string',
        ]);

        $level->update($validated);
        return redirect()->back()->with('success', 'Level updated successfully');
    }

    public function destroyLevel($id)
    {
        MasterLevel::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Level deleted successfully');
    }

    // --- Template Methods ---
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string|in:promotion,info,greeting',
        ]);
        
        BroadcastTemplate::create($validated);
        return redirect()->back()->with('success', 'Template created successfully');
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = BroadcastTemplate::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string|in:promotion,info,greeting',
        ]);
        
        $template->update($validated);
        return redirect()->back()->with('success', 'Template updated successfully');
    }

    public function destroyTemplate($id)
    {
        BroadcastTemplate::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Template deleted successfully');
    }
}
