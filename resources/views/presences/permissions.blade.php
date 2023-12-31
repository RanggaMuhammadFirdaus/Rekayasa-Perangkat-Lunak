@extends('layouts.app')

@section('buttons')
<div class="btn-toolbar mb-2 mb-md-0">
    <div>
        <a href="{{ route('presences.show', $attendance->id) }}" class="btn btn-sm btn-light">
            <span data-feather="arrow-left-circle" class="align-text-bottom"></span>
            Kembali
        </a>
    </div>
</div>
@endsection

@section('content')
@include('partials.alerts')

<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3 mb-md-0">
                <h5 class="card-title">{{ $attendance->title }}</h5>
                <h6 class="card-subtitle mb-2 text-muted">{{ $attendance->description }}</h6>
                <div class="d-flex align-items-center gap-2">
                    <span href="" class="btn btn-warning">Izin</span>
                </div>
            </div>
            <div class="col-md-6">                
            </div>
        </div>
    </div>
</div>

<div>
    <div class="p-3 rounded bg-light border my-3 d-flex align-items-center justify-content-between">
        <div>Hari : <span class="fw-bold">
                {{ \Carbon\Carbon::parse($date)->dayName }}
                {{ \Carbon\Carbon::parse($date)->isCurrentDay() ? '(Hari ini)' : '' }}
            </span>
        </div>
        <div>Tanggal : <span class="fw-bold">{{ $date }}</span></div>
        <div>Jumlah : <span class="fw-bold">{{ $permissions->count() }}</span></div>
    </div>
    @if (count($permissions) === 0)
    <small class="text-danger fw-bold">Tidak ada data yang bisa ditampilkan, <a href="">Tampilkan data berdasarkan hari
            ini.</a></small>
    @else
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nama Anggota</th>
                    <th scope="col">Kontak</th>
                    <th scope="col">Posisi</th>
                    <th scope="col">Tangani</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permissions as $permission)
                <tr>
                    <th scope="row">{{ $loop->iteration }}</th>
                    <td>{{ $permission->user->name }}</td>
                    <td>
                        <a href="mailto:{{ $permission->user->email }}">{{ $permission->user->email }}</a>
                        <span class="fw-bold"> / </span>
                        <a href="tel:{{ $permission->user->phone }}">{{ $permission->user->phone }}</a>
                    </td>
                    <td>{{ $permission->user->position->name }}</td>
                    @if ($permission->is_accepted)
                    <td>
                        <span class="btn btn-success border-0 ">Sudah Diterima</span>
                        <button class="btn btn-info border-0 permission-detail-modal-triggers"
                            data-permission-id="{{ $permission->id }}" data-bs-toggle="modal"
                            data-bs-target="#permission-detail-modal">Lihat
                            Alasan</button>
                    </td>
                    @else
                    <td>
                        <form action="{{ route('presences.acceptPermission', $attendance->id) }}" method="post">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $permission->user->id }}">
                            <input type="hidden" name="permission_date" value="{{ $permission->permission_date }}">
                            <button class="btn btn-primary border-0 mb-2" type="submit">Terima</button>
                        </form>
                        <button class="btn btn-info border-0 permission-detail-modal-triggers"
                            data-permission-id="{{ $permission->id }}" data-bs-toggle="modal"
                            data-bs-target="#permission-detail-modal">Lihat
                            Alasan</button>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@if (count($permissions) !== 0)
<div class="modal fade" id="permission-detail-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Izin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul>
                    <li>Judul Izin : <span id="permission-title"></span></li>
                    <li>Keterangan Izin : <p id="permission-description"></p>
                    </li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <form action="{{ route('presences.acceptPermission', $attendance->id) }}" method="post">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $permission->user->id }}">
                    <input type="hidden" name="permission_date" value="{{ $permission->permission_date }}">
                    <button class="btn btn-primary border-0" type="submit">Terima</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('script')
<script>
    const permissionUrl = "{{ route('api.permissions.show') }}";
</script>
<script src="{{ asset('js/presences/permissions.js') }}"></script>
@endpush