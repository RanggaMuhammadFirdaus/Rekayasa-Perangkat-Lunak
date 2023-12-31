<?php

namespace App\Http\Livewire;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Guest;
use App\Models\Presence;
use Livewire\Component;

class PresenceForm extends Component
{
    public Attendance $attendance;

    public $data;
    public $name;
    public $phone;

    public function mount(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }

    // NOTED: setiap method send presence agar lebih aman seharusnya menggunakan if statement seperti diviewnya

    public function sendEnterPresence()
    {
        $attendance = $this->attendance;
        $attendanceLimit = Attendance::where('id', $attendance->id)
            ->value('limit');

        $attendanceCount = Presence::where('attendance_id', $attendance->id)->count();

        // Check if the attendanceCount is less than or equal to the attendanceLimit
        if ($attendanceCount >= $attendanceLimit) {
            return $this->dispatchBrowserEvent('showToast', ['error' => true, 'message' => "Kehadiran atas nama '" . auth()->user()->name . "' gagal dikirim."]);
        } else {
            // If the presence doesn't exist and attendance limit is not exceeded, create it
            if ($attendanceLimit > 0) {
                if ($this->attendance->data->is_start && !$this->attendance->data->is_using_qrcode) { // sama (harus) dengan view
                    Presence::create([
                        "user_id" => auth()->user()->id,
                        "attendance_id" => $this->attendance->id,
                        "presence_date" => now()->toDateString(),
                        "presence_enter_time" => now()->toTimeString(),
                    ]);

                    // untuk refresh if statement
                    $this->data['is_has_enter_today'] = true;

                    return $this->dispatchBrowserEvent('showToast', ['success' => true, 'message' => "Kehadiran atas nama '" . auth()->user()->name . "' berhasil dikirim."]);
                }
            }
        }
    }



    public function render()
    {
        return view('livewire.presence-form');
    }
}
