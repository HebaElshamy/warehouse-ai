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
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<!-- ================= Products Table ================= -->
<div class="card">
  <div class="card-header border-transparent">
    <h3 class="card-title">Products</h3>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table m-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Prefix</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Supplier</th>
            <th>Price</th>
            <th>Location</th>
            <th>Reorder Level</th>
            <th>Reorder Qty</th>
            <th>Status</th>
            {{-- <th>Created At</th> --}}
            <th width="120">Actions</th>
          </tr>
        </thead>

        <tbody>
        @forelse($products as $product)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $product->barcode_prefix }}</td>
            <td>{{ $product->product_name }}</td>
            <td>{{ $product->category?->name ?? '-' }}</td>
            <td>{{ $product->supplier?->name ?? '-' }}</td>
            <td>{{ $product->unit_price ?? '-' }}</td>
            <td>{{ $product->warehouse_location ?? '-' }}</td>
            <td>{{ $product->reorder_level }}</td>
            <td>{{ $product->reorder_quantity }}</td>

            <td>
                @if($product->is_active === 0)
                    <span class="badge bg-secondary">Inactive</span>
                @elseif($product->is_active === 1)
                     <span class="badge bg-success">Active</span>
                

                @endif
            </td>

            {{-- <td>{{ $product->created_at->format('Y-m-d') }}</td> --}}

            <td>
              <div class="d-inline-flex align-items-center" style="gap:6px;">

                <!-- Edit -->
                <button
                  type="button"
                  class="btn btn-sm btn-success"
                  title="Edit"
                  onclick="setEditProduct(
                    {{ $product->id }},
                    @js($product->barcode_prefix),
                    @js($product->product_name),
                    @js($product->unit),
                    {{ $product->category_id ?? 'null' }},
                    {{ $product->supplier_id ?? 'null' }},
                    @js($product->unit_price),
                    @js($product->expiration_date),
                    @js($product->warehouse_location),
                    {{ (int)$product->reorder_level }},
                    {{ (int)$product->reorder_quantity }},
                    {{ $product->is_active ? 1 : 0 }}
                  )"
                >
                  <i class="fas fa-pen"></i>
                </button>

                <!-- Delete -->
                <form action="{{ route('products.destroy', $product->id) }}"
                      method="POST"
                      onsubmit="return confirm('Delete this product?')"
                      class="m-0 p-0">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>

              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="12" class="text-center">No products found</td>
          </tr>
        @endforelse
        </tbody>

      </table>
    </div>
  </div>
</div>


<!-- ================= Add / Edit Product ================= -->
<div class="card collapsed-card" id="productFormCard">
  <div class="card-header">
    <h3 class="card-title" id="productFormTitle">Add Product</h3>

    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse">
        <i class="fas fa-plus"></i>
      </button>
    </div>
  </div>

  <div class="card-body">
    <form id="productForm" action="{{ route('products.store') }}" method="POST">
      @csrf
      <input type="hidden" name="_method" id="productFormMethod" value="POST">

      <div class="form-group">
        <label>Barcode Prefix</label>
        <input type="number"
               name="barcode_prefix"
               id="pPrefix"
               class="form-control"
               min="100"
               max="999"
               required>
      </div>

      <div class="form-group">
        <label>Product Name</label>
        <input type="text"
               name="product_name"
               id="pName"
               class="form-control"
               required>
      </div>

      <div class="form-group">
        <label>Category</label>
        <select name="category_id" id="pCategory" class="form-control">
          <option value="">-- None --</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label>Supplier</label>
        <select name="supplier_id" id="pSupplier" class="form-control">
          <option value="">-- None --</option>
          @foreach($suppliers as $sup)
            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label>Unit</label>
        <input type="text" name="unit" id="pUnit" class="form-control">
      </div>

      <div class="form-group">
        <label>Unit Price</label>
        <input type="number" step="0.01" name="unit_price" id="pPrice" class="form-control">
      </div>

      <div class="form-group">
        <label>Expiration Date</label>
        <input type="date" name="expiration_date" id="pExpiry" class="form-control">
      </div>

      <div class="form-group">
        <label>Warehouse Location</label>
        <input type="text" name="warehouse_location" id="pLocation" class="form-control">
      </div>

      <div class="form-group">
        <label>Reorder Level</label>
        <input type="number" name="reorder_level" id="pReorderLevel" class="form-control" min="0" required>
      </div>

      <div class="form-group">
        <label>Reorder Quantity</label>
        <input type="number" name="reorder_quantity" id="pReorderQty" class="form-control" min="0" required>
      </div>

      <div class="form-check mb-3">
        <input type="checkbox" name="is_active" id="pActive" class="form-check-input" value="1" checked>
        <label class="form-check-label">Active</label>
      </div>

      <button type="submit" class="btn btn-primary" id="productSaveBtn">
        <i class="fas fa-plus"></i> Save
      </button>

      <button type="button" class="btn btn-secondary" onclick="resetProductForm()">
        Cancel
      </button>

    </form>
  </div>
</div>
<!-- /.card -->

@endsection


@push('scripts')
<script>
function setEditProduct(
  id,
  prefix,
  name,
  unit,
  categoryId,
  supplierId,
  price,
  expiry,
  location,
  reorderLevel,
  reorderQty,
  isActive
) {
  // عنوان الفورم
  document.getElementById('productFormTitle').innerText = 'Edit Product';

  // route + method
  const form = document.getElementById('productForm');
  form.action = '/products/' + id;
  document.getElementById('productFormMethod').value = 'PUT';

  // تعبئة القيم
  document.getElementById('pPrefix').value = prefix ?? '';
  document.getElementById('pName').value = name ?? '';
  document.getElementById('pUnit').value = unit ?? '';

  document.getElementById('pCategory').value =
    (categoryId === null || categoryId === 'null') ? '' : categoryId;

  document.getElementById('pSupplier').value =
    (supplierId === null || supplierId === 'null') ? '' : supplierId;

  document.getElementById('pPrice').value = price ?? '';
  document.getElementById('pExpiry').value = expiry ?? '';
  document.getElementById('pLocation').value = location ?? '';

  document.getElementById('pReorderLevel').value = reorderLevel ?? 0;
  document.getElementById('pReorderQty').value = reorderQty ?? 0;

  document.getElementById('pActive').checked = (isActive == 1);

  // زرار Update
  const saveBtn = document.getElementById('productSaveBtn');
  saveBtn.className = 'btn btn-success';
  saveBtn.innerHTML = '<i class="fas fa-check"></i> Update';

  // افتح الكارد لو مقفول
  const card = document.getElementById('productFormCard');
  if (card.classList.contains('collapsed-card')) {
    card.querySelector('[data-card-widget="collapse"]').click();
  }

  // اسكرول للفورم
  card.scrollIntoView({ behavior: 'smooth' });
}

function resetProductForm() {
  document.getElementById('productFormTitle').innerText = 'Add Product';

  const form = document.getElementById('productForm');
  form.action = "{{ route('products.store') }}";
  document.getElementById('productFormMethod').value = 'POST';

  form.reset();

  const saveBtn = document.getElementById('productSaveBtn');
  saveBtn.className = 'btn btn-primary';
  saveBtn.innerHTML = '<i class="fas fa-plus"></i> Save';
}
</script>
@endpush
