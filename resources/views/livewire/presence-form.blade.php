<div>

    
    {{-- jika tidak menggunakan qrcode (button) dan karyawan saat ini tidak menekan tombol izin --}}
    @if (!$attendance->data->is_using_qrcode && !$data['is_there_permission'])

    {{-- jika belum absen dan absen masuk sudah dimulai --}}
    @if ($attendance->data->is_start && !$data['is_has_enter_today'])
    <button id="presentButton" class="btn btn-primary px-3 py-2 btn-sm fw-bold d-block w-100 mb-2" wire:click="sendEnterPresence"
        wire:loading.attr="disabled" wire:target="sendEnterPresence" disabled>Masuk</button>
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
<script>
        function checkEmployeeLocation() {
            navigator.geolocation.getCurrentPosition((position) => {
                const coordinatedLocation = { latitude: -6.966403552369666, longitude: 107.5924623654144 };
                const currentLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };

                const distance = calculateDistance(currentLocation, coordinatedLocation);
                const ACCEPTABLE_DISTANCE =0.05; // Set the acceptable distance to 0.5 kilometers (500 meters)

                const presentButton = document.querySelector('#presentButton');
                presentButton.disabled = distance > ACCEPTABLE_DISTANCE; // Enable or disable based on the location check
            });
        }
    
        function calculateDistance(loc1, loc2) {
    const earthRadius = 6371; // Earth's radius in kilometers

    const { latitude: lat1, longitude: lon1 } = loc1;
    const { latitude: lat2, longitude: lon2 } = loc2;

    const dLat = toRadians(lat2 - lat1);
    const dLon = toRadians(lon2 - lon1);

    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    const distance = earthRadius * c; // Distance in kilometers

    return distance;
}

function toRadians(degrees) {
    return degrees * (Math.PI / 180);
}
    
        window.addEventListener('load', checkEmployeeLocation);
    </script>    
</div>