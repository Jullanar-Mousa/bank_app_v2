<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Watchlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatchlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Watchlist::query();

        if ($request->filled('list_type')) {
            $query->where('list_type', $request->input('list_type'));
        }

        if ($request->filled('entry_type')) {
            $query->where('entry_type', $request->input('entry_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('entry_value', 'like', "%{$search}%");
        }

        $items = $query->latest()->paginate(20)->withQueryString();

        return view('admin.watchlists.index', compact('items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'list_type' => ['required', 'in:blacklist,whitelist'],
            'entry_type' => ['required', 'in:ip,device,account,beneficiary'],
            'entry_value' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        Watchlist::create([
            'list_type' => $validated['list_type'],
            'entry_type' => $validated['entry_type'],
            'entry_value' => $validated['entry_value'],
            'reason' => $validated['reason'] ?? null,
            'added_by' => auth()->id(),
        ]);

        return redirect()->route('admin.watchlists.index')->with('success', 'تمت إضافة العنصر إلى القائمة بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Watchlist $watchlist): RedirectResponse
    {
        $watchlist->delete();

        return redirect()->route('admin.watchlists.index')->with('success', 'تم حذف العنصر من القائمة بنجاح.');
    }
}