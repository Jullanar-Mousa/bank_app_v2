<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountAction;
use App\Models\AccountEvent;
use App\Models\AiProposal;
use App\Models\Alert;
use App\Models\Approval;
use App\Models\AuditLog;
use App\Models\CaseAttachment;
use App\Models\CaseNote;
use App\Models\Customer;
use App\Models\CustomerReport;
use App\Models\FraudCase;
use App\Models\Label;
use App\Models\Notification;
use App\Models\OtpChallenge;
use App\Models\Rule;
use App\Models\RuleSuggestion;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * يعبّي جداول سير العمل. لازم يشتغل بعد DemoDataSeeder و AhmadScenarioSeeder.
 * مو idempotent: استعملي migrate:fresh --seed.
 */
class WorkflowDataSeeder extends Seeder
{
    public function run(): void
    {
        $analyst = User::where('email', 'analyst@bank.test')->firstOrFail();
        $supervisor = User::where('email', 'supervisor@bank.test')->firstOrFail();
        $admin = User::where('email', 'admin@bank.test')->firstOrFail();

        $ahmadAlert = Alert::whereHas('riskScore.transaction.account',
            fn ($q) => $q->where('account_number', 'SY-AHMAD-0001'))->first();

        if ($ahmadAlert) {
            $this->seedAhmadWorkflow($ahmadAlert, $analyst);
        }

        $this->seedResolvedAlerts($ahmadAlert?->id, $analyst, $supervisor);
        $this->seedOtpChallenges($ahmadAlert?->id);
        $this->seedLabels($analyst);
        $this->seedCustomerReports($ahmadAlert?->id, $analyst);
        $this->seedRuleSuggestions($analyst, $supervisor);
        $this->seedAuditLogs($admin, $analyst, $supervisor);
        $this->seedNotifications($analyst, $supervisor, $ahmadAlert);
    }

    /** سيناريو أحمد: الحالة مفتوحة، مقترحات AI تنتظر المحلل، وطلب تجميد ينتظر المشرف */
    private function seedAhmadWorkflow(Alert $alert, User $analyst): void
    {
        $case = $alert->fraudCase;
        $account = Account::where('account_number', 'SY-AHMAD-0001')->firstOrFail();

        CaseNote::create([
            'case_id' => $case->id,
            'user_id' => $analyst->id,
            'note' => 'تسلسل مشبوه: جهاز جديد ثم تغيير جوال ثم مستفيد جديد ثم تحويل كبير خلال 22 دقيقة.',
            'is_ai_generated' => false,
        ]);

        CaseAttachment::create([
            'case_id' => $case->id,
            'uploaded_by' => $analyst->id,
            'file_name' => 'ip-geolocation-istanbul.png',
            'file_path' => 'case-attachments/seed/ip-geolocation-istanbul.png', // مسار وهمي
            'mime_type' => 'image/png',
            'file_size' => 148_320,
        ]);

        // المساعد الذكي يقترح فقط، والمحلل يقرر (لا تنفيذ تلقائي)
        AiProposal::create([
            'case_id' => $case->id,
            'alert_id' => $alert->id,
            'tool_name' => 'note_case_add',
            'payload' => ['note' => 'نمط يطابق استيلاء على حساب (Account Takeover). الثقة 97%. المستفيد جديد والمبلغ ~50 ضعف المعتاد.'],
            'status' => 'pending',
        ]);
        AiProposal::create([
            'case_id' => $case->id,
            'alert_id' => $alert->id,
            'tool_name' => 'action_propose',
            'payload' => ['action' => 'freeze', 'account_number' => $account->account_number, 'reason' => 'اشتباه استيلاء على الحساب'],
            'status' => 'pending',
        ]);

        // طلب تجميد من المحلل ينتظر موافقة المشرف (مبدأ العينين)
        $approval = Approval::create([
            'type' => 'freeze_account',
            'subject_type' => Account::class,
            'subject_id' => $account->id,
            'requested_by' => $analyst->id,
            'status' => 'pending',
            'reason' => 'اشتباه استيلاء على حساب أحمد، تحويل 7400 لمستفيد جديد من جهاز جديد.',
        ]);
        AccountAction::create([
            'account_id' => $account->id,
            'transaction_id' => $alert->riskScore->transaction_id,
            'alert_id' => $alert->id,
            'approval_id' => $approval->id,
            'action' => 'freeze',
            'status' => 'pending',
            'requested_by' => $analyst->id,
            'reason' => $approval->reason,
        ]);

        // إجراء النظام التلقائي: إيقاف التحويل (requested_by = null يعني النظام)
        AccountAction::create([
            'account_id' => $account->id,
            'transaction_id' => $alert->riskScore->transaction_id,
            'alert_id' => $alert->id,
            'action' => 'block_transaction',
            'status' => 'executed',
            'requested_by' => null,
            'reason' => 'درجة الخطورة 94 (أعلى من حد الإيقاف 75)',
            'executed_at' => now()->subHours(2)->subMinutes(38),
        ]);
    }

    /** أول 4 تنبيهات من الـ Demo: مؤكد احتيال / إنذار كاذب / مُصعّد / قيد المراجعة */
    private function seedResolvedAlerts(?int $skipAlertId, User $analyst, User $supervisor): void
    {
        $alerts = Alert::with('riskScore.transaction.account')
            ->when($skipAlertId, fn ($q) => $q->where('id', '!=', $skipAlertId))
            ->orderBy('id')->limit(4)->get();

        // 0) احتيال مؤكد + حالة مغلقة + تجميد معتمد ومنفّذ
        if ($a = $alerts->get(0)) {
            $txn = $a->riskScore->transaction;
            $account = $txn->account;

            $case = FraudCase::create([
                'title' => 'احتيال مؤكد: حساب '.$account->account_number,
                'assigned_to' => $analyst->id, 'status' => 'closed', 'priority' => 'high',
            ]);
            $a->update([
                'case_id' => $case->id, 'status' => 'resolved', 'assigned_to' => $analyst->id,
                'decision' => 'confirmed_fraud', 'decision_reason' => 'العميل أكد أنه لم ينفذ العملية.',
                'decided_by' => $analyst->id, 'decided_at' => now()->subDays(2),
            ]);
            Label::create([
                'transaction_id' => $txn->id, 'alert_id' => $a->id, 'is_fraud' => true,
                'source' => 'analyst', 'labeled_by' => $analyst->id,
            ]);
            CaseNote::create(['case_id' => $case->id, 'user_id' => $analyst->id,
                'note' => 'تواصلنا مع العميل وأكد أن العملية غير مصرّح بها.']);
            CaseNote::create(['case_id' => $case->id, 'user_id' => $supervisor->id,
                'note' => 'تمت الموافقة على التجميد وإغلاق الحالة.']);

            $approval = Approval::create([
                'type' => 'freeze_account', 'subject_type' => Account::class, 'subject_id' => $account->id,
                'requested_by' => $analyst->id, 'approved_by' => $supervisor->id, 'status' => 'approved',
                'reason' => 'احتيال مؤكد', 'decided_at' => now()->subDays(2)->addHour(),
            ]);
            AccountAction::create([
                'account_id' => $account->id, 'transaction_id' => $txn->id, 'alert_id' => $a->id,
                'approval_id' => $approval->id, 'action' => 'freeze', 'status' => 'executed',
                'requested_by' => $analyst->id, 'reason' => 'احتيال مؤكد',
                'executed_at' => now()->subDays(2)->addHour(),
            ]);
            $account->update(['status' => 'frozen']);
        }

        // 1) إنذار كاذب
        if ($a = $alerts->get(1)) {
            $a->update([
                'status' => 'resolved', 'assigned_to' => $analyst->id,
                'decision' => 'false_positive', 'decision_reason' => 'العميل سافر وأكد العملية.',
                'decided_by' => $analyst->id, 'decided_at' => now()->subDay(),
            ]);
            Label::create([
                'transaction_id' => $a->riskScore->transaction_id, 'alert_id' => $a->id, 'is_fraud' => false,
                'source' => 'analyst', 'labeled_by' => $analyst->id,
            ]);
        }

        // 2) مُصعّد للمشرف
        if ($a = $alerts->get(2)) {
            $a->update([
                'status' => 'in_progress', 'assigned_to' => $analyst->id,
                'decision' => 'escalated', 'decision_reason' => 'مبلغ كبير يحتاج رأي المشرف.',
                'decided_by' => $analyst->id, 'decided_at' => now()->subHours(5),
            ]);
        }

        // 3) قيد المراجعة مع حالة وملاحظة
        if ($a = $alerts->get(3)) {
            $case = FraudCase::create([
                'title' => 'قيد المراجعة: '.$a->riskScore->transaction->account->account_number,
                'assigned_to' => $analyst->id, 'status' => 'in_review', 'priority' => 'medium',
            ]);
            $a->update(['case_id' => $case->id, 'status' => 'in_progress', 'assigned_to' => $analyst->id]);
            CaseNote::create(['case_id' => $case->id, 'user_id' => $analyst->id,
                'note' => 'بانتظار رد العميل على طلب المعلومات.']);
            CaseNote::create(['case_id' => $case->id, 'user_id' => null,
                'note' => 'ملخص آلي: العملية تتجاوز المتوسط بشكل ملحوظ لكن المستفيد قديم.',
                'is_ai_generated' => true]);
        }
    }

    /** OTP لكل تنبيه فيه otp_required، ولأحداث أحمد (المهاجم استلم الرموز بعد السيطرة على الشريحة) */
    private function seedOtpChallenges(?int $skipAlertId): void
    {
        $statuses = ['verified', 'failed', 'expired'];

        Alert::with('riskScore.transaction.account')
            ->when($skipAlertId, fn ($q) => $q->where('id', '!=', $skipAlertId))
            ->get()
            ->filter(fn ($a) => $a->riskScore->decision === 'otp_required')
            ->values()
            ->each(function ($a, $i) use ($statuses) {
                $txn = $a->riskScore->transaction;
                OtpChallenge::factory()->{$statuses[$i % 3]}()->create([
                    'customer_id' => $txn->account->customer_id,
                    'transaction_id' => $txn->id,
                ]);
            });

        $ahmad = Customer::where('email', 'ahmad@bank.test')->first();
        if ($ahmad) {
            AccountEvent::where('idempotency_key', 'like', 'seed-ahmad-attack-%')
                ->whereIn('event_type', ['login', 'change_phone', 'add_beneficiary'])
                ->get()
                ->each(fn ($e) => OtpChallenge::factory()->verified()->create([
                    'customer_id' => $ahmad->id,
                    'event_id' => $e->id,
                ]));
        }
    }

    /** وسوم على عمليات عادية (مادة للتدريب الخارجي) */
    private function seedLabels(User $analyst): void
    {
        Transaction::whereDoesntHave('riskScore')->inRandomOrder()->limit(15)->get()
            ->each(fn ($t) => Label::factory()->create([
                'transaction_id' => $t->id,
                'is_fraud' => false,
                'source' => fake()->randomElement(['analyst', 'customer']),
                'labeled_by' => $analyst->id,
            ]));
    }

    private function seedCustomerReports(?int $ahmadAlertId, User $analyst): void
    {
        // بلاغ أحمد عن التحويل
        if ($ahmadAlertId) {
            $ahmad = Customer::where('email', 'ahmad@bank.test')->first();
            $txn = Alert::find($ahmadAlertId)->riskScore->transaction;
            CustomerReport::create([
                'customer_id' => $ahmad->id,
                'transaction_id' => $txn->id,
                'description' => 'وصلتني رسالة بتغيير رقمي وأنا ما غيرته، وما نفّذت هالتحويل.',
                'status' => 'new',
            ]);
        }

        // 3 بلاغات بحالات مختلفة لعملاء الـ Demo
        $statuses = ['under_review', 'confirmed', 'rejected'];
        Customer::whereDoesntHave('user')->inRandomOrder()->limit(3)->get()->each(function ($c, $i) use ($statuses, $analyst) {
            $txn = Transaction::whereIn('account_id', $c->accounts()->pluck('id'))->inRandomOrder()->first();
            CustomerReport::factory()->create([
                'customer_id' => $c->id,
                'transaction_id' => $txn?->id,
                'status' => $statuses[$i],
                'handled_by' => $analyst->id,
                'handled_at' => now()->subHours(6),
            ]);
        });
    }

    private function seedRuleSuggestions(User $analyst, User $supervisor): void
    {
        RuleSuggestion::create([
            'suggested_by' => $analyst->id,
            'title' => 'تحويل كبير ليلاً لمستفيد جديد',
            'description' => 'لاحظت عدة حالات احتيال بين 2 و5 فجراً لمستفيدين جدد.',
            'conditions' => ['all' => [
                ['field' => 'hour_of_day', 'op' => 'between', 'value' => [2, 5]],
                ['field' => 'beneficiary_age_hours', 'op' => '<', 'value' => 24],
            ]],
            'status' => 'pending',
        ]);

        RuleSuggestion::create([
            'suggested_by' => $analyst->id,
            'title' => 'تنبيه لأي تحويل أكبر من 1000',
            'description' => 'قاعدة عامة جداً.',
            'conditions' => ['all' => [['field' => 'amount', 'op' => '>', 'value' => 1000]]],
            'status' => 'rejected',
            'reviewed_by' => $supervisor->id,
            'reviewed_at' => now()->subDays(3),
            'review_note' => 'عامة زيادة وبتعمل إنذارات كاذبة كتير.',
        ]);

        $rule = Rule::updateOrCreate(
            ['rule_name' => 'rapid_beneficiary_then_transfer'],
            [
                'description' => 'تحويل خلال 30 دقيقة من إضافة المستفيد',
                'weight' => 25, 'priority' => 2, 'is_active' => true, 'action' => 'add_score',
                'conditions' => ['all' => [['field' => 'beneficiary_age_minutes', 'op' => '<', 'value' => 30]]],
            ]
        );
        RuleSuggestion::create([
            'suggested_by' => $analyst->id,
            'title' => 'تحويل سريع بعد إضافة مستفيد',
            'description' => 'نمط متكرر بحالات الاستيلاء على الحساب.',
            'conditions' => $rule->conditions,
            'status' => 'accepted',
            'reviewed_by' => $supervisor->id,
            'reviewed_at' => now()->subDays(5),
            'review_note' => 'مقبولة وتحولت لقاعدة فعلية.',
            'rule_id' => $rule->id,
        ]);
    }

    private function seedAuditLogs(User $admin, User $analyst, User $supervisor): void
    {
        $logs = [
            [$admin, 'user.created', 'User', $analyst->id, null, ['email' => $analyst->email, 'role' => 'fraud_analyst']],
            [$admin, 'setting.updated', 'Setting', null, ['block_min' => 80], ['block_min' => 75]],
            [$admin, 'rule.created', 'Rule', Rule::value('id'), null, ['rule_name' => 'new_device']],
            [$analyst, 'alert.decided', 'Alert', Alert::value('id'), ['status' => 'new'], ['status' => 'resolved', 'decision' => 'confirmed_fraud']],
            [$supervisor, 'approval.approved', 'Approval', Approval::where('status', 'approved')->value('id'), ['status' => 'pending'], ['status' => 'approved']],
            [$supervisor, 'rule_suggestion.reviewed', 'RuleSuggestion', RuleSuggestion::where('status', 'rejected')->value('id'), ['status' => 'pending'], ['status' => 'rejected']],
            [$analyst, 'account.freeze_requested', 'Account', Account::where('account_number', 'SY-AHMAD-0001')->value('id'), null, ['status' => 'pending']],
            [null, 'transaction.blocked', 'Transaction', Transaction::where('status', 'blocked')->value('id'), ['status' => 'pending'], ['status' => 'blocked']],
        ];

        foreach ($logs as [$user, $action, $entity, $entityId, $old, $new]) {
            AuditLog::factory()->create([
                'user_id' => $user?->id,
                'action' => $action,
                'entity_type' => $entity,
                'entity_id' => $entityId,
                'old_values' => $old,
                'new_values' => $new,
            ]);
        }
    }

    private function seedNotifications(User $analyst, User $supervisor, ?Alert $ahmadAlert): void
    {
        if ($ahmadAlert) {
            $this->notify($analyst, 'HighRiskAlert', [
                'alert_id' => $ahmadAlert->id,
                'message' => 'تنبيه عالي الخطورة (94): تحويل 7400 من حساب أحمد الخطيب.',
            ]);
            $this->notify($supervisor, 'ApprovalRequested', [
                'message' => 'طلب تجميد حساب SY-AHMAD-0001 بانتظار موافقتك.',
            ]);

            $ahmadUser = User::where('email', 'ahmad@bank.test')->first();
            if ($ahmadUser) {
                $this->notify($ahmadUser, 'SuspiciousActivityNotice', [
                    'message' => 'أوقفنا عملية تحويل مشبوهة على حسابك. إذا لم تكن أنت، تواصل مع البنك.',
                ]);
            }
        }
    }

    private function notify(User $user, string $type, array $data): void
    {
        Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\'.$type,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => $data,
            'read_at' => null,
        ]);
    }
}