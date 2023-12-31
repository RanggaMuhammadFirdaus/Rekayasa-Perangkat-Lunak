<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\User;
use App\Models\Presence;
use App\Models\Attendance;
use App\Models\DiligenceData;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
{
    $diligenceData = DiligenceData::select('user_id', 'count')
        ->get();

    // Fetch user names based on user_id
    $userNames = User::whereIn('id', $diligenceData->pluck('user_id'))->pluck('name', 'id');

    // Map user names to the diligenceData collection
    $diligenceData = $diligenceData->map(function ($item) use ($userNames) {
        $item->name = $userNames[$item->user_id] ?? null;
        return $item;
    });

    return view('dashboard.index', [
        "title" => "Dashboard",
        "positionCount" => Position::count(),
        "userCount" => User::count(),
        "diligenceData" => $diligenceData,
    ]);
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

public function deleteAttendance($attendanceId)
{
    // Fetch the user_id associated with this attendance record
    $userId = Attendance::findOrFail($attendanceId)->user_id;

    // Delete the attendance record
    Attendance::destroy($attendanceId);

    // Update the diligence data after deletion
    $this->updateDiligenceData($userId);
}
}
