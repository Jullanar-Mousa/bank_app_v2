<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Alert;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\FraudAlertsExport;
use Maatwebsite\Excel\Facades\Excel;

class FraudAnalyticsCharts extends Component
{
    public array $priorityData = [];
    public array $transactionTrends = [];
    public int $daysRange = 7;

    public function mount(): void
    {
        $this->loadChartData();
    }

    public function updatedDaysRange(): void
    {
        $this->loadChartData();
        $this->dispatch('update-charts', [
            'priority' => $this->priorityData,
            'trends' => $this->transactionTrends,
        ]);
    }

    public function loadChartData(): void
    {
        // 1. توزيع التنبيهات حسب الأولوية (من جدول alerts)
        $priorities = ['critical', 'high', 'medium', 'low'];
        $counts = Alert::select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();

        $this->priorityData = [
            'labels' => ['حرج (Critical)', 'مرتفع (High)', 'متوسط (Medium)', 'منخفض (Low)'],
            'series' => array_map(fn ($p) => (int) ($counts[$p] ?? 0), $priorities),
        ];

        // 2. اتجاه المعاملات خلال الفترة المحددة (من جدول transactions)
        $startDate = Carbon::now()->subDays($this->daysRange - 1)->startOfDay();
        
        $rawTx = Transaction::select(
                DB::raw('DATE(transaction_time) as date'),
                'status',
                DB::raw('count(*) as total')
            )
            ->where('transaction_time', '>=', $startDate)
            ->groupBy('date', 'status')
            ->get();

        $dates = [];
        $approvedSeries = [];
        $flaggedSeries = [];

        for ($i = 0; $i < $this->daysRange; $i++) {
            $currentDate = $startDate->copy()->addDays($i)->format('Y-m-d');
            $dates[] = Carbon::parse($currentDate)->format('M d');

            $dayRecords = $rawTx->where('date', $currentDate);
            
            $approvedCount = $dayRecords->whereIn('status', ['completed', 'approved'])->sum('total');
            $flaggedCount = $dayRecords->whereIn('status', ['blocked', 'under_review', 'flagged'])->sum('total');

            $approvedSeries[] = (int) $approvedCount;
            $flaggedSeries[] = (int) $flaggedCount;
        }

        $this->transactionTrends = [
            'categories' => $dates,
            'approved' => $approvedSeries,
            'flagged' => $flaggedSeries,
        ];
    }

    public function render()
    {
        return view('livewire.fraud-analytics-charts');
    }
    public function exportExcel(?string $priority = null)
    {
        if (function_exists('activity')) {
            activity()
                ->withProperties(['priority_filter' => $priority ?? 'all'])
                ->log('Exported Fraud Alerts Compliance Excel Report');
        }

        $fileName = 'Fraud_Compliance_Report_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new FraudAlertsExport($priority), $fileName);
    }
}