<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Permission;
use App\Models\Presence;
use App\Models\User;
use App\Models\DiligenceData;
use App\Models\Guest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PresenceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::all()->sortByDesc('data.is_end')->sortByDesc('data.is_start');

        return view('presences.index', [
            "title" => "Daftar Presensi Dengan Kehadiran",
            "attendances" => $attendances
        ]);
    }

    public function show(Attendance $attendance)
    {
        $attendance->load(['positions', 'presences']);
        $presence = Presence::where('attendance_id', $attendance->id)->get();

        // dd($qrcode);
        return view('presences.show', [
            "title" => "Data Detail Kehadiran",
            "attendance" => $attendance,
            "presence" => $presence,
        ]);
    }

    public function showQrcode()
    {
        $code = request('code');
        $qrcode = $this->getQrCode($code);

        return view('presences.qrcode', [
            "title" => "Hasilkan Presensi QRCode",
            "qrcode" => $qrcode,
            "code" => $code
        ]);
    }

    public function downloadQrCodePDF()
    {
        $code = request('code');
        $qrcode = $this->getQrCode($code);

        $html = '<img src="' . $qrcode . '" />';
        return Pdf::loadHTML($html)->setWarnings(false)->download('qrcode.pdf');
    }

    public function getQrCode(?string $code): string
    {
        if (!Attendance::query()->where('code', $code)->first())
            throw new NotFoundHttpException(message: "Tidak ditemukan presensi dengan code '$code'.");

        return parent::getQrCode($code);
    }

    public function notPresent(Attendance $attendance)
    {
        $byDate = now()->toDateString();
        if (request('display-by-date'))
            $byDate = request('display-by-date');

        $presences = Presence::query()
            ->where('attendance_id', $attendance->id)
            ->where('presence_date', $byDate)
            ->get(['presence_date', 'user_id']);

        // jika semua karyawan tidak hadir
        if ($presences->isEmpty()) {
            $notPresentData[] =
                [
                    "not_presence_date" => $byDate,
                    "users" => User::query()
                        ->with('position')
                        ->onlyEmployees()
                        ->get()
                        ->toArray()
                ];
        } else {
            $notPresentData = $this->getNotPresentEmployees($presences);
        }


        return view('presences.not-present', [
            "title" => "Data Anggota Tidak Hadir",
            "attendance" => $attendance,
            "notPresentData" => $notPresentData
        ]);
    }

    public function permissions(Attendance $attendance)
    {
        $byDate = now()->toDateString();
        if (request('display-by-date'))
            $byDate = request('display-by-date');

        $permissions = Permission::query()
            ->with(['user', 'user.position'])
            ->where('attendance_id', $attendance->id)
            ->where('permission_date', $byDate)
            ->get();

        return view('presences.permissions', [
            "title" => "Data Anggota Izin",
            "attendance" => $attendance,
            "permissions" => $permissions,
            "date" => $byDate
        ]);
    }

    public function presentUser(Request $request, Attendance $attendance)
    {
        // Validate the request data
        $validated = $request->validate([
            'user_id' => 'required|string|numeric',
            'presence_date' => 'required|date',
        ]);

        // Find or fail the User based on the user_id
        $user = User::findOrFail($validated['user_id']);

        // Check if the presence already exists for the user on the specified date
        $presence = Presence::where('attendance_id', $attendance->id)
            ->where('user_id', $user->id)
            ->where('presence_date', $validated['presence_date'])
            ->first();

        // Retrieve the limit from presences table with the same attendance_id
        $attendanceLimit = Attendance::where('id', $attendance->id)
            ->value('limit');

        $attendanceCount = Presence::where('attendance_id', $attendance->id)->count();

        // Check if the attendanceCount is less than or equal to the attendanceLimit
        if ($attendanceCount >= $attendanceLimit) {
            return back()->with('warning', "Gagal menyimpan data hadir atas nama \"$user->name\".");
        } else {
            // If the presence doesn't exist and attendance limit is not exceeded, create it
            if (!$presence && $attendanceLimit > 0) {
                Presence::create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $user->id,
                    'presence_date' => $validated['presence_date'],
                    'presence_enter_time' => now()->toTimeString(),
                ]);

                // Update or create the diligence data
                $this->updateDiligenceData($user->id);

                // Decrease the limit by 1 after successful presence registration
                // Attendance::where('id', $attendance->id)
                //     ->decrement('limit');
            }
            return back()->with('success', "Berhasil menyimpan data hadir atas nama \"$user->name\".");
        }
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

    public function acceptPermission(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'user_id' => 'required|string|numeric',
            "permission_date" => "required|date"
        ]);

        $user = User::findOrFail($validated['user_id']);

        $permission = Permission::query()
            ->where('attendance_id', $attendance->id)
            ->where('user_id', $user->id)
            ->where('permission_date', $validated['permission_date'])
            ->first();

        $presence = Presence::query()
            ->where('attendance_id', $attendance->id)
            ->where('user_id', $user->id)
            ->where('presence_date', $validated['permission_date'])
            ->first();

        // jika data user yang didapatkan dari request user_id, presence_date, sudah absen atau sudah ada ditable presences
        if ($presence || !$user)
            return back()->with('failed', 'Request tidak diterima.');

        Presence::create([
            "attendance_id" => $attendance->id,
            "user_id" => $user->id,
            "presence_date" => $validated['permission_date'],
            "presence_enter_time" => now()->toTimeString(),
            'is_permission' => true
        ]);

        $permission->update([
            'is_accepted' => 1
        ]);

        return back()
            ->with('success', "Berhasil menerima data izin Anggota atas nama \"$user->name\".");
    }

    private function getNotPresentEmployees($presences)
    {
        $uniquePresenceDates = $presences->unique("presence_date")->pluck('presence_date');
        $uniquePresenceDatesAndCompactTheUserIds = $uniquePresenceDates->map(function ($date) use ($presences) {
            return [
                "presence_date" => $date,
                "user_ids" => $presences->where('presence_date', $date)->pluck('user_id')->toArray()
            ];
        });
        $notPresentData = [];
        foreach ($uniquePresenceDatesAndCompactTheUserIds as $presence) {
            $notPresentData[] =
                [
                    "not_presence_date" => $presence['presence_date'],
                    "users" => User::query()
                        ->with('position')
                        ->onlyEmployees()
                        ->whereNotIn('id', $presence['user_ids'])
                        ->get()
                        ->toArray()
                ];
        }
        return $notPresentData;
    }
    public function create(Attendance $attendance)
    {
        return view('presences.guest', [
            'title' => 'Form Tamu',
            'attendance' => $attendance,
        ]);
    }
    public function guest(Request $request, $id)
    {
        // Validate form input fields
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'phone' => 'required|max:13',
        ]);

        // Check validasi
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $attendance = $id;
        $attendanceLimit = Attendance::where('id', $attendance)
            ->value('limit');

        $attendanceCount = Presence::where('attendance_id', $attendance)->count();

        // Check if the attendanceCount is less than or equal to the attendanceLimit
        if ($attendanceCount >= $attendanceLimit) {
            // Memanggil peristiwa browser sebagai respons
            $message = "Kehadiran gagal dikirim.";
            $response = ['success' => true, 'message' => $message];

            // Menggabungkan dengan redirect
            return Response::json($response)->header('X-Inertia-Location', route('presences.guest', ['attendance' => $id]));
            // return $this->dispatchBrowserEvent('showToast', ['error' => true, 'message' => "Kehadiran atas nama '" . auth()->user()->name . "' gagal dikirim."]);
        } else {
            // If the presence doesn't exist and attendance limit is not exceeded, create it
            if ($attendanceLimit > 0) {
                // Create or find a guest record with the provided phone number
                $guest = Guest::Create([
                    'name' => $request->input('name'),
                    'phone' => $request->input('phone')
                ]);

                // Create a presence for the guest in the selected attendance
                Presence::create([
                    'attendance_id' => $id,
                    'user_id' => $guest->id, // Fix: Use $guest->id instead of $this->$guest->id
                    'presence_date' => now()->toDateString(),
                    'presence_enter_time' => now()->toTimeString(),
                    "guest" => 1,
                ]);

                // Memanggil peristiwa browser sebagai respons
                $message = "Kehadiran atas nama '" . $guest->name . "' berhasil dikirim.";
                $response = ['success' => true, 'message' => $message];

                // Menggabungkan dengan redirect dan header Inertia.js
                return Response::json($response)->header('X-Inertia-Location', route('presences.guest', ['attendance' => $id]));
                // return $this->dispatchBrowserEvent('showToast', ['success' => true, 'message' => "Kehadiran atas nama '" . auth()->user()->name . "' berhasil dikirim."]);
            }
        }
    }
}
