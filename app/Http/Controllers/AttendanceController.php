<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('attendances.index', [
            "title" => "Presensi"
        ]);
    }

    public function create()
    {
        return view('attendances.create', [
            "title" => "Tambah Data Presensi"
        ]);
    }

    public function edit()
    {
        return view('attendances.edit', [
            "title" => "Edit Data Presensi",
            "attendance" => Attendance::findOrFail(request('id'))
        ]);
    }
}
