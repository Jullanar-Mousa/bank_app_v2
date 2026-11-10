<?php

namespace App\Http\Controllers;

use App\Models\ApiClient;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiClientController extends Controller
{
    /**
     * عرض جميع مفاتيح التكامل والأنظمة المتصلة
     */
    public function index(): View
    {
        $clients = ApiClient::with('creator')
            ->latest('id')
            ->paginate(15);

        return view('api_clients.index', compact('clients'));
    }

    /**
     * توليد مفتاح تكامل جديد لنظام خارجي
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $keyId = 'bk_' . Str::random(24);
        $plainSecret = 'sec_' . Str::random(48);

        $client = ApiClient::create([
            'name' => $validated['name'],
            'key_id' => $keyId,
            'secret' => $plainSecret,
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        AuditLogService::log(
            action: 'API_KEY_CREATED',
            entity: $client,
            newValues: [
                'name' => $client->name,
                'key_id' => $client->key_id,
            ]
        );

        return redirect()->route('api-clients.index')->with([
            'status' => 'تم إنشاء مفتاح التكامل بنجاح.',
            'generated_key_id' => $keyId,
            'generated_secret' => $plainSecret,
            'client_name' => $client->name,
        ]);
    }

    /**
     * تبديل حالة التفعيل (تعطيل مؤقت أو إعادة تفعيل)
     */
    public function toggle(ApiClient $apiClient): RedirectResponse
    {
        $oldState = $apiClient->is_active;
        $apiClient->update([
            'is_active' => ! $oldState,
        ]);

        AuditLogService::log(
            action: $oldState ? 'API_KEY_DEACTIVATED' : 'API_KEY_ACTIVATED',
            entity: $apiClient,
            oldValues: ['is_active' => $oldState],
            newValues: ['is_active' => ! $oldState]
        );

        $msg = $apiClient->is_active ? 'تم تفعيل المفتاح بنجاح.' : 'تم تعطيل المفتاح بنجاح.';

        return redirect()->route('api-clients.index')->with('status', $msg);
    }

    /**
     * إلغاء/إبطال المفتاح نهائياً (Revoke)
     */
    public function revoke(ApiClient $apiClient): RedirectResponse
    {
        $apiClient->update([
            'is_active' => false,
            'revoked_at' => now(),
        ]);

        AuditLogService::log(
            action: 'API_KEY_REVOKED',
            entity: $apiClient,
            newValues: ['revoked_at' => now()]
        );

        return redirect()->route('api-clients.index')->with('status', 'تم إبطال وإلغاء صلاحية المفتاح نهائياً.');
    }

    /**
     * حذف المفتاح بالكامل
     */
    public function destroy(ApiClient $apiClient): RedirectResponse
    {
        AuditLogService::log(
            action: 'API_KEY_DELETED',
            entity: $apiClient,
            oldValues: [
                'name' => $apiClient->name,
                'key_id' => $apiClient->key_id,
            ]
        );

        $apiClient->delete();

        return redirect()->route('api-clients.index')->with('status', 'تم حذف مفتاح التكامل.');
    }
}