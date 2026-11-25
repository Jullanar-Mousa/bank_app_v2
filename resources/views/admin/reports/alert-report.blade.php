<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fraud Alert Investigation Report #{{ $alert['id'] }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #dc2626;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #111827;
        }
        .meta {
            color: #6b7280;
            font-size: 11px;
            margin-top: 4px;
        }
        .badge-high {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .section-title {
            background-color: #f3f4f6;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 13px;
            margin-top: 18px;
            margin-bottom: 10px;
            border-left: 4px solid #2563eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f9fafb;
            width: 35%;
            color: #374151;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>AML & Fraud Investigation Report</h1>
        <div class="meta">
            Report Reference: <strong>#ALR-{{ $alert['id'] }}</strong> |
            Generated At: {{ $generated_at }} |
            Investigator: {{ $investigator }}
        </div>
    </div>

    <div class="section-title">1. Alert Overview</div>
    <table>
        <tr>
            <th>Alert ID</th>
            <td>#{{ $alert['id'] }}</td>
        </tr>
        <tr>
            <th>Risk Score</th>
            <td><span class="badge-high">{{ $alert['risk_score'] }} / 100</span></td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ strtoupper($alert['status']) }}</td>
        </tr>
        <tr>
            <th>Detection Rule / Trigger</th>
            <td>{{ $alert['trigger_reason'] }}</td>
        </tr>
        <tr>
            <th>Detected Timestamp</th>
            <td>{{ $alert['created_at'] }}</td>
        </tr>
    </table>

    <div class="section-title">2. Flagged Transaction Details</div>
    <table>
        <tr>
            <th>Transaction Reference</th>
            <td>{{ $transaction['reference'] }}</td>
        </tr>
        <tr>
            <th>Amount & Currency</th>
            <td><strong>{{ $transaction['amount'] }} {{ $transaction['currency'] }}</strong></td>
        </tr>
        <tr>
            <th>Transaction Type</th>
            <td>{{ $transaction['type'] }}</td>
        </tr>
        <tr>
            <th>Source Account</th>
            <td>{{ $account['account_number'] }} ({{ $account['customer_name'] }})</td>
        </tr>
        <tr>
            <th>Customer Email</th>
            <td>{{ $account['customer_email'] }}</td>
        </tr>
    </table>

    <div class="section-title">3. Compliance & Analyst Notes</div>
    <p>
        This report was automatically compiled from the core banking fraud monitoring ledger.
        All transaction links and risk metrics have been verified for regulatory audit and Suspicious Activity Report (SAR) filing.
    </p>

    <div class="footer">
        Confidential - Internal Banking Compliance & Fraud Investigation Unit
    </div>

</body>
</html>