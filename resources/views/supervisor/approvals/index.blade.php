@extends('layouts.app')

@section('title', 'طلبات الاعتماد')
@section('page_title', 'اعتماد ومصادقة إجراءات الحسابات')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-right text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-700 border-b border-slate-200">
                <tr>
                    <th class="p-4 font-bold">الحساب المستهدف</th>
                    <th class="p-4 font-bold">نوع الإجراء</th>
                    <th class="p-4 font-bold">المحلل مقدم الطلب</th>
                    <th class="p-4 font-bold">السبب والمبرر</th>
                    <th class="p-4 font-bold">التاريخ</th>
                    <th class="p-4 font-bold text-center">القرار</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pendingActions as $action)
                    <tr class="hover:bg-slate-50">
                        <td class="p-4 font-mono font-bold text-slate-900">{{ $action->account_id }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 text-xs rounded-full font-bold {{ $action->action_type === 'freeze' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ strtoupper($action->action_type) }}
                            </span>
                        </td>
                        <td class="p-4 font-medium text-slate-800">{{ $action->requestedBy->name ?? 'N/A' }}</td>
                        <td class="p-4 text-xs text-slate-600 max-w-xs">{{ $action->reason }}</td>
                        <td class="p-4 text-xs text-slate-400">{{ $action->created_at->format('Y-m-d H:i') }}</td>
                        <td class="p-4 text-center">
                            <form action="{{ route('supervisor.approvals.decide', $action) }}" method="POST" class="flex items-center justify-center gap-2">
                                @csrf
                                <button type="submit" name="decision" value="approved" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold">
                                    موافقة
                                </button>
                                <button type="submit" name="decision" value="rejected" class="px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-xs font-semibold">
                                    رفض
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500">لا توجد طلبات معلقة بانتظار الاعتماد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100">
            {{ $pendingActions->links() }}
        </div>
    </div>
</div>
@endsection