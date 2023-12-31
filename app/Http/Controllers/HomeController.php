<?php

namespace App\Http\Controllers;

use App\Models\Attendance;

use App\Models\Permission;
use App\Models\Presence;
use App\Models\DiligenceData;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $attendances = Attendance::query()
            // ->with('positions')
            ->forCurrentUser(auth()->user()->position_id)
            ->get()
            ->sortByDesc('data.is_end')
            ->sortByDesc('data.is_start');

        return view('home.index', [
            "title" => "Beranda",
            "attendances" => $attendances
        ]);
    }

    public function show(Attendance $attendance)
{
    $user = auth()->user();

    $presences = Presence::query()
        ->where('attendance_id', $attendance->id)
        ->where('user_id', $user->id)
        ->get();

    $isHasEnterToday = $presences
        ->where('presence_date', now()->toDateString())
        ->isNotEmpty();

    $isTherePermission = Permission::query()
        ->where('permission_date', now()->toDateString())
        ->where('attendance_id', $attendance->id)
        ->where('user_id', $user->id)
        ->first();

    $data = [
        'is_has_enter_today' => $isHasEnterToday, // sudah absen masuk            
        'is_there_permission' => (bool) $isTherePermission,
        'is_permission_accepted' => $isTherePermission?->is_accepted ?? false
    ];

    $history = Presence::query()
        ->where('user_id', $user->id)
        ->where('attendance_id', $attendance->id)
        ->get();

    $priodDate = CarbonPeriod::create($attendance->created_at->toDateString(), now()->toDateString())
        ->toArray();

    foreach ($priodDate as $i => $date) { // get only stringdate
        $priodDate[$i] = $date->toDateString();
    }

    $priodDate = array_slice(array_reverse($priodDate), 0, 30);

    // If the user has entered today, update or create the diligence_data entry
    if ($isHasEnterToday) {
        $this->updateDiligenceData($user->id);
    }

    return view('home.show', [
        "title" => "Informasi Presensi Kehadiran",
        "attendance" => $attendance,
        "data" => $data,            
        'history' => $history,
        'priodDate' => $priodDate
    ]);
}





    public function permission(Attendance $attendance)
    {
        return view('home.permission', [
            "title" => "Form Permintaan Izin",
            "attendance" => $attendance
        ]);
    }

    // for qrcode
    public function sendEnterPresenceUsingQRCode()
{
    $code = request('code');
    $attendance = Attendance::query()->where('code', $code)->first();

    if ($attendance && $attendance->data->is_start && $attendance->data->is_using_qrcode) {
        $user = auth()->user();

        Presence::create([
            "user_id" => $user->id,
            "attendance_id" => $attendance->id,
            "presence_date" => now()->toDateString(),
            "presence_enter_time" => now()->toTimeString(),
        ]);

        // Update or create the diligence_data entry based on user_id
        $this->updateDiligenceData($user->id);

        return response()->json([
            "success" => true,
            "message" => "Kehadiran atas nama '" . $user->name . "' berhasil dikirim."
        ]);
    }

    return response()->json([
        "success" => false,
        "message" => "Terjadi masalah pada saat melakukan presensi."
    ], 400);
}

private function updateDiligenceData($userId)
{
    $diligenceData = DiligenceData::where('user_id', $userId)->first();

    if ($diligenceData) {
        $diligenceData->increment('count');
    } else {
        DiligenceData::create([
            'user_id' => $userId,
            'count' => 1,
        ]);
    }
}

    
    
}
