@extends('layouts.auth')

@push('style')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endpush

@section('content')

<div class="w-100">

    <main class="form-signin w-100 m-auto">
        <form method="POST" action="{{ route('auth.login') }}" id="login-form">
            <h1 class="h3 mb-3 fw-normal">Login</h1>

            <div class="form-floating">
                <input type="text" class="form-control" id="floatingInputEmail" name="email"
                    placeholder="name@example.com">
                <label for="floatingInputEmail">Username atau Email</label>
            </div>
            <div class="form-floating">
                <input type="password" class="form-control" id="floatingPassword" name="password"
                    placeholder="Password">
                <label for="floatingPassword">Password</label>
            </div>            

            <button class="w-100 btn btn-primary" type="submit" id="login-form-button">Masuk</button>
            <p class="mt-5 mb-3 text-muted">&copy; Rangga M Firdaus</p>
        </form>
    </main>

</div>
@endsection

@push('script')
<script type="module" src="{{ asset('js/auth/login.js') }}"></script>
@endpush