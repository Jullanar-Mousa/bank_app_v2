<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiClientSecret
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientId = $request->header('X-Client-ID');
        $clientSecret = $request->header('X-Client-Secret');

        if (! $clientId || ! $clientSecret) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات المصادقة مفقودة (X-Client-ID أو X-Client-Secret).',
            ], 401);
        }

        $client = ApiClient::where('key_id', $clientId)->first();

        if (! $client || ! $client->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'العميل غير صالح أو معطل.',
            ], 403);
        }

        // مقارنة الـ secret بعد فك تشفيره التلقائي بواسطة الموديل
        if (! hash_equals((string) $client->secret, (string) $clientSecret)) {
            return response()->json([
                'success' => false,
                'message' => 'مفتاح التحقق السري غير صحيح.',
            ], 401);
        }

        // تحديث تاريخ آخر استخدام
        $client->updateQuietly(['last_used_at' => now()]);

        $request->attributes->set('api_client', $client);

        return $next($request);
    }
}