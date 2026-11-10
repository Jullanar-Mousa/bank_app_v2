<?php

namespace App\Support;

use App\Models\OtpChallenge;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class OtpCheck
{
    /**
     * النتائج: verified | wrong | locked | expired | closed
     */
    public static function attempt(OtpChallenge $challenge, string $code): string
    {
        $max = (int) (Setting::where('key', 'otp_max_attempts')->value('value') ?? 3);

        if ($challenge->status !== 'pending') {
            return 'closed';
        }

        if ($challenge->expires_at && Carbon::parse($challenge->expires_at)->isPast()) {
            $challenge->forceFill(['status' => 'expired'])->save();
            return 'expired';
        }

        if ($challenge->attempts >= $max) {
            $challenge->forceFill(['status' => 'failed'])->save();
            return 'locked';
        }

        $challenge->forceFill(['attempts' => $challenge->attempts + 1])->save();

        if (self::matches((string) $challenge->code_hash, $code)) {
            $challenge->forceFill(['status' => 'verified', 'verified_at' => now()])->save();
            return 'verified';
        }

        if ($challenge->attempts >= $max) {
            $challenge->forceFill(['status' => 'failed'])->save();
        }

        return 'wrong';
    }

    private static function matches(string $hash, string $code): bool
    {
        try {
            if (Hash::check($code, $hash)) {
                return true;
            }
        } catch (\Throwable $e) {
            // الهاش مو bcrypt، منجرّب sha256 تحت
        }

        return hash_equals($hash, hash('sha256', $code));
    }
}