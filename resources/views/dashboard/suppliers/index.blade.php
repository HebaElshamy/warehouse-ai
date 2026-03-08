@extends('dashboard.layouts.app')
@section('content')

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Suppliers</h3>
  </div>

  <div class="card-body p-0">
    <table class="table m-0">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Phone</th>
          <th>Email</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($suppliers as $sup)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $sup->name }}</td>
          <td>{{ $sup->phone }}</td>
          <td>{{ $sup->email }}</td>
          <td>
            <form action="{{ route('suppliers.destroy',$sup->id) }}" method="POST" style="display:inline;">
              @csrf
              @method('DELETE')
              <button class="btn btn-danger btn-sm">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="text-center">No suppliers</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="card collapsed-card">
  <div class="card-header">
    <h3 class="card-title">Add Supplier</h3>

    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse">
        <i class="fas fa-plus"></i>
      </button>
    </div>
  </div>

  <div class="card-body">
    <form action="{{ route('suppliers.store') }}" method="POST">
      @csrf

      <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control">
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control">
      </div>

      <div class="form-group">
        <label>Address</label>
        <input type="text" name="address" class="form-control">
      </div>

      <div class="form-check mb-3">
        <input type="checkbox" name="is_active" class="form-check-input" checked>
        <label class="form-check-label">Active</label>
      </div>

      <button class="btn btn-primary">
        <i class="fas fa-plus"></i> Save
      </button>
    </form>
  </div>
</div>

@endsection
