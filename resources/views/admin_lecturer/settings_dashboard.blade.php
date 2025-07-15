@extends('layout/admin_layout')

@section('space-work')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg">
                <!-- BEGIN .main-heading -->
                <header class="main-heading">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xl-8 col-lg-8 col-md-6 col-sm-6">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item home">
                                        <a href="/">
                                            <i class="link-icon" data-feather="home"></i>
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">Settings</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </header>
                <!-- END: .main-heading -->

                <div class="main-content">
                    {{-- Profile Overview --}}
                    <div class="card mb-3">
                        <div class="card-header">Overview</div>
                        <div class="card-body">
                            <form id="updateProfile">
                                @csrf
                                <div class="row g-0">
                                    <input type="hidden" name="id" value="{{ $curr_user->id }}">
                                    <div class="row mb-3 profile">
                                        <div class="col-5 col-md-3 d-flex align-items-center label_container">
                                            <div class="p-2 text-center">Full Name</div>
                                        </div>
                                        <div class="col-7 col-md-9">
                                            <div class="p-2">
                                                <input type="text" class="form-control" name="name"
                                                    value="{{ $curr_user->name ?? '' }}"
                                                    {{ $curr_user ? 'disabled' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 profile">
                                        <div class="col-5 col-md-3 d-flex align-items-center label_container">
                                            <div class="p-2 text-center">Email</div>
                                        </div>
                                        <div class="col-7 col-md-9">
                                            <div class="p-2">
                                                <div>
                                                    {{ $curr_user->email ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 profile">
                                        <div class="col-5 col-md-3 d-flex align-items-center label_container">
                                            <div class="p-2 text-center">Address</div>
                                        </div>
                                        <div class="col-7 col-md-9">
                                            <div class="p-2">
                                                <textarea class="form-control" name="address" rows="4" {{ $curr_user ? 'disabled' : '' }}>{{ $curr_user->profileDetail->address ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 profile">
                                        <div class="col-5 col-md-3 d-flex align-items-center label_container">
                                            <div class="p-2 text-center">Phone</div>
                                        </div>
                                        <div class="col-7 col-md-9">
                                            <div class="p-2">
                                                <input type="number" class="form-control" name="phone"
                                                    value="{{ $curr_user->profileDetail->phone ?? '' ? '0' . $curr_user->profileDetail->phone : '' }}"
                                                    {{ $curr_user ? 'disabled' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row ms-2 profile">
                                        <div class="col-5 label_container">
                                            <!-- Edit and cancel buttons logic -->
                                            @if ($curr_user)
                                                <!-- If admin or lecturer exists, show edit button and hide other buttons -->
                                                <button class="btn btn-warning editProfileBtn">
                                                    <i class="fa-solid fa-pencil"></i>
                                                </button>
                                                <button type="submit" class="btn btn-primary d-none updateProfileBtn">
                                                    <i class="fa-solid fa-check"></i>
                                                    <span id="spinner_updateProfile"
                                                        class="spinner-border spinner-border-sm d-none" role="status"
                                                        aria-hidden="true"></span>
                                                </button>
                                                <button class="btn btn-danger d-none cancelUpdateProfileBtn">
                                                    <i class="fa-solid fa-x"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Change Password --}}
                    <div class="card">
                        <div class="card-header">Change Password</div>
                        <div class="card-body">
                            <form id="updatePassword" method="post">
                                @csrf
                                <div class="row gy-3 gy-xxl-4">
                                    <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                                    <div class="col-12">
                                        <label for="currentPassword" class="form-label">Current Password</label>
                                        <input type="password" name="currentPassword" id="currentPassword"
                                            class="form-control" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="newPassword" class="form-label">New Password</label>
                                        <input type="password" name="newPassword" id="newPassword" class="form-control"
                                            required>
                                        <small>* Min. 8 characters long and include: uppercase, lowercase, symbol, and
                                            number.</small>
                                    </div>
                                    <div class="col-12">
                                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                                        <input type="password" name="confirmPassword" id="confirmPassword"
                                            class="form-control" required>
                                        <small>* Min. 8 characters long and include: uppercase, lowercase, symbol, and
                                            number.</small>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Save Changes <span
                                                id="spinner_updatePassword"
                                                class="spinner-border spinner-border-sm d-none" role="status"
                                                aria-hidden="true"></span></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const is_admin = @json($curr_user->is_admin);
        const updateProfileUrl = is_admin == 1 ? @json(route('updateAdminProfile')) : is_admin == 2 ?
            @json(route('updateLecturerProfile')) :
            '#';
        const updatePasswordUrl = is_admin == 1 ? @json(route('updateAdminPassword')) : is_admin == 2 ?
            @json(route('updateLecturerPassword')) : '#';
    </script>
    @vite('resources/js/admin_lecturer/settings.js')
@endsection
