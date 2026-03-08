@extends('dashboard.layouts.app')
@section('content')

@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show">
    <b>Please fix the following:</b>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
@endif


<!-- ================= Scan Barcode ================= -->
<div class="card collapsed-card">
  <div class="card-header">
    <h3 class="card-title">Scan Barcode</h3>

    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse">
        <i class="fas fa-plus"></i>
      </button>
    </div>
  </div>

  <div class="card-body">
    <form method="POST" action="{{ route('inventory.scan') }}">
      @csrf

      <div class="form-group">
        <label>Barcode</label>
        <input type="text"
               name="barcode"
               class="form-control"
               placeholder="Scan or type barcode"
               required>
      </div>

      <button class="btn btn-primary">
        <i class="fas fa-barcode"></i> Scan
      </button>
    </form>
  </div>

  <div class="card-footer">
    System reads first 3 digits and updates stock automatically
  </div>
</div>
<!-- /.card -->


<!-- ================= Inventory Table ================= -->
<div class="card">
  <div class="card-header border-transparent">
    <h3 class="card-title">Inventory</h3>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table m-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Prefix</th>
            <th>Product</th>
            <th>Category</th>
            <th>Current Stock</th>
            <th>Reorder Level</th>
            <th>Status</th>
            <th width="120">Action</th>
          </tr>
        </thead>

        <tbody>
        @forelse($products as $product)

  @php
    $currentStock = $product->inventoryCurrent?->current_stock ?? 0;
    $status = ($currentStock <= (int)$product->reorder_level) ? 'LOW' : 'OK';
  @endphp

  <tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $product->barcode_prefix }}</td>
    <td>{{ $product->product_name }}</td>
    <td>{{ $product->category?->name ?? '-' }}</td>
    <td>{{ $currentStock }}</td>
    <td>{{ $product->reorder_level }}</td>

    <td>
      @if($status === 'LOW')
        <span class="badge badge-danger">Low Stock</span>
      @else
        <span class="badge badge-success">OK</span>
      @endif
    </td>

    <td>
      <div class="d-inline-flex align-items-center" style="gap:6px;">
        <button
          type="button"
          class="btn btn-sm btn-success"
          onclick="setEditInventory(
            {{ $product->id }},
            @js($product->product_name),
            {{ (int)$currentStock }}
          )"
        >
          <i class="fas fa-pen"></i>
        </button>
      </div>
    </td>
  </tr>

@empty
  <tr>
    <td colspan="8" class="text-center">No products found</td>
  </tr>
@endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card-footer">
    Update current stock manually or via scan
  </div>
</div>
<!-- /.card -->


<!-- ================= Update Stock Form ================= -->
<div class="card collapsed-card">
  <div class="card-header">
    <h3 class="card-title" id="invFormTitle">Update Stock</h3>

    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse">
        <i class="fas fa-plus"></i>
      </button>
    </div>
  </div>

  <div class="card-body">
    <form id="inventoryForm" action="" method="POST">
      @csrf
      @method('PUT')

      <div class="form-group">
        <label>Product</label>
        <input type="text" id="invProductName" class="form-control" disabled>
      </div>

      <div class="form-group">
        <label>Current Stock (Cartons)</label>
        <input type="number"
               name="current_stock"
               id="invCurrentStock"
               class="form-control"
               min="0"
               required>
      </div>

      <button type="submit" class="btn btn-success">
        <i class="fas fa-check"></i> Update
      </button>

      <button type="button" class="btn btn-secondary" onclick="resetInventoryForm()">
        Cancel
      </button>
    </form>
  </div>

  <div class="card-footer">
    Manual stock update affects status instantly
  </div>
</div>
<!-- /.card -->

@endsection


@push('scripts')
<script>
function setEditInventory(productId, productName, currentStock) {
    const form = document.getElementById('inventoryForm');
    form.action = '/inventory/' + productId;

    document.getElementById('invProductName').value = productName ?? '';
    document.getElementById('invCurrentStock').value = currentStock ?? 0;

    form.scrollIntoView({ behavior: 'smooth' });
}

function resetInventoryForm() {
    document.getElementById('invProductName').value = '';
    document.getElementById('invCurrentStock').value = '';
}
</script>
@endpush
