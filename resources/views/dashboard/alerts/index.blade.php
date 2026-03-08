@extends('dashboard.layouts.app')
@section('content')

@if (session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<div class="card">
  <div class="card-header">
    <h3 class="card-title text-danger">
      <i class="fas fa-bell"></i> Active Alerts
    </h3>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table m-0">
        <thead>
          <tr>
            <th>Product</th>
            <th>Type</th>
            <th>Message</th>
            <th>Created At</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($alerts as $alert)
          <tr>
            <td>{{ $alert->product?->product_name ?? '-' }}</td>
            <td>
            @if($alert->type === 'LOW_STOCK')
                <span class="badge badge-danger">LOW STOCK</span>
            @elseif($alert->type === 'FORECAST')
                <span class="badge badge-warning">FORECAST</span>
            @else
                <span class="badge badge-info">{{ $alert->type }}</span>
            @endif
            </td>
            <td>{{ $alert->message }}</td>
            <td>{{ $alert->created_at->format('Y-m-d H:i') }}</td>
            <td>
              <form method="POST" action="{{ route('alerts.resolve', $alert->id) }}">
                @csrf
                @method('PUT')
                <button class="btn btn-sm btn-success">
                  Resolve
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center text-success">
              No active alerts 🎉
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection
