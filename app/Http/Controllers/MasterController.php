<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterLevel;
use App\Models\BroadcastTemplate;
use App\Models\MasterTag;
use App\Models\MasterTagGroup;

class MasterController extends Controller
{
    public function index()
    {
        $levels = MasterLevel::orderBy('min_spending', 'asc')->get();
        $templates = BroadcastTemplate::orderBy('created_at', 'desc')->get();
        $tagGroups = MasterTagGroup::orderBy('name', 'asc')->get();
        $tags = MasterTag::with('group')->orderBy('master_tag_group_id')->orderBy('name', 'asc')->get();
        return view('admin.master', compact('levels', 'templates', 'tags', 'tagGroups'));
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
        $level = MasterLevel::findOrFail($id);
        
        // Count how many customers are using this level
        $customerCount = \App\Models\Customer::where('master_level_id', $id)->count();
        
        if ($customerCount > 0) {
            return redirect()->back()->with('error', "Gagal menghapus: Level '{$level->name}' masih digunakan oleh {$customerCount} pelanggan.");
        }

        $level->delete();
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

    public function storeTag(Request $request)
    {
        $validated = $request->validate([
            'master_tag_group_id' => 'required|exists:master_tag_groups,id',
            'name' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:20',
        ]);
        
        MasterTag::create($validated);
        return redirect()->back()->with('success', 'Tag created successfully');
    }

    public function updateTag(Request $request, $id)
    {
        $tag = MasterTag::findOrFail($id);
        $validated = $request->validate([
            'master_tag_group_id' => 'required|exists:master_tag_groups,id',
            'name' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:20',
        ]);
        
        $tag->update($validated);
        return redirect()->back()->with('success', 'Tag updated successfully');
    }

    public function destroyTag($id)
    {
        MasterTag::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Tag deleted successfully');
    }

    // --- Group Tag Methods ---
    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:master_tag_groups,name',
        ]);
        MasterTagGroup::create($validated);
        return redirect()->back()->with('success', 'Tag Group created successfully');
    }

    public function updateGroup(Request $request, $id)
    {
        $group = MasterTagGroup::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:master_tag_groups,name,' . $id,
        ]);
        $group->update($validated);
        return redirect()->back()->with('success', 'Tag Group updated successfully');
    }

    public function destroyGroup($id)
    {
        MasterTagGroup::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Tag Group deleted successfully');
    }
}
