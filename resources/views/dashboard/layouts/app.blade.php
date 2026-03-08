<!DOCTYPE html>
<html lang="en">
<head>
@include('dashboard.includes.style')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="{{ asset('assets/img/favicon.png')}}" alt="AdminLTELogo" height="70" width="70">
  </div>

  <!-- Navbar -->
@include('dashboard.includes.navbar')
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
@include('dashboard.includes.sidebar')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <div class="container-fluid">
   @yield('content')
    <!-- /.content -->
  </div>
  </div>
  <!-- /.content-wrapper -->
 @include('dashboard.includes.footerbar')

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->
@include('dashboard.includes.script')

</body>
</html>
