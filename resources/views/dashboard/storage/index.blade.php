@extends('dashboard.layouts.app')
@section('content')

@php
$hasLatest = !is_null($latest);
$temp = $hasLatest ? $latest->temperature_c : null;
$hum  = $hasLatest ? $latest->humidity_pct : null;
$measured_at = $hasLatest ? $latest->measured_at : null;
$tempOk = $hasLatest ? ($temp >= $minTemp && $temp <= $maxTemp) : true;
$humOk  = $hasLatest ? ($hum >= $minHum && $hum <= $maxHum) : true;
@endphp

{{-- ================= Current Cards ================= --}}
<div class="row mb-3">

  <div class="col-md-4">
    <div class="small-box {{ $tempOk ? 'bg-info' : 'bg-danger' }}">
      <div class="inner">
        <h3>{{ $hasLatest ? number_format($temp,1).'°C' : '--' }}</h3>
        <p>Current Temperature</p>
        <small>Range: {{ $minTemp }} - {{ $maxTemp }} °C</small>
      </div>
      <div class="icon"><i class="fas fa-thermometer-half"></i></div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="small-box {{ $humOk ? 'bg-success' : 'bg-danger' }}">
      <div class="inner">
        <h3>{{ $hasLatest ? number_format($hum,1).'%' : '--' }}</h3>
        <p>Current Humidity</p>
        <small>Range: {{ $minHum }} - {{ $maxHum }} %</small>
      </div>
      <div class="icon"><i class="fas fa-tint"></i></div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="small-box {{ ($tempOk && $humOk) ? 'bg-success' : 'bg-danger' }}">
      <div class="inner">
        <h3>{{ ($tempOk && $humOk) ? 'OK' : 'ALERT' }}</h3>
        <p>Status</p>
        <small>{{ $measured_at->format('Y-m-d H:i') }}</></small>
      </div>
      <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
    </div>
  </div>

</div>

{{-- ================= Alerts ================= --}}
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Today's Alerts</h3>
  </div>
  <div class="card-body">
    @if($alertsToday->isEmpty())
      <span class="text-success">No alerts today 🎉</span>
    @else
      <ul class="mb-0">
        @foreach($alertsToday as $a)
          <li>
            <strong>{{ $a->type }}</strong> —
            {{ $a->message }}
            <small class="text-muted">({{ $a->created_at->format('H:i') }})</small>
          </li>
        @endforeach
      </ul>
    @endif
  </div>
</div>

{{-- ================= Chart ================= --}}
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Today Temperature & Humidity Trend</h3>
  </div>
  <div class="card-body">
    <div style="height:320px;">
      <canvas id="envChart"></canvas>
    </div>

    <script>
      window.envData = @json(
        $todayReadings->map(fn($r)=>[
          'time'=>$r->measured_at->format('H:i'),
          'temp'=>$r->temperature_c,
          'hum'=>$r->humidity_pct
        ])
      );
    </script>
  </div>
</div>

{{-- ================= Table ================= --}}
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Latest Readings</h3>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table m-0">
        <thead>
          <tr>
            <th>Time</th>
            <th>Device</th>
            <th>Temperature</th>
            <th>Humidity</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
        @forelse($readings as $r)

          @php
            $tOk = $r->temperature_c >= $minTemp && $r->temperature_c <= $maxTemp;
            $hOk = $r->humidity_pct >= $minHum && $r->humidity_pct <= $maxHum;
          @endphp

          <tr>
            <td>{{ $r->measured_at->format('Y-m-d H:i') }}</td>
            <td>{{ $r->device_id }}</td>
            <td>{{ $r->temperature_c }} °C</td>
            <td>{{ $r->humidity_pct }} %</td>
            <td>
              @if($tOk && $hOk)
                <span class="badge badge-success">OK</span>
              @else
                <span class="badge badge-danger">ALERT</span>
              @endif
            </td>
          </tr>

        @empty
          <tr>
            <td colspan="5" class="text-center">No readings yet</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  const rows = window.envData || [];
  const labels = rows.map(r=>r.time);
  const temp = rows.map(r=>r.temp);
  const hum  = rows.map(r=>r.hum);

  const ctx = document.getElementById('envChart');
  if(!ctx) return;

  new Chart(ctx,{
    type:'line',
    data:{
      labels,
      datasets:[
        {label:'Temperature (°C)',data:temp,tension:0.3},
        {label:'Humidity (%)',data:hum,tension:0.3,yAxisID:'y2'}
      ]
    },
    options:{
      responsive:true,
      maintainAspectRatio:false,
      scales:{
        y:{title:{display:true,text:'Temperature'}},
        y2:{
          position:'right',
          grid:{drawOnChartArea:false},
          title:{display:true,text:'Humidity'}
        }
      }
    }
  });
})();
</script>

@endsection
