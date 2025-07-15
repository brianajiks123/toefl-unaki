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
                                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </header>
                <!-- END: .main-heading -->

                <div class="main-content">
                    <!-- Button trigger modal -->
                    <div class="row mb-3">
                        <div class="col-lg">
                            <i class="link-icon addUserBtn" data-feather="plus" data-bs-toggle="modal"
                                data-bs-target="#addUserModal" role="button" aria-label="Add User"></i>
                            <a
                                href="{{ Auth::user()->is_admin == 1 ? route('exportAdminUsers') : (Auth::user()->is_admin == 2 ? route('exportLecturerUsers') : '#') }}">
                                <i class="link-icon mx-2 exportUserBtn" data-feather="download"
                                    aria-label="Download All User"></i>
                            </a>
                            <i class="link-icon mx-2 reloadButton" data-feather="refresh-ccw" role="button"
                                aria-label="Reload Page"></i>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Users Details</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="users_table" class="table table-hover table-bordered text-center">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Role</th>
                                            <th scope="col">Account</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users as $u => $user)
                                            <tr>
                                                <td>{{ $u + 1 }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    @switch($user->is_admin)
                                                        @case(1)
                                                            Admin
                                                        @break

                                                        @case(2)
                                                            Lecturer
                                                        @break

                                                        @default
                                                            Student
                                                    @endswitch
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge {{ $user->email_verified_at ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $user->email_verified_at ? 'Verified' : 'Not Verified' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div
                                                        class="d-flex justify-content-center align-items-center act_btn_user">
                                                        @empty($user->email_verified_at)
                                                            <div class="col-lg-5">
                                                                <i class="link-icon reverifyBtn" data-feather="send"
                                                                    data-id="{{ $user->id }}" role="button"
                                                                    aria-label="Re-verify User"></i>
                                                            </div>
                                                        @endempty
                                                        <div class="col-lg-5">
                                                            <i class="link-icon editUserBtn" data-feather="edit-2"
                                                                data-id="{{ $user->id }}"
                                                                data-name="{{ $user->name }}"
                                                                data-email="{{ $user->email }}" data-bs-toggle="modal"
                                                                data-bs-target="#editUsersModal" role="button"
                                                                aria-label="Edit User"></i>
                                                        </div>
                                                        <div class="col-lg-5">
                                                            <i class="link-icon deleteUserBtn" data-feather="trash"
                                                                data-id="{{ $user->id }}"
                                                                data-name="{{ $user->name }}"
                                                                data-email="{{ $user->email }}" data-bs-toggle="modal"
                                                                data-bs-target="#deleteUsersModal" role="button"
                                                                aria-label="Delete User"></i>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6">Users not found!</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Add User Modal -->
                        <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fs-5" id="addUserModalLabel">Add User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form id="addUser">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="row mb-2">
                                                <div class="col">
                                                    <input type="text" name="name" id="name"
                                                        class="form-control form-control-lg fs-6" placeholder="full name"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col">
                                                    <input type="email" name="email" id="email"
                                                        class="form-control form-control-lg fs-6" placeholder="email"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <select name="user_role" class="form-select fs-6" required>
                                                        <option value="" selected disabled>-- select role --</option>
                                                        @php
                                                            $roles = [];
                                                            if (Auth::user()->is_admin == 1) {
                                                                $roles = [
                                                                    1 => 'admin',
                                                                    2 => 'lecturer',
                                                                    0 => 'student',
                                                                ];
                                                            } elseif (Auth::user()->is_admin == 2) {
                                                                $roles = [2 => 'lecturer', 0 => 'student'];
                                                            }
                                                        @endphp
                                                        @foreach ($roles as $value => $label)
                                                            <option value="{{ $value }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success">
                                                Add <span id="spinner_add" class="spinner-border spinner-border-sm d-none"
                                                    role="status" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Edit User Modal -->
                        <div class="modal fade" id="editUsersModal" tabindex="-1" aria-labelledby="editUsersModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fs-5" id="editUsersModalLabel">Edit User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form id="editUser">
                                        @csrf
                                        <div class="modal-body">
                                            <input type="hidden" name="id" id="edit_userid" required>
                                            <div class="row mb-2">
                                                <div class="col">
                                                    <input type="text" name="name" id="edit_username"
                                                        class="form-control form-control-lg fs-6" placeholder="full name"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <input type="email" name="email" id="edit_useremail"
                                                        class="form-control form-control-lg fs-6" placeholder="user email"
                                                        required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary updateUserBtn">Update <span
                                                    id="spinner_edit" class="spinner-border spinner-border-sm d-none"
                                                    role="status"aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Delete User Modal -->
                        <div class="modal fade" id="deleteUsersModal" tabindex="-1" aria-labelledby="deleteUsersModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fs-5" id="deleteUsersModalLabel">Delete User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form id="deleteUser">
                                        @csrf
                                        <div class="modal-body">
                                            <input type="hidden" name="id" id="delete_userid" required>
                                            <p>Are you sure to delete this User?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-danger">Delete <span id="spinner_delete"
                                                    class="spinner-border spinner-border-sm d-none"
                                                    role="status"aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let users = @json($users);
            let is_admin = @json(Auth::user()->is_admin);

            let addUserUrl = is_admin == 1 ? @json(route('addAdminUser')) : is_admin == 2 ? @json(route('addLecturerUser')) :
                '#';

            let resendVerifyUrl = is_admin == 1 ? @json(route('resendAdminVerify')) : is_admin == 2 ?
                @json(route('resendLecturerVerify')) :
                '#';

            let editUserUrl = is_admin == 1 ? @json(route('editAdminUser')) : is_admin == 2 ? @json(route('editLecturerUser')) :
                '#';

            let deleteUserUrl = is_admin == 1 ? @json(route('deleteAdminUser')) : is_admin == 2 ? @json(route('deleteLecturerUser')) :
                '#';
        </script>
        @vite('resources/js/admin_lecturer/users.js')
    @endsection
