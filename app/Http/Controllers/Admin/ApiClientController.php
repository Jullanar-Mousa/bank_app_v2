<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $clients = ApiClient::latest()->paginate(15);

        return view('admin.api-clients.index', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'key_id' => ['required', 'string', 'max:100', 'unique:api_clients,key_id'],
        ]);

        $plainSecret = Str::random(40);

        ApiClient::create([
            'name' => $validated['name'],
            'key_id' => $validated['key_id'],
            'secret_hash' => hash('sha256', $plainSecret),
            'is_active' => true,
        ]);

        return redirect()->route('admin.api-clients.index')
            ->with('success', 'تم إنشاء عميل التكامل بنجاح.')
            ->with('generated_secret', $plainSecret);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ApiClient $apiClient): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $apiClient->update([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.api-clients.index')->with('success', 'تم تحديث بيانات العميل بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApiClient $apiClient): RedirectResponse
    {
        $apiClient->delete();

        return redirect()->route('admin.api-clients.index')->with('success', 'تم حذف عميل التكامل بنجاح.');
    }
}