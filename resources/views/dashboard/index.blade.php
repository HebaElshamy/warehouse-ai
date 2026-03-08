@extends('dashboard.layouts.app')
@section('content')

<div class="row">

  {{-- Total Products --}}
  <div class="col-lg-3 col-6">
   <div class="small-box bg-info">
  <div class="inner">
    <h3>{{ $totalProducts }}</h3>
    <p>Total Products</p>
    <small class="d-block invisible">placeholder</small>
  </div>
  <div class="icon"><i class="fas fa-box"></i></div>
  <a href="{{ route('products.index') }}" class="small-box-footer">
    More info <i class="fas fa-arrow-circle-right"></i>
  </a>
</div>
  </div>

  {{-- Low Stock Items --}}
  <div class="col-lg-3 col-6">
   <div class="small-box bg-danger">
  <div class="inner">
    <h3>{{ $lowStockCount }}</h3>
    <p>Low Stock Items</p>
    <small class="d-block invisible">placeholder</small>
  </div>
  <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
  <a href="{{ route('inventory.index') }}" class="small-box-footer">
    More info <i class="fas fa-arrow-circle-right"></i>
  </a>
</div>
  </div>

  {{-- Total Sales (Qty + Amount) --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>{{ $totalSalesQty }}</h3>
        <p>Total Sales (Last 30 days)</p>
        <small class="d-block">Amount: {{ number_format($totalSalesAmount, 2) }}</small>
      </div>
      <div class="icon"><i class="fas fa-chart-line"></i></div>
      <a href="{{ route('reports.index') }}" class="small-box-footer">
        More info <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  {{-- Forecast next week --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3>{{ $forecastSuggestedTotal }}</h3>
        <p class="mb-1">Next Week Forecast</p>

        <small class="d-block text-truncate">
          Predicted demand: {{ number_format($forecastDemandTotal, 2) }}
        </small>

        <small class="d-block text-truncate">
          @if($latestGeneratedAt)
            Batch: {{ \Carbon\Carbon::parse($latestGeneratedAt)->format('Y-m-d H:i') }}
          @else
            Batch: -
          @endif
        </small>
      </div>

      <div class="icon"><i class="fas fa-bolt"></i></div>
      <a href="{{ route('forecasting.index') }}" class="small-box-footer">
        More info <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

</div>

{{-- Sales Chart --}}
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Sales Trend (Last 30 days)</h3>
  </div>

  <div class="card-body">
    <div style="height: 320px;">
      <canvas id="salesChart"></canvas>
    </div>

    <script>
      window.__salesTrend = @json($salesTrend);
    </script>
  </div>
</div>

{{-- Alerts Box --}}
<div class="card mt-3">
  <div class="card-header border-transparent">
    <h3 class="card-title text-danger">
      Unresolved Alerts <i class="fas fa-exclamation-triangle"></i>
    </h3>
  </div>

  <div class="card-body">
    @if($alerts->isEmpty())
      <p class="text-success mb-0">No active alerts 🎉</p>
    @else
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Time</th>
              <th>Type</th>
              <th>Product</th>
              <th>Message</th>
            </tr>
          </thead>
          <tbody>
            @foreach($alerts as $a)
              <tr>
                <td>{{ $a->created_at->format('Y-m-d H:i') }}</td>
                <td><span class="badge badge-danger">{{ $a->type }}</span></td>
                <td>{{ $a->product?->product_name ?? '-' }}</td>
                <td>{{ $a->message }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
  const rows = window.__salesTrend || [];
  const labels = rows.map(r => r.date);
  const qtyData = rows.map(r => r.qty);
  const amountData = rows.map(r => r.amount);

  const canvas = document.getElementById('salesChart');
  if (!canvas) return;

  new Chart(canvas, {
    type: 'line',
    data: {
      labels,
      datasets: [
        { label: 'Qty', data: qtyData, tension: 0.25 },
        { label: 'Amount', data: amountData, tension: 0.25, yAxisID: 'yAmount' },
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      scales: {
        y: { beginAtZero: true, title: { display: true, text: 'Qty' } },
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
