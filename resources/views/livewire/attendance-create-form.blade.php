<div>
    <form wire:submit.prevent="save" method="post" novalidate>
        @include('partials.alerts')
        <div class="w-100">
            <div class="mb-3">
                <x-form-label id="title" label='Judul Absensi' />
                <x-form-input id="title" name="title" wire:model.defer="attendance.title" />
                <x-form-error key="attendance.title" />
            </div>
            <div class="mb-3">
                <x-form-label id="description" label='Keterangan' />
                <textarea id="description" name="description" class="form-control"
                    wire:model.defer="attendance.description"></textarea>
                <x-form-error key="attendance.description" />
            </div>
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <x-form-label id="start_time" label='Waktu Absen Masuk' />
                        <x-form-input type="text" maxlength="5" id="start_time" name="start_time"
                            wire:model.defer="attendance.start_time" placeholder="07:00" />
                        <x-form-error key="attendance.start_time" />
                    </div>
                    <div class="col-md-6">
                        <x-form-label id="batas_start_time" label='Batas Waktu Absen Masuk' />
                        <x-form-input type="text" maxlength="5" id="batas_start_time" name="batas_start_time"
                            wire:model.defer="attendance.batas_start_time" />
                        <x-form-error key="attendance.batas_start_time" />
                    </div>
                    </div>
                <small class="text-muted d-block mt-1">Masukan dengan format 24:00.</small>
                    <div class="col-md-6">
                        <x-form-label id="limit" label='Jumlah Peserta' />
                        <x-form-input type="number" id="limit" name="limit"
                            wire:model.defer="attendance.limit" />
                        <x-form-error key="attendance.limit" />
                    </div>                
            </div>
            

            <div class="mb-3">
                <x-form-label id="positions" label='Posisi Anggota' />
                <div class="row ms-1">
                    @foreach ($positions as $position)
                    <div class="form-check col-sm-4">
                        <input class="form-check-input" type="checkbox" value="{{ $position->id }}"
                            wire:model.defer="position_ids.{{ $position->id }}"
                            id="flexCheckPosition{{ $loop->index }}">
                        <label class="form-check-label" for="flexCheckPosition{{ $loop->index }}">
                            {{ $position->name }}
                        </label>
                    </div>
                    @endforeach
                </div>
                <small class="text-muted d-block mt-1">Pilih posisi anggota yang akan menggunakan absensi ini.</small>
                <x-form-error key="position_ids" />
                {{-- tom-select init script ada di create.blade.php attendances --}}
            </div>


            

        </div>

        <div class="d-flex justify-content-between align-items-center mb-5">
            <button class="btn btn-primary">
                Simpan
            </button>
        </div>
    </form>
</div>