<div>
    

    {{-- jika tidak menggunakan qrcode (button) dan karyawan saat ini tidak menekan tombol izin --}}
    @if ($attendance->data->is_using_qrcode && !$data['is_there_permission'])

    {{-- jika belum absen dan absen masuk sudah dimulai --}}
    @if ($attendance->data->is_start && !$data['is_has_enter_today'])
    <button class="btn btn-primary px-3 py-2 btn-sm fw-bold d-block w-100 mb-2" data-bs-toggle="modal"
        data-bs-target="#qrcode-scanner-modal" data-is-enter="1">Scan QRCode Masuk</button>
    <a href="{{ route('home.permission', $attendance->id) }}"
        class="btn btn-info px-3 py-2 btn-sm fw-bold d-block w-100">Izin</a>
    @endif

    @if ($data['is_has_enter_today'])
    <div class="alert alert-success">
        <small class="d-block fw-bold text-success">Anda sudah berhasil mengirim absensi masuk.</small>
    </div>
    @endif    
    @endif

    @if($data['is_there_permission'] && !$data['is_permission_accepted'])
    <div class="alert alert-info">
        <small class="fw-bold">Permintaan izin sedang diproses (atau masih belum di terima).</small>
    </div>
    @endif

    @if($data['is_there_permission'] && $data['is_permission_accepted'])
    <div class="alert alert-success">
        <small class="fw-bold">Permintaan izin sudah diterima.</small>
    </div>
    @endif

    <div class="modal fade" id="qrcode-scanner-modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Scan QRCode Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="reader"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script src="{{ asset('html5-qrcode/html5-qrcode.min.js') }}"></script>
<script>
    const enterPresenceUrl = "{{ route('home.sendEnterPresenceUsingQRCode') }}";    
</script>
<script type="module" src="{{ asset('js/home/qrcode.js') }}"></script>
@endpush