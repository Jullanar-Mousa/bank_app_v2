<?php

namespace App\Exports;

use App\Models\Alert;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FraudAlertsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected ?string $priorityFilter;

    public function __construct(?string $priorityFilter = null)
    {
        $this->priorityFilter = $priorityFilter;
    }

    public function collection(): Enumerable
    {
        return Alert::with(['transaction.account', 'riskScore'])
            ->when($this->priorityFilter, fn ($query) => $query->where('priority', $this->priorityFilter))
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Alert ID',
            'Priority',
            'Status',
            'Risk Score',
            'Account Number',
            'Amount',
            'City',
            'IP Address',
            'Channel',
            'Created At',
        ];
    }

    public function map($alert): array
    {
        $tx = $alert->transaction;
        $account = $tx?->account;

        return [
            '#' . $alert->id,
            strtoupper($alert->priority ?? 'N/A'),
            strtoupper($alert->status ?? 'NEW'),
            $alert->riskScore?->score ?? $alert->risk_score ?? 'N/A',
            $account?->account_number ?? 'N/A',
            $tx ? number_format($tx->amount, 2) : '0.00',
            $tx?->city ?? 'N/A',
            $tx?->ip_address ?? 'N/A',
            strtoupper($tx?->channel ?? 'N/A'),
            $alert->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B'],
                ],
            ],
        ];
    }
}