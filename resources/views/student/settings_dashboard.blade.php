@extends('layout/student_layout')

@section('space-work')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Profile Details</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="{{ route('studentExams') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Home
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Profile Details</li>
        </ul>
    </div>

    <div class="row gy-4">
        <!-- Profile Overview Widget Start -->
        <div class="col-xxl col-sm">
            <div class="card px-24 py-16 shadow-none radius-8 border h-100 bg-gradient-start-3">
                <div class="card-body p-0">
                    <form id="updateProfile">
                        @csrf
                        <div class="row g-0">
                            @php
                                // Retrieve the student data based on the authenticated user ID
                                $student = $students->firstWhere('id', Auth::user()->id);
                            @endphp
                            <!-- Hidden input field for ID, empty if $student does not exist -->
                            <input type="hidden" name="id" value="{{ $student->id ?? '' }}">
                            <div class="row mb-3">
                                <div class="col-5 col-md-3 d-flex align-items-center">
                                    <div class="p-2">Full Name</div>
                                </div>
                                <div class="col-7 col-md-9">
                                    <div class="p-2">
                                        <input type="text" class="form-control" name="name"
                                            value="{{ $student->name ?? '' }}" {{ $student ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-5 col-md-3 d-flex align-items-center">
                                    <div class="p-2 text-center">Email</div>
                                </div>
                                <div class="col-7 col-md-9">
                                    <div class="p-2">
                                        <div>
                                            {{ $student->email ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-5 col-md-3 d-flex align-items-center">
                                    <div class="p-2 text-center">Address</div>
                                </div>
                                <div class="col-7 col-md-9">
                                    <div class="p-2">
                                        <textarea class="form-control" name="address" rows="4" {{ $student ? 'disabled' : '' }}>{{ isset($student->profileDetail) ? $student->profileDetail->address : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-5 col-md-3 d-flex align-items-center">
                                    <div class="p-2 text-center">Phone</div>
                                </div>
                                <div class="col-7 col-md-9">
                                    <div class="p-2">
                                        <input type="number" class="form-control" name="phone"
                                            value="{{ isset($student->profileDetail) && $student->profileDetail->phone ? '0' . $student->profileDetail->phone : '' }}"
                                            {{ $student ? 'disabled' : '' }}>
                                        <small>contoh: 81879xxx</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row ms-2">
                                <div class="col">
                                    <!-- Edit and cancel buttons logic -->
                                    @if ($student)
                                        <!-- If student exists, show edit button and hide other buttons -->
                                        <button class="btn btn-warning editProfileBtn">
                                            <i class="fa-solid fa-pencil"></i>
                                        </button>
                                        <button type="submit" class="btn btn-primary d-none updateProfileBtn">
                                            <i class="fa-solid fa-check"></i>
                                            <span id="spinner_updateProfile" class="spinner-border spinner-border-sm d-none"
                                                role="status" aria-hidden="true"></span>
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
        </div>
        <!-- Profile Overview Widget End -->

        <!-- Change Password Widget Start -->
        <div class="col-xxl col-sm">
            <div class="card px-24 py-16 shadow-none radius-8 border h-100 bg-gradient-start-3">
                <div class="card-body p-0">
                    <form id="updatePasswordStudent" method="post" action="{{ route('updateStudentPassword') }}">
                        @csrf
                        <div class="row gy-3 gy-xxl-4">
                            <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                            <div class="col-12">
                                <label for="currentPassword" class="form-label">Current Password</label>
                                <input type="password" name="currentPassword" id="currentPassword" class="form-control"
                                    required>
                            </div>
                            <div class="col-12">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="password" name="newPassword" id="newPassword" class="form-control" required>
                                <small>* Min. 8 characters long and include: uppercase, lowercase, symbol, and
                                    number.</small>
                            </div>
                            <div class="col-12">
                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                <input type="password" name="confirmPassword" id="confirmPassword" class="form-control"
                                    required>
                                <small>* Min. 8 characters long and include: uppercase, lowercase, symbol, and
                                    number.</small>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Save Changes <span
                                        id="spinner_updatePassword" class="spinner-border spinner-border-sm d-none"
                                        role="status" aria-hidden="true"></span></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Change Password Widget End -->
    </div>

    <script>
        const updateStudentProfileUrl = @json(route('updateStudentProfile'));
        const updateStudentPasswordUrl = @json(route('updateStudentPassword'));
    </script>
    @vite('resources/js/student/settings.js')
@endsection
