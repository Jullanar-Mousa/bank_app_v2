<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AiProposalsReview extends Component
{
    public string $statusFilter = 'pending_analyst_review';
    public string $flashMessage = '';

    public function approveProposal(int $proposalId): void
    {
        if (! Schema::hasTable('ai_proposals')) {
            return;
        }

        $proposal = DB::table('ai_proposals')->where('id', $proposalId)->first();
        if (! $proposal) {
            return;
        }

        $analystId = auth()->id() ?? 1;

        // 1. تحديث حالة المقترح إلى معتمد
        $updateData = ['status' => 'approved', 'updated_at' => now()];
        if (Schema::hasColumn('ai_proposals', 'reviewed_by')) {
            $updateData['reviewed_by'] = $analystId;
        }
        if (Schema::hasColumn('ai_proposals', 'reviewed_at')) {
            $updateData['reviewed_at'] = now();
        }

        DB::table('ai_proposals')->where('id', $proposalId)->update($updateData);

        // 2. تنفيذ الإجراء المقترح على التنبيه والقضايا (Human-in-the-loop Execution)
        $action = $proposal->proposed_action ?? $proposal->action ?? '';
        $alertId = $proposal->alert_id ?? null;

        if ($alertId && Schema::hasTable('alerts')) {
            if ($action === 'mark_false_positive') {
                DB::table('alerts')->where('id', $alertId)->update([
                    'status' => 'false_positive',
                    'updated_at' => now(),
                ]);
            } elseif (in_array($action, ['escalate_to_case', 'freeze_account_review'])) {
                DB::table('alerts')->where('id', $alertId)->update([
                    'status' => 'escalated',
                    'updated_at' => now(),
                ]);

                // إنشاء قضية تحقيق جديدة في جدول cases إذا كان موجوداً
                if (Schema::hasTable('cases')) {
                    $caseData = [
                        'alert_id' => $alertId,
                        'status' => 'open',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    if (Schema::hasColumn('cases', 'assigned_to')) {
                        $caseData['assigned_to'] = $analystId;
                    }
                    if (Schema::hasColumn('cases', 'summary')) {
                        $caseData['summary'] = $proposal->rationale ?? $proposal->reasoning_summary ?? 'Escalated via AI Proposal';
                    }
                    DB::table('cases')->insert($caseData);
                }
            }
        }

        // 3. تسجيل الاعتماد في سجل التدقيق
        if (function_exists('activity')) {
            activity()
                ->withProperties([
                    'proposal_id' => $proposalId,
                    'alert_id' => $alertId,
                    'action' => $action,
                ])
                ->log('Analyst approved AI Investigation Proposal');
        }

        $this->flashMessage = "تم اعتماد مقترح الذكاء الاصطناعي #{$proposalId} وتنفيذ الإجراء بنجاح.";
    }

    public function rejectProposal(int $proposalId): void
    {
        if (! Schema::hasTable('ai_proposals')) {
            return;
        }

        $updateData = ['status' => 'rejected', 'updated_at' => now()];
        if (Schema::hasColumn('ai_proposals', 'reviewed_by')) {
            $updateData['reviewed_by'] = auth()->id() ?? 1;
        }

        DB::table('ai_proposals')->where('id', $proposalId)->update($updateData);

        if (function_exists('activity')) {
            activity()
                ->withProperties(['proposal_id' => $proposalId])
                ->log('Analyst rejected AI Investigation Proposal');
        }

        $this->flashMessage = "تم رفض مقترح الذكاء الاصطناعي #{$proposalId}.";
    }

    public function render()
    {
        $proposals = collect();

        if (Schema::hasTable('ai_proposals')) {
            $query = DB::table('ai_proposals')->orderByDesc('created_at');

            if ($this->statusFilter !== 'all') {
                $query->where('status', $this->statusFilter);
            }

            $proposals = $query->limit(20)->get();
        }

        return view('livewire.ai-proposals-review', [
            'proposals' => $proposals,
        ]);
    }
}