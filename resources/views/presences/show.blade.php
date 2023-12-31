@extends('layouts.app')

@push('style')
    @powerGridStyles
@endpush

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5 class="card-title">{{ $attendance->title }}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">{{ $attendance->description }}</h6>
                    <div class="d-flex align-items-center gap-2">
                        @include('partials.attendance-badges')
                        <a href="{{ route('presences.permissions', $attendance->id) }}" class="btn btn-info"
                            style="transition: opacity 0.5s ease-in-out;">Anggota Izin</a>
                        <a href="{{ route('presences.not-present', $attendance->id) }}" class="btn btn-danger"
                            style="transition: opacity 0.5s ease-in-out;">Belum
                            Absen</a>
                        <a href="{{ route('presences.guest', $attendance->id) }}" class="btn btn-secondary">Tamu</a>
                        @if ($attendance->code)
                            <a href="{{ route('presences.qrcode', ['code' => $attendance->code]) }}"
                                class="btn btn-success">QRCode</a>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <small class="fw-bold text-muted d-block">Jarak Jam Masuk</small>
                                <span>{{ $attendance->start_time }} - {{ $attendance->batas_start_time }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <small class="fw-bold text-muted d-block">Jabatan / Posisi</small>
                                <div>
                                    @foreach ($attendance->positions as $position)
                                        <span class="btn btn-success d-inline-block me-1">{{ $position->name }}</span>
                                    @endforeach
                                    @if ($presence->where('guest', 1)->first())
                                        <span class="btn btn-success d-inline-block me-1">Tamu</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <small class="fw-bold text-muted d-block">Jarak Jam Masuk</small>
                                <span>{{ $presence->count() }} /
                                    {{ $attendance->limit }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <h3>Anggota : {{ $presence->where('guest', 0)->count() }}</h3>
        <livewire:presence-table attendanceId="{{ $attendance->id }}" />
    </div>
    <hr>
    <div>
        <h3>Tamu : {{ $presence->where('guest', 1)->count() }}</h3>
        <livewire:presence-table-guest attendanceId="{{ $attendance->id }}" />
    </div>
@endsection

@push('script')
    <script src="{{ asset('jquery/jquery-3.6.0.min.js') }}"></script>
    @powerGridScripts
@endpush
