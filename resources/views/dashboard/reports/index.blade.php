@extends('dashboard.layouts.app')
@section('content')

<div class="row mb-3">
  <div class="col-md-6">
    <h3>Reports</h3>
    <p class="text-muted">Monthly analytics and trends</p>
  </div>

  <div class="col-md-6 text-right">
    <form method="GET" action="{{ route('reports.index') }}" class="form-inline justify-content-end">
      <label class="mr-2">Month</label>
      <input type="month" name="month" value="{{ $month }}" class="form-control mr-2">
      <button class="btn btn-primary">Apply</button>
      <a class="btn btn-success ml-2" href="{{ route('reports.monthly_pdf', ['month' => $month]) }}">
        Download PDF
      </a>
    </form>
  </div>
</div>

<div class="row">
  <div class="col-lg-6 col-12">
    <div class="small-box bg-info">
      <div class="inner">
        <h3>{{ (int)$monthlyTotals->qty }}</h3>
        <p>Total Sales (Qty) - {{ $month }}</p>
      </div>
      <div class="icon"><i class="fas fa-chart-line"></i></div>
    </div>
  </div>

  <div class="col-lg-6 col-12">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>{{ number_format((float)$monthlyTotals->amount, 2) }}</h3>
        <p>Total Sales Amount - {{ $month }}</p>
      </div>
      <div class="icon"><i class="fas fa-dollar-sign"></i></div>
    </div>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Daily Sales Trend</h3>
  </div>

  <div class="card-body">

    {{-- Chart --}}
    <div style="height: 320px;">
      <canvas id="salesTrendChart"></canvas>
    </div>

    {{-- Pass data to JS --}}
    <script>
      window.__dailyTrend = @json($dailyTrend);
    </script>

    {{-- Table (optional - keep as details) --}}
    <div class="table-responsive mt-3">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Date</th>
            <th>Qty</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
          @forelse($dailyTrend as $d)
            <tr>
              <td>{{ $d['date'] }}</td>
              <td>{{ $d['qty'] }}</td>
              <td>{{ number_format($d['amount'], 2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted">No sales data for this month</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Top Selling Products</h3></div>
      <div class="card-body p-0">
        <table class="table table-striped">
          <thead>
            <tr><th>Product</th><th>Qty</th><th>Amount</th></tr>
          </thead>
          <tbody>
            @forelse($topSelling as $r)
              <tr>
                <td>{{ $r->product?->product_name ?? 'N/A' }}</td>
                <td>{{ (int)$r->qty }}</td>
                <td>{{ number_format((float)$r->amount, 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Low Moving Products</h3></div>
      <div class="card-body p-0">
        <table class="table table-striped">
          <thead>
            <tr><th>Product</th><th>Qty</th><th>Amount</th></tr>
          </thead>
          <tbody>
            @forelse($lowMoving as $r)
              <tr>
                <td>{{ $r->product?->product_name ?? 'N/A' }}</td>
                <td>{{ (int)$r->qty }}</td>
                <td>{{ number_format((float)$r->amount, 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  (function () {
    const rows = window.__dailyTrend || [];

    const labels = rows.map(r => r.date);
    const qtyData = rows.map(r => r.qty);
    const amountData = rows.map(r => r.amount);

    const canvas = document.getElementById('salesTrendChart');
    if (!canvas) return;

    new Chart(canvas, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Qty',
            data: qtyData,
            tension: 0.25,
          },
          {
            label: 'Amount',
            data: amountData,
            tension: 0.25,
            yAxisID: 'yAmount'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: true },
          tooltip: { enabled: true }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Qty' }
          },
          yAmount: {
            beginAtZero: true,
            position: 'right',
            grid: { drawOnChartArea: false },
            title: { display: true, text: 'Amount' }
          }
        }
      }
    });
  })();
</script>

@endsection
