<div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                    MCP AI Assistant
                </span>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    مقترحات التحقيق الذكية (بانتظار مراجعة المحلل)
                </h3>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                توصيات يولدها الذكاء الاصطناعي عبر خادم MCP — لا تُنفّذ أي إجراءات حظر إلا بعد اعتماد المحلل البشري.
            </p>
        </div>

        <select wire:model.live="statusFilter" class="text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
            <option value="pending_analyst_review">بانتظار المراجعة (Pending)</option>
            <option value="approved">المعتمدة (Approved)</option>
            <option value="rejected">المرفوضة (Rejected)</option>
            <option value="all">جميع المقترحات</option>
        </select>
    </div>

    @if($flashMessage)
        <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between">
            <span>{{ $flashMessage }}</span>
            <button wire:click="$set('flashMessage', '')" class="text-emerald-600 hover:text-emerald-900 font-bold">✕</button>
        </div>
    @endif

    @if($proposals->isEmpty())
        <div class="p-8 text-center border border-dashed border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-500 dark:text-gray-400">
            لا توجد مقترحات ذكاء اصطناعي ضمن هذا التصنيف حالياً.
        </div>
    @else
        <div class="space-y-4">
            @foreach($proposals as $item)
                @php
                    $actionLabel = match($item->proposed_action ?? '') {
                        'escalate_to_case' => 'تصعيد إلى قضية تحقيق (Escalate to Case)',
                        'request_otp' => 'طلب تحقق إضافي (Request OTP)',
                        'freeze_account_review' => 'مراجعة تجميد الحساب (Freeze Review)',
                        'mark_false_positive' => 'إغلاق كإنذار كاذب (False Positive)',
                        default => $item->proposed_action ?? 'توصية تحقيق',
                    };
                    $confidence = $item->confidence_score ?? $item->confidence ?? 0;
                @endphp

                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="space-y-1.5 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                مقترح #{{ $item->id }}
                            </span>
                            <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">
                                التنبيه #{{ $item->alert_id ?? 'N/A' }}
                            </span>
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                {{ $actionLabel }}
                            </span>
                            <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">
                                نسبة الثقة: {{ number_format($confidence, 1) }}%
                            </span>
                        </div>

                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                            {{ $item->rationale ?? $item->reasoning_summary ?? 'لا يوجد ملخص مرفق.' }}
                        </p>

                        <div class="text-[11px] text-gray-400">
                            تاريخ التوليد: {{ $item->created_at ?? '' }}
                        </div>
                    </div>

                    @if(($item->status ?? '') === 'pending_analyst_review')
                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                wire:click="approveProposal({{ $item->id }})"
                                wire:loading.attr="disabled"
                                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition cursor-pointer"
                            >
                                ✓ اعتماد وتنفيذ
                            </button>
                            <button
                                wire:click="rejectProposal({{ $item->id }})"
                                wire:loading.attr="disabled"
                                class="px-3.5 py-2 bg-white hover:bg-red-50 text-red-600 border border-red-300 text-xs font-semibold rounded-lg transition cursor-pointer"
                            >
                                ✕ رفض
                            </button>
                        </div>
                    @else
                        <span class="text-xs font-bold px-3 py-1 rounded-full {{ ($item->status ?? '') === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                            {{ ($item->status ?? '') === 'approved' ? 'معتمد ومنفّذ' : 'مرفوض' }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>