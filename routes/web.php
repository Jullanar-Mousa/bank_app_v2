<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Analyst;
use App\Http\Controllers\Customer;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Simulator;
use App\Http\Controllers\Supervisor;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Analyst\AlertController;
use App\Http\Controllers\Analyst\CaseController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\EngineSettingController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ApiClientController;
use App\Http\Controllers\MlModelController;



Route::get('/', HomeController::class)->name('home');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/ml-models', [MlModelController::class, 'index'])->name('ml-models.index');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/api-clients', [ApiClientController::class, 'index'])->name('api-clients.index');
    Route::post('/api-clients', [ApiClientController::class, 'store'])->name('api-clients.store');
    Route::patch('/api-clients/{apiClient}/toggle', [ApiClientController::class, 'toggle'])->name('api-clients.toggle');
    Route::patch('/api-clients/{apiClient}/revoke', [ApiClientController::class, 'revoke'])->name('api-clients.revoke');
    Route::delete('/api-clients/{apiClient}', [ApiClientController::class, 'destroy'])->name('api-clients.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/settings/engine', [EngineSettingController::class, 'edit'])->name('settings.engine.edit');
    Route::put('/settings/engine', [EngineSettingController::class, 'update'])->name('settings.engine.update');
});

Route::middleware(['auth', 'role:admin,fraud_analyst'])->group(function () {
    Route::get('/watchlists', [WatchlistController::class, 'index'])->name('watchlists.index');
    Route::post('/watchlists', [WatchlistController::class, 'store'])->name('watchlists.store');
    Route::delete('/watchlists/{watchlist}', [WatchlistController::class, 'destroy'])->name('watchlists.destroy');
});

Route::middleware(['auth', 'role:admin,fraud_analyst'])->group(function () {
    // إدارة القواعد
    Route::resource('rules', RuleController::class);
    Route::patch('/rules/{rule}/toggle', [RuleController::class, 'toggleStatus'])->name('rules.toggle');
});

Route::middleware(['auth', 'role:admin,fraud_analyst'])->prefix('analyst')->name('analyst.')->group(function () {
    // التنبيهات
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/{alert}', [AlertController::class, 'show'])->name('alerts.show');
    Route::put('/alerts/{alert}', [AlertController::class, 'update'])->name('alerts.update');
    
    // تصعيد التنبيه لقضية
    Route::post('/alerts/{alert}/escalate', [CaseController::class, 'escalateFromAlert'])->name('alerts.escalate');

    // إدارة القضايا
    Route::get('/cases', [CaseController::class, 'index'])->name('cases.index');
    Route::get('/cases/{case}', [CaseController::class, 'show'])->name('cases.show');
    Route::put('/cases/{case}', [CaseController::class, 'update'])->name('cases.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('analyst')->name('analyst.')->group(function () {
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/{alert}', [AlertController::class, 'show'])->name('alerts.show');
    Route::put('/alerts/{alert}', [AlertController::class, 'update'])->name('alerts.update');
});

// إشعارات (أي مستخدم مسجّل)
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});

