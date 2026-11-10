<?php

namespace App\Http\Controllers\Api\Mcp;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\CaseNote;
use App\Models\FraudCase;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseToolController extends Controller
{
    /** معلومات الحالة لتلخيصها: قراءة فقط */
    public function show(Request $request, FraudCase $case): JsonResponse
    {
        Audit::log($request, 'mcp.case_details', $case);

        return response()->json([
            'case'   => $case->only(['id', 'title', 'status', 'priority']),
            'alerts' => Alert::where('case_id', $case->id)->get(['id', 'status', 'priority']),
            'notes'  => CaseNote::where('case_id', $case->id)->orderBy('created_at')
                            ->get(['note', 'is_ai_generated', 'created_at']),
        ]);
    }
}