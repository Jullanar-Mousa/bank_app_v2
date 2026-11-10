<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatchlistController extends Controller
{
    /**
     * عرض عناصر القوائم المحظورة والموثوقة
     */
    public function index(Request $request): View
    {
        $query = Watchlist::query();

        if ($request->filled('list_type')) {
            $query->where('list_type', $request->input('list_type'));
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->input('entity_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('entity_value', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $items = $query->latest('added_at')->latest('id')->paginate(15)->withQueryString();

        return view('watchlists.index', compact('items'));
    }

    /**
     * إضافة مدخل جديد للقائمة السوداء أو البيضاء
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'list_type' => 'required|in:blacklist,whitelist',
            'entity_type' => 'required|in:account,ip,beneficiary,device',
            'entity_value' => 'required|string|max:255',
            'reason' => 'nullable|string|max:1000',
        ]);

        // التحقق من عدم وجود نفس القيد مسبقاً
        $exists = Watchlist::where('list_type', $validated['list_type'])
            ->where('entity_type', $validated['entity_type'])
            ->where('entity_value', $validated['entity_value'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'entity_value' => 'هذه القيمة موجودة بالفعل في هذه القائمة لنفس نوع الكيان.',
            ]);
        }

        // حفظ الكيان المنشأ في متغير لاستخدامه في التدقيق الأمني
        $watchlist = Watchlist::create([
            'list_type' => $validated['list_type'],
            'entity_type' => $validated['entity_type'],
            'entity_value' => trim($validated['entity_value']),
            'reason' => $validated['reason'],
            'added_at' => now(),
        ]);

        // تسجيل العملية في سجل التدقيق الأمني
        AuditLogService::log(
            action: 'WATCHLIST_ADD',
            entity: $watchlist,
            newValues: $validated
        );

        $listName = $validated['list_type'] === 'blacklist' ? 'القائمة السوداء' : 'القائمة الموثوقة';

        return redirect()->route('watchlists.index')->with(
            'status',
            "تمت إضافة العنصر إلى {$listName} بنجاح."
        );
    }

    /**
     * حذف مدخل من القائمة
     */
    public function destroy(Watchlist $watchlist): RedirectResponse
    {
        $oldData = $watchlist->toArray();

        // تسجيل العملية أولاً قبل تنفيذ الحذف وإعادة التوجيه
        AuditLogService::log(
            action: 'WATCHLIST_REMOVE',
            entity: $watchlist,
            oldValues: $oldData
        );

        $watchlist->delete();

        return redirect()->route('watchlists.index')->with('status', 'تمت إزالة العنصر من القائمة بنجاح.');
    }
}