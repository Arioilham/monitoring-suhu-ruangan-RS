@extends('layouts.main')

@section('title', 'Insiden Darurat - Sistem Monitoring')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3"><i class="fas fa-exclamation-triangle"></i> Insiden Darurat</h1>
        <p class="text-muted">Menampilkan kondisi darurat (>5 menit tidak normal)</p>
    </div>
</div>

<!-- Alert -->
<div class="alert alert-warning">
    <i class="fas fa-info-circle"></i> Tabel ini menampilkan rekaman yang menunjukkan kondisi tidak normal
    berlangsung lebih dari 5 menit berturut-turut
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('monitoring.emergency-incidents') }}" method="get" class="row g-3">
            <!-- Hidden device_id since only 1 room exists -->
            <input type="hidden" name="device_id" value="{{ $selectedDevice }}">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">Tanggal Akhir</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Cari Insiden
                </button>
                <a href="{{ route('monitoring.emergency-incidents') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-redo"></i> Reset Filter
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Insiden Darurat</h6>
                <h2 class="text-danger mb-0">{{ $emergencies->total() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Ruangan Terdampak</h6>
                <h2 class="text-warning mb-0">{{ $emergencies->count() > 0 ? $emergencies->pluck('device.device_name')->unique()->count() : 0 }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Emergency Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-danger">
                <tr>
                    <th>Waktu Kejadian</th>
                    <th>Suhu (°C)</th>
                    <th>Kelembapan (%)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($emergencies as $emergency)
                <tr class="table-danger">
                    <td>
                        {{ $emergency->recorded_at->format('d-m-Y H:i:s') }}<br>
                        <small class="text-muted">{{ $emergency->recorded_at->diffForHumans() }}</small>
                    </td>
                    <td>
                        <span class="{{ $emergency->temperature < 15 || $emergency->temperature > 30 ? 'badge bg-danger' : 'badge bg-success' }}">
                            {{ number_format($emergency->temperature, 1) }}°C
                        </span>
                    </td>
                    <td>
                        <span class="{{ $emergency->humidity < 35 || $emergency->humidity > 60 ? 'badge bg-danger' : 'badge bg-success' }}">
                            {{ number_format($emergency->humidity, 1) }}%
                        </span>
                    </td>
                    <td>
                        @if($emergency->status === 'Panas')
                            <span class="badge bg-danger">Panas</span>
                        @else
                            <span class="badge bg-primary">Dingin</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('monitoring.history', ['device_id' => $emergency->device_id, 'start_date' => $emergency->recorded_at->format('Y-m-d')]) }}" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-history"></i> Riwayat
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <p class="text-muted mb-0">
                            <i class="fas fa-check-circle"></i> Tidak ada insiden darurat
                        </p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $emergencies->render('pagination::bootstrap-4') }}
</div>

@endsection
