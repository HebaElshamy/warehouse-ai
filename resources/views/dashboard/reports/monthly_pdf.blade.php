<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Monthly Report</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1, h2 { margin: 0 0 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 6px; }
    th { background: #f5f5f5; }
  </style>
</head>
<body>
  <h1>Monthly Report - {{ $month }}</h1>
  <p>From {{ $start->toDateString() }} to {{ $end->toDateString() }}</p>

  <h2>Totals</h2>
  <table>
    <tr><th>Total Qty</th><td>{{ (int)$monthlyTotals->qty }}</td></tr>
    <tr><th>Total Amount</th><td>{{ number_format((float)$monthlyTotals->amount, 2) }}</td></tr>
  </table>

  <h2>Top Selling</h2>
  <table>
    <thead><tr><th>Product</th><th>Qty</th><th>Amount</th></tr></thead>
    <tbody>
      @foreach($topSelling as $r)
        <tr>
          <td>{{ $r->product?->product_name ?? 'N/A' }}</td>
          <td>{{ (int)$r->qty }}</td>
          <td>{{ number_format((float)$r->amount, 2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <h2>Low Moving</h2>
  <table>
    <thead><tr><th>Product</th><th>Qty</th><th>Amount</th></tr></thead>
    <tbody>
      @foreach($lowMoving as $r)
        <tr>
          <td>{{ $r->product?->product_name ?? 'N/A' }}</td>
          <td>{{ (int)$r->qty }}</td>
          <td>{{ number_format((float)$r->amount, 2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
