@extends('layouts.app')

@section('title', 'سجل العمليات')
@section('page_title', 'سجل المعاملات والتحويلات المالية')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-right text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-700 border-b border-slate-200">
                <tr>
                    <th class="p-4 font-bold">الرقم المرجعي</th>
                    <th class="p-4 font-bold">المستفيد</th>
                    <th class="p-4 font-bold">المبلغ</th>
                    <th class="p-4 font-bold">القناة</th>
                    <th class="p-4 font-bold">التاريخ</th>
                    <th class="p-4 font-bold">الحالة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $txn)
                    <tr class="hover:bg-slate-50">
                        <td class="p-4 font-mono font-bold text-slate-900">{{ $txn->reference_id }}</td>
                        <td class="p-4 font-mono">{{ $txn->beneficiary_account }}</td>
                        <td class="p-4 font-bold text-slate-900">{{ number_format($txn->amount, 2) }} {{ $txn->currency }}</td>
                        <td class="p-4 uppercase text-xs">{{ $txn->channel }}</td>
                        <td class="p-4 text-xs text-slate-400">{{ $txn->created_at->format('Y-m-d H:i') }}</td>
                        <td class="p-4">
                            <span class="text-xs px-2.5 py-1 rounded-full font-bold {{ $txn->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($txn->status === 'blocked' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                {{ $txn->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500">لا توجد معاملات منجزة حتى الآن.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection