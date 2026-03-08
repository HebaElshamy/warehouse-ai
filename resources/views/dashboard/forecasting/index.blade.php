@extends('dashboard.layouts.app')
@section('content')

@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <b>Please fix the following:</b>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
@endif

<!-- ================= Forecast Results ================= -->
<div class="card">
  <div class="card-header border-transparent d-flex align-items-center justify-content-between">
    <h3 class="card-title mb-0">AI Demand Forecasting</h3>

    @if($latestPrediction)
      <div class="small text-muted">
        Model: <b>{{ $latestPrediction->model_version }}</b> |
        Generated at: <b>{{ optional($latestPrediction->generated_at)->format('Y-m-d H:i') }}</b>
      </div>
    @endif
  </div>

  <div class="card-body">

    <!-- Filter + Generate Row -->
    <div class="row align-items-end">
      <!-- VIEW FORM -->
      <div class="col-md-10">
        <form method="GET" action="{{ route('forecasting.index') }}" class="row align-items-end">
          <div class="col-md-7">
            <div class="form-group mb-2">
              <label class="mb-1">Select Product</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-box"></i></span>
                </div>
                <select name="product_id" class="form-control">
                  <option value="">-- Select --</option>
                  @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                      {{ $p->product_name }} ({{ $p->barcode_prefix }})
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group mb-2">
              <label class="mb-1">Horizon (days)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                </div>
                <input type="number" name="horizon_days" class="form-control" min="1" max="30" value="{{ $horizon }}">
              </div>
            </div>
          </div>

          <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-block mb-2">
              <i class="fas fa-search"></i> View
            </button>
          </div>
        </form>
      </div>

      <!-- GENERATE BUTTON -->
      <div class="col-md-2">
        @if(request('product_id'))
          <form method="POST" action="{{ route('forecasting.generate') }}" class="mb-2">
            @csrf
            <input type="hidden" name="product_id" value="{{ request('product_id') }}">
            <input type="hidden" name="horizon_days" value="{{ $horizon }}">

            <button class="btn btn-success btn-block">
              <i class="fas fa-sync-alt"></i> Update
            </button>
          </form>
        @else
          <button class="btn btn-success btn-block mb-2" disabled title="Select a product first">
            <i class="fas fa-sync-alt"></i> Update
          </button>
        @endif
      </div>
    </div>

    @if(!$selectedProduct)
      <hr>
      <div class="alert alert-info mb-0">
        Select a product to view its latest AI forecast.
      </div>
    @else
      <hr>

      @php
        $stock = (int) ($selectedProduct->inventoryCurrent?->current_stock ?? 0);
        $predTotal = (float) ($latestPrediction?->predicted_demand ?? 0);
        $suggested = (int) ($latestPrediction?->suggested_order_qty ?? 0);

        $badgeClass = 'badge-secondary';
        $badgeText  = 'No forecast yet';

        if ($latestPrediction) {
          if ($suggested <= 0) {
            $badgeClass = 'badge-success';
            $badgeText  = 'Stock sufficient';
          } else {
            $badgeClass = 'badge-danger';
            $badgeText  = 'Order suggested';
          }
        }
      @endphp

      <div class="row">
        <!-- Predicted Demand -->
        <div class="col-md-4">
          <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Predicted Demand (next {{ $horizon }} days)</span>
              <span class="info-box-number">
                {{ $latestPrediction ? number_format($predTotal, 2) : '—' }}
              </span>
              @if(!$latestPrediction)
                <span class="text-muted small">No forecast yet. Click <b>Update</b>.</span>
              @endif
            </div>
          </div>
        </div>

        <!-- Suggested Order -->
        <div class="col-md-4">
          <div class="info-box">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-shopping-cart"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Suggested Order Qty</span>
              <span class="info-box-number">
                {{ $latestPrediction ? $suggested : '—' }}
              </span>
              <span class="badge {{ $badgeClass }} mt-1">{{ $badgeText }}</span>

              @if($latestPrediction)
                <div class="text-muted small mt-1">
                  {{-- Based on forecast vs available stock --}}
                </div>
              @endif
            </div>
          </div>
        </div>

        <!-- Current Stock -->
        <div class="col-md-4">
          <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-box"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Current Stock</span>
              <span class="info-box-number">{{ $stock }}</span>
              {{-- <span class="text-muted small">Live inventory level</span> --}}
            </div>
          </div>
        </div>
      </div>

      <div class="text-muted small">
        Click <b>Update</b> to generate the latest forecast using the AI model.
      </div>

    @endif
  </div>

  <div class="card-footer">
    AI-Based Demand Forecasting (Random Forest)
  </div>
</div>

<!-- ================= Sales History Table ================= -->
@if($selectedProduct)
<div class="card">
  <div class="card-header border-transparent">
    <h3 class="card-title">Sales History (Daily) - Last 60 days</h3>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table m-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Sales Volume</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sales as $row)
            <tr>
              <td>{{ \Carbon\Carbon::parse($row->sale_date)->format('Y-m-d') }}</td>
              <td>{{ $row->sales_volume }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="2" class="text-center">No sales data for this product</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

@endsection
