<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login', [
            "title" => "Masuk"
        ]);
    }

    public function authenticate(LoginRequest $request)
{
    $remember = $request->boolean('remember');
    $credentials = $request->only(['email', 'password']);

    // Attempt to authenticate by email
    if (Auth::attempt($credentials, $remember)) {
        request()->session()->regenerate();
        $data = [
            "success" => true,
            "redirect_to" => auth()->user()->isUser() ? route('home.index') : route('dashboard.index'),
            "message" => "Login berhasil, silahkan tunggu!"
        ];
        return response()->json($data);
    }

    // If authentication by email fails, attempt to authenticate by name
    $nameCredentials = [
        'name' => $credentials['email'], // Assuming the input field is named 'email'
        'password' => $credentials['password'],
    ];

    if (Auth::attempt($nameCredentials, $remember)) {
        request()->session()->regenerate();
        $data = [
            "success" => true,
            "redirect_to" => auth()->user()->isUser() ? route('home.index') : route('dashboard.index'),
            "message" => "Login berhasil, silahkan tunggu!"
        ];
        return response()->json($data);
    }

    // If both authentication attempts fail, return an error response
    $data = [
        "success" => false,
        "message" => "Login gagal, silahkan coba lagi!"
    ];
    return response()->json($data)->setStatusCode(400);
}


    public function logout()
    {
        auth()->logout();

        request()->session()->regenerate();
        request()->session()->regenerateToken();

        return redirect()->route('auth.login')->with('success', 'Anda berhasil keluar.');
    }
}
