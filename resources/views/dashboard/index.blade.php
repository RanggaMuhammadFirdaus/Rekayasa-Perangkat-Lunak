@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card shadow">
            <div class="card-body">
                <h6 class="fs-6 fw-light text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Banyaknya Data Jabatan">Data Jabatan</h6>
                <h4 class="fw-bold text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $positionCount }}"> {{ $positionCount }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow">
            <div class="card-body">
                <h6 class="fs-6 fw-light text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Banyaknya Data Anggota">Data Anggota</h6>
                <h4 class="fw-bold text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $userCount }}">{{ $userCount }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Enable Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

<!-- Chart section -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body">
                <h6 class="fs-6 fw-light">Kehadiran Anggota</h6>
                <canvas id="employeeChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Script to render the chart -->
<script>
    const ctx = document.getElementById('employeeChart').getContext('2d');
    const diligenceData = @json($diligenceData);

    const labels = diligenceData.map(item => item.name ?? 'Unknown');
    const data = diligenceData.map(item => item.count);
    
    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Kehadiran Anggota',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection
