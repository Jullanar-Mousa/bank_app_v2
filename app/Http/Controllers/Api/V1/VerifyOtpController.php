<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OtpChallenge;
use App\Support\OtpCheck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyOtpController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'challenge_id' => 'required|integer|exists:otp_challenges,id',
            'code'         => 'required|digits:6',
        ]);

        $challenge = OtpChallenge::findOrFail($data['challenge_id']);

        $result = OtpCheck::attempt($challenge, $data['code']);

        return response()->json(
            ['result' => $result],
            $result === 'verified' ? 200 : 422
        );
    }
}