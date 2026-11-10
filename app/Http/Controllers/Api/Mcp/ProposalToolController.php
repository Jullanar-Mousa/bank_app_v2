<?php

namespace App\Http\Controllers\Api\Mcp;

use App\Http\Controllers\Controller;
use App\Models\AiProposal;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalToolController extends Controller
{
    /**
     * note_case_add / action_propose
     * المساعد ما بينفّذ شي، بينسجّل مقترح ينتظر موافقة المحلل
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tool_name' => 'required|in:note_case_add,action_propose',
            'case_id'   => 'nullable|integer|exists:cases,id',
            'alert_id'  => 'nullable|integer|exists:alerts,id',
            'payload'   => 'required|array',
        ]);

        $proposal = AiProposal::forceCreate($data + ['status' => 'pending']);

        Audit::log($request, 'mcp.proposal_created', $proposal, null, ['tool' => $data['tool_name']]);

        return response()->json(['id' => $proposal->id, 'status' => 'pending'], 201);
    }
}