// ───────── المدير ─────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::resource('users', Admin\UserController::class);

    Route::get('roles', [Admin\RoleController::class, 'index'])->name('roles.index');
    Route::get('roles/{role}', [Admin\RoleController::class, 'show'])->name('roles.show');
    Route::put('roles/{role}', [Admin\RoleController::class, 'update'])->name('roles.update');

    Route::apiResource('rules', Admin\RuleController::class);
    Route::apiResource('watchlists', Admin\WatchlistController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('api-clients', Admin\ApiClientController::class)
        ->parameters(['api-clients' => 'apiClient'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('settings', [Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings/{key}', [Admin\SettingController::class, 'update'])->name('settings.update');

    Route::get('models', [Admin\ModelRegistryController::class, 'index'])->name('models.index');
    Route::get('models/{model}', [Admin\ModelRegistryController::class, 'show'])->name('models.show');

    Route::get('audit-logs', [Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
});

// ───────── المحلل ─────────
Route::middleware(['auth', 'role:fraud_analyst'])->prefix('analyst')->name('analyst.')->group(function () {
    Route::get('/', Analyst\DashboardController::class)->name('dashboard');

    Route::get('alerts', [Analyst\AlertController::class, 'index'])->name('alerts.index');
    Route::get('alerts/{alert}', [Analyst\AlertController::class, 'show'])->name('alerts.show');
    Route::post('alerts/{alert}/decision', [Analyst\AlertController::class, 'decide'])->name('alerts.decide');

    Route::get('cases', [Analyst\CaseController::class, 'index'])->name('cases.index');
    Route::post('cases', [Analyst\CaseController::class, 'store'])->name('cases.store');
    Route::get('cases/{case}', [Analyst\CaseController::class, 'show'])->name('cases.show');
    Route::put('cases/{case}', [Analyst\CaseController::class, 'update'])->name('cases.update');

    Route::get('cases/{case}/notes', [Analyst\CaseNoteController::class, 'index'])->name('cases.notes.index');
    Route::post('cases/{case}/notes', [Analyst\CaseNoteController::class, 'store'])->name('cases.notes.store');

    Route::post('cases/{case}/attachments', [Analyst\CaseAttachmentController::class, 'store'])->name('cases.attachments.store');
    Route::delete('attachments/{attachment}', [Analyst\CaseAttachmentController::class, 'destroy'])->name('attachments.destroy');

    Route::get('ai-proposals', [Analyst\AiProposalController::class, 'index'])->name('ai-proposals.index');
    Route::post('ai-proposals/{proposal}/approve', [Analyst\AiProposalController::class, 'approve'])->name('ai-proposals.approve');
    Route::post('ai-proposals/{proposal}/reject', [Analyst\AiProposalController::class, 'reject'])->name('ai-proposals.reject');

    Route::get('account-actions', [Analyst\AccountActionController::class, 'index'])->name('account-actions.index');
    Route::post('account-actions', [Analyst\AccountActionController::class, 'store'])->name('account-actions.store');

    Route::get('rule-suggestions', [Analyst\RuleSuggestionController::class, 'index'])->name('rule-suggestions.index');
    Route::post('rule-suggestions', [Analyst\RuleSuggestionController::class, 'store'])->name('rule-suggestions.store');
});

// ───────── المشرف ─────────
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/', Supervisor\DashboardController::class)->name('dashboard');

    Route::put('alerts/{alert}/assignment', [Supervisor\AlertAssignmentController::class, 'update'])->name('alerts.assign');

    Route::get('approvals', [Supervisor\ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('approvals/{approval}/decision', [Supervisor\ApprovalController::class, 'decide'])->name('approvals.decide');

    Route::get('rule-suggestions', [Supervisor\RuleSuggestionReviewController::class, 'index'])->name('rule-suggestions.index');
    Route::post('rule-suggestions/{suggestion}/approve', [Supervisor\RuleSuggestionReviewController::class, 'approve'])->name('rule-suggestions.approve');
    Route::post('rule-suggestions/{suggestion}/reject', [Supervisor\RuleSuggestionReviewController::class, 'reject'])->name('rule-suggestions.reject');

    Route::get('reports', [Supervisor\ReportController::class, 'index'])->name('reports.index');
});

// ───────── العميل ─────────
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/', Customer\DashboardController::class)->name('dashboard');

    Route::get('transactions', [Customer\TransactionController::class, 'index'])->name('transactions.index');
    Route::post('transactions', [Customer\TransactionController::class, 'store'])->name('transactions.store');

    Route::get('otp', [Customer\OtpChallengeController::class, 'index'])->name('otp.index');
    Route::post('otp/{challenge}/verify', [Customer\OtpChallengeController::class, 'verify'])->name('otp.verify');

    Route::get('reports', [Customer\ReportController::class, 'index'])->name('reports.index');
    Route::post('reports', [Customer\ReportController::class, 'store'])->name('reports.store');
});

// ───────── المحاكي (محلي فقط) ─────────
if (app()->environment(['local', 'testing'])) {
    Route::middleware(['auth', 'role:admin'])->prefix('simulator')->name('simulator.')->group(function () {
        Route::get('scenarios', [Simulator\ScenarioController::class, 'index'])->name('scenarios.index');
        Route::post('scenarios/{scenario}/run', [Simulator\ScenarioController::class, 'run'])->name('scenarios.run');
    });
}