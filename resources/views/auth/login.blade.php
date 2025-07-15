@extends('auth/layout/header')

@section('space-work')
    <!----------------------- Main Container -------------------------->

    <div class="container d-flex justify-content-center align-items-center min-vh-100">

        <!----------------------- Login Container -------------------------->

        <div class="row border rounded-5 p-3 shadow box-area">

            <!--------------------------- Left Box ----------------------------->

            <div
                class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box bg-white py-3">
                <img src="{{ asset('student/images/logo_unaki_yellow.png') }}" width="80%">
                <h1 class="text-center my-3">TOEFL <span>UNAKI</span></h1>
                <p class="text-wrap text-center mt-2">Test your English skills on this platform.</p>
                <ul class="mt-3 mb-0">
                    <li>Listening Comprehension</li>
                    <li>Structure & Written Expression</li>
                    <li>Reading Comprehension</li>
                </ul>
            </div>

            <!-------------------- ------ Right Box ---------------------------->

            <div class="col-md-6 right-box">
                <div class="row align-items-center text-white">
                    <form id="goLogin">
                        @csrf
                        <div class="header-text mb-4 text-center">
                            <p>Please Login to do the TOEFL Test.</p>
                        </div>
                        @if (session('success'))
                            <div class="alert alert-success fade show" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif
                        <div class="input-group mb-3">
                            <input type="email" name="email" id="email"
                                class="form-control form-control-lg bg-light fs-6" placeholder="Email address"
                                value="{{ old('email') }}" required>
                        </div>
                        <div class="input-group mb-1">
                            <input type="password" name="password" id="password"
                                class="form-control form-control-lg bg-light fs-6" placeholder="Password" required>
                        </div>
                        <div class="input-group my-3 d-flex justify-content-between">
                            <div class="form-check"></div>
                            <div class="forgot">
                                <small><a href="{{ route('forgetPasswdLoad') }}" class="text-white">Forgot
                                        Password?</a></small>
                            </div>
                        </div>
                        <div class="input-group my-4">
                            <button type="submit" class="btn btn-lg login_btn w-100 fs-6">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    let userLogin = @json(route('userLogin'));
</script>

@vite(['resources/js/auth/template.js'])
