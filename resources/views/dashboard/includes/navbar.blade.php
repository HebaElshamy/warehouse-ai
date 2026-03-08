<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <!-- Left -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>

    <li class="nav-item d-none d-sm-inline-block">
      <a href="{{ route('dashboard') }}" class="nav-link">
        {{ config('app.name', 'Warehouse AI') }}
      </a>
    </li>
  </ul>

  <!-- Right -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item d-none d-sm-inline-block">
      <span class="nav-link text-muted">
        {{ auth()->user()->name ?? 'Admin' }}
      </span>
    </li>

    <li class="nav-item">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn nav-link" style="border:0;background:transparent;">
          <i class="fas fa-sign-out-alt"></i> Logout
        </button>
      </form>
    </li>
  </ul>
</nav>
