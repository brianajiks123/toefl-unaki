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
                                    <li class="breadcrumb-item active" aria-current="page">Batches</li>
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
                            <i class="link-icon addBatchBtn" data-feather="plus" data-bs-toggle="modal"
                                data-bs-target="#addBatchModal" role="button" aria-label="View Students Batch"></i>
                            <i class="link-icon mx-2 reloadButton" data-feather="refresh-ccw" role="button"
                                aria-label="Reload Page"></i>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Batches Details</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered text-center">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Student</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($batches as $b => $batch)
                                            <tr>
                                                <td>{{ $b + 1 }}</td>
                                                <td>{{ $batch->name }}</td>
                                                <td>
                                                    <i class="link-icon viewStudentsBatchBtn" data-feather="eye"
                                                        data-id="{{ $batch->id }}" data-bs-toggle="modal"
                                                        data-bs-target="#addBatchStudentModal" role="button"
                                                        aria-label="View Students Batch"></i>
                                                </td>
                                                <td>
                                                    <div
                                                        class="d-flex justify-content-center align-items-center act_btn_batch">
                                                        <div class="col-lg-4 col-md-4 col-sm-4">
                                                            <i class="link-icon deleteBatchBtn" role="button"
                                                                data-id="{{ $batch->id }}" data-bs-toggle="modal"
                                                                data-bs-target="#deleteBatchModal" data-feather="trash"></i>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4">Batch not found!</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

			<div class="mt-3">
                            {{ $batches->links('pagination::bootstrap-5') }}
                        </div>
                    </div>

                    <!-- Add Batch Modal -->
                    <div class="modal fade" id="addBatchModal" tabindex="-1" aria-labelledby="addBatchModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="addBatchModalLabel">Add Batch</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="addBatch">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="row mb-2">
                                            <div class="col">
                                                <input type="text" name="batch_name" id="batch_name"
                                                    class="form-control form-control-lg fs-6" placeholder="batch name"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col">
                                                <select name="user_ids[]" id="user_ids" class="form-select fs-6" multiple>
                                                    <option value="" selected disabled>-- select student --</option>
                                                    @if ($users_verified)
                                                        @foreach ($users_verified as $user)
                                                            <option value="{{ $user->id }}">{{ $user->name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
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

                    <!-- See/Add Batch Student Modal -->
                    <div class="modal fade" id="addBatchStudentModal" tabindex="-1"
                        aria-labelledby="addBatchStudentModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="addBatchStudentModalLabel">See/Add Student</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div id="add_student_batch" class="modal-body">
                                    <form id="addBatchStudent">
                                        @csrf
                                        <div class="row mb-2">
                                            <div class="col user_list_option">

                                            </div>
                                        </div>
                                        <div class="row mb-4">
                                            <div class="col">
                                                <button type="submit" class="btn btn-outline-success">
                                                    Add Student <span id="spinner_add_studbatch"
                                                        class="spinner-border spinner-border-sm d-none" role="status"
                                                        aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">
                                        <div class="table-responsive px-2 user_list_option_table">

                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Batch Modal -->
                    <div class="modal fade" id="deleteBatchModal" tabindex="-1" aria-labelledby="deleteBatchModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="deleteBatchModalLabel">Delete Batch</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="deleteBatch">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="id" id="delete_batchid" required>
                                        <p>Are you sure to delete this Batch?</p>
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
        let batches = @json($batches);
        let is_admin = @json(Auth::user()->is_admin);

        let showStudentsBatchUrl = is_admin == 1 ? @json(route('showAdminStudentsBatch', ['batchId' => ':batchId'])) : is_admin == 2 ?
            @json(route('showLecturerStudentsBatch', ['batchId' => ':batchId'])) : '#';

        let addBatchUrl = is_admin == 1 ? @json(route('addAdminBatch')) : is_admin == 2 ? @json(route('addLecturerBatch')) :
            '#';

        let addUserBatchUrl = is_admin == 1 ? @json(route('addAdminUserBatch')) : is_admin == 2 ?
            @json(route('addLecturerUserBatch')) : '#';

        let deleteStudentBatchUrl = is_admin == 1 ? @json(route('deleteAdminStudentBatch')) : is_admin == 2 ?
            @json(route('deleteLecturerStudentBatch')) : '#';

        let deleteBatchUrl = is_admin == 1 ? @json(route('deleteAdminBatch')) : is_admin == 2 ?
            @json(route('deleteLecturerBatch')) : '#';
    </script>
    @vite('resources/js/admin_lecturer/batches.js')
@endsection
