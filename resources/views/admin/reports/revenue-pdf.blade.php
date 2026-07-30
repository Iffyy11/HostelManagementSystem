<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Revenue Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        h1 { color: #003B71; }
    </style>
</head>
<body>
    <h1>USIU Hostel — Revenue Report</h1>
    <p>Period: {{ $from }} to {{ $to }}</p>
    <p><strong>Total: KES {{ number_format($total) }}</strong></p>
    <table>
        <thead>
            <tr>
                <th>Receipt</th>
                <th>Student</th>
                <th>Amount</th>
                <th>Phone</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->mpesa_receipt_number }}</td>
                    <td>{{ $payment->booking->student->user->name ?? 'N/A' }}</td>
                    <td>{{ number_format($payment->amount) }}</td>
                    <td>{{ $payment->phone_number }}</td>
                    <td>{{ optional($payment->transaction_date)->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
