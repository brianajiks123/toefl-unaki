@extends('auth/layout/header')

@section('space-work')
    <!----------------------- Main Container -------------------------->

    <div class="container d-flex justify-content-center align-items-center min-vh-100">

        <!----------------------- Reset Container -------------------------->

        <div class="row border rounded-5 shadow box-area bg_forget">

            <!-------------------- ------ Right Box ---------------------------->

            <div class="col-md right-box">
                <div class="row align-items-center text-white">
                    <form action="{{ route('reset_post', ['token' => $data['token']]) }}" method="post">
                        @csrf
                        <div class="header-text mb-4 text-center">
                            <p>Update your account password in the form below.</p>
                        </div>
                        @if (Session::has('success'))
                            <div class="alert alert-success" role="alert">
                                {{ Session::get('success') }}
                            </div>
                        @elseif (Session::has('error'))
                            <div class="alert alert-danger" role="alert">
                                {!! nl2br(e(Session::get('error'))) !!}
                            </div>
                        @endif
                        <div class="input-group mb-3">
                            <input type="email" name="email" id="email"
                                class="form-control form-control-lg bg-light fs-6" placeholder="Email address" value="{{ old('email') }}" required>
                        </div>
                        <small id="email-error" class="text-white">
                            @error('email')
                                {{ $message }}
                            @enderror
                        </small>
                        <div class="input-group mb-3">
                            <input type="password" name="password" id="password"
                                class="form-control form-control-lg bg-light fs-6" placeholder="Password" required>
                        </div>
                        <div class="input-group mb-3">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="form-control form-control-lg bg-light fs-6" placeholder="Confirm Password" required>
                        </div>
                        <small id="password-error" class="text-white">
                            @error('password')
                                {{ $message }}
                            @enderror
                        </small>
                        <div class="input-group">
                            <button type="submit" class="btn btn-outline-light btn-lg reset_btn w-100 fs-6">Update
                                Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
