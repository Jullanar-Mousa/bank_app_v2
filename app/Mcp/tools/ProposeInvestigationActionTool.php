<?php

namespace App\Mcp\Tools;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ProposeInvestigationActionTool extends Tool
{
    protected string $description = 'تسجيل مقترح تحقيق وتوصية من الذكاء الاصطناعي لتنبيه محدد دون تنفيذ حظر مباشر، ليقوم المحلل البشري بمراجعته.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'alert_id' => $schema->integer()
                ->description('معرف التنبيه (Alert ID).')
                ->required(),
            'recommended_action' => $schema->string()
                ->enum(['escalate_to_case', 'request_otp', 'freeze_account_review', 'mark_false_positive'])
                ->description('الإجراء المقترح للمحلل البشري.')
                ->required(),
            'confidence' => $schema->number()
                ->description('نسبة ثقة النموذج بالتوصية من 0 إلى 100.')
                ->required(),
            'reasoning_summary' => $schema->string()
                ->description('ملخص تحليلي يشرح أسباب التوصية والأدلة المستخرجة من المعاملات والأجهزة.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $alertId = $request->integer('alert_id');
        $action = $request->string('recommended_action');
        $confidence = $request->float('confidence');
        $summary = $request->string('reasoning_summary');

        $proposalId = null;

        // الحفظ في جدول ai_proposals إذا كان موجوداً في قاعدة البيانات
        if (Schema::hasTable('ai_proposals')) {
            $proposalId = DB::table('ai_proposals')->insertGetId([
                'alert_id' => $alertId,
                'proposed_action' => $action,
                'confidence_score' => $confidence,
                'rationale' => $summary,
                'status' => 'pending_analyst_review',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // توثيق المقترح في سجل التدقيق
        if (function_exists('activity')) {
            activity()
                ->withProperties([
                    'alert_id' => $alertId,
                    'proposal_id' => $proposalId,
                    'proposed_action' => $action,
                    'confidence' => $confidence,
                ])
                ->log('AI Agent submitted investigation proposal via MCP');
        }

        return Response::text(json_encode([
            'status' => 'proposal_recorded',
            'proposal_id' => $proposalId,
            'alert_id' => $alertId,
            'recommended_action' => $action,
            'governance_note' => 'تم تسجيل التوصية بنجاح وبانتظار موافقة المحلل البشري (Human-in-the-loop).',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}