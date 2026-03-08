<aside class="main-sidebar sidebar-dark-primary elevation-4">

  <!-- Brand Logo -->
  <a href="" class="brand-link text-center">
          <img src="{{ asset('assets/img/favicon.png')}}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">

      <span class="brand-text font-weight-light">
          {{ config('app.name', 'Warehouse AI') }}
      </span>
  </a>

  <div class="sidebar">

      <!-- User Panel -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex text-center">
         <div class="image">
          <img src="{{ asset('assets/img/avatar2.png')}}" class="img-circle elevation-2" alt="User Image">
        </div>
          <div class="info">
              <a href="#" class="d-block">
                  {{ auth()->user()->name ?? 'Administrator' }}
              </a>
          </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

             <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
                </a>
                </li>

            <li class="nav-item">
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-box"></i>
                    <p>Products</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tags"></i>
                    <p>Categories</p>
                </a>
            </li>

             <li class="nav-item">
                <a href="{{ route('suppliers.index') }}"
                    class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-truck"></i>
                    <p>Suppliers</p>
                </a>
            </li>

              <li class="nav-item">
                  <a href="{{ route('inventory.index') }}" class="nav-link">
                      <i class="nav-icon fas fa-warehouse"></i>
                      <p>Inventory</p>
                  </a>
              </li>

              <li class="nav-item">
                  <a href="{{ route('forecasting.index') }}" class="nav-link">
                      <i class="nav-icon fas fa-chart-line"></i>
                      <p>Forecasting</p>
                  </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('alerts.index') }}" class="nav-link">
                    <i class="nav-icon fas fa-bell"></i>
                    <p>Alerts</p>
                </a>
               </li>

              <li class="nav-item">
                  <a href="{{ route('storage.index') }}" class="nav-link">
                      <i class="nav-icon fas fa-thermometer-half"></i>
                      <p>Storage Environment</p>
                  </a>
              </li>

              <li class="nav-item">
                  <a href="{{ route('reports.index') }}" class="nav-link">
                      <i class="nav-icon fas fa-file-alt"></i>
                      <p>Reports</p>
                  </a>
              </li>

          </ul>
      </nav>

  </div>
</aside>
