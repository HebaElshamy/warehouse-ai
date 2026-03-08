@extends('dashboard.layouts.app')
@section('content')

<!-- ================= Categories Table ================= -->
<div class="card">
  <div class="card-header border-transparent">
    <h3 class="card-title">Categories</h3>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table m-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Status</th>
            <th>Created At</th>
            <th width="120">Actions</th>
          </tr>
        </thead>

        <tbody>
          @forelse($categories as $category)
          <tr>
            <td>{{ $loop->iteration }}</td>

            <td>{{ $category->name }}</td>

            <td>
              @if($category->is_active)
                <span class="badge badge-success">Active</span>
              @else
                <span class="badge badge-danger">Inactive</span>
              @endif
            </td>

            <td>{{ $category->created_at->format('Y-m-d') }}</td>

            <td>
              <div class="d-inline-flex align-items-center" style="gap:6px;">

                <!-- Edit -->
                <button
                  type="button"
                  class="btn btn-sm btn-success"
                  title="Edit"
                  onclick="setEditCategory(
                    {{ $category->id }},
                    @js($category->name),
                    @js($category->description),
                    {{ $category->is_active ? 1 : 0 }}
                  )"
                >
                  <i class="fas fa-pen"></i>
                </button>

                <!-- Delete -->
                <form action="{{ route('categories.destroy', $category->id) }}"
                      method="POST"
                      onsubmit="return confirm('Delete this category?')"
                      class="m-0 p-0">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>

              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center">No categories found</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card-footer">
    Manage your warehouse categories
  </div>
</div>
<!-- /.card -->



<!-- ================= Add / Edit Category ================= -->
<div class="card collapsed-card">
  <div class="card-header">
    <h3 class="card-title" id="formTitle">Add Category</h3>

    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse">
        <i class="fas fa-plus"></i>
      </button>
    </div>
  </div>

  <div class="card-body">
    <form id="categoryForm" action="{{ route('categories.store') }}" method="POST">
      @csrf

      <input type="hidden" name="_method" id="formMethod" value="POST">

      <div class="form-group">
        <label>Category Name</label>
        <input type="text"
               name="name"
               id="catName"
               class="form-control"
               required>
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea name="description"
                  id="catDesc"
                  class="form-control"></textarea>
      </div>

      <div class="form-group">
        <div class="form-check">
          <input type="checkbox"
                 name="is_active"
                 id="catActive"
                 value="1"
                 class="form-check-input"
                 checked>
          <label class="form-check-label">
            Active
          </label>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" id="saveBtn">
        <i class="fas fa-plus"></i> Save
      </button>

      <button type="button"
              class="btn btn-secondary"
              onclick="resetCategoryForm()">
        Cancel
      </button>

    </form>
  </div>

  <div class="card-footer">
    Add new warehouse category
  </div>
</div>
<!-- /.card -->

@endsection



@push('scripts')
<script>
  console.log('Categories JS Loaded');
</script>
<script>

function setEditCategory(id, name, description, isActive) {

  document.getElementById('formTitle').innerText = 'Edit Category';

  const form = document.getElementById('categoryForm');
  form.action = '/categories/' + id;

  document.getElementById('formMethod').value = 'PUT';

  document.getElementById('catName').value = name ?? '';
  document.getElementById('catDesc').value = description ?? '';
  document.getElementById('catActive').checked = (isActive == 1);

  // Change button style to green for update
  const saveBtn = document.getElementById('saveBtn');
  saveBtn.className = 'btn btn-success';
  saveBtn.innerHTML = '<i class="fas fa-check"></i> Update';

  form.scrollIntoView({ behavior: 'smooth' });
}


function resetCategoryForm() {

  document.getElementById('formTitle').innerText = 'Add Category';

  const form = document.getElementById('categoryForm');
  form.action = "{{ route('categories.store') }}";

  document.getElementById('formMethod').value = 'POST';

  document.getElementById('catName').value = '';
  document.getElementById('catDesc').value = '';
  document.getElementById('catActive').checked = true;

  const saveBtn = document.getElementById('saveBtn');
  saveBtn.className = 'btn btn-primary';
  saveBtn.innerHTML = '<i class="fas fa-plus"></i> Save';
}

</script>
@endpush
