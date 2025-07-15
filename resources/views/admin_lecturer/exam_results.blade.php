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
                                    <li class="breadcrumb-item active" aria-current="page">Exam Results</li>
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
                            <i class="link-icon mx-2 reloadButton" data-feather="refresh-ccw" role="button"
                                aria-label="Reload Page"></i>
                        </div>
                    </div>

                    @if ($batches)
                        @foreach ($batches as $batch)
                            <h5 class="my-3">{{ $batch->name }}</h5>

                            <div class="card">
                                <div class="card-header">Exam Results Details</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="user_exam_results_table"
                                            class="table table-hover table-bordered text-center">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Student</th>
                                                    <th scope="col">Score</th>
                                                    <th scope="col">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($batch->users as $s => $student)
                                                    <tr>
                                                        <td>{{ $s + 1 }}</td>
                                                        <td>{{ $student->name }}</td>
                                                        <td>
                                                            <i class="link-icon viewExamResultBtn" data-feather="eye"
                                                                data-batch_id="{{ $batch->id }}"
                                                                data-student_id="{{ $student->id }}" data-bs-toggle="modal"
                                                                data-bs-target="#seeExamResultModal" role="button"
                                                                aria-label="View Students Batch"></i>
                                                        </td>
                                                        <td>
                                                            <div
                                                                class="d-flex justify-content-center align-items-center act_btn_exam_result">
                                                                <div class="col-lg-4 col-md-4 col-sm-4">
                                                                    <i class="link-icon deleteExamResultBtn" role="button"
                                                                        data-batch_id="{{ $batch->id }}"
                                                                        data-student_id="{{ $student->id }}"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#deleteExamResultModal"
                                                                        data-feather="trash"></i>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4">No students found in this batch!</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-3">
                            {{ $batches->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <h5 class="text-center text-danger">Batch not found!</h5>
                    @endif

                    <!-- See Exam Result Modal -->
                    <div class="modal fade" id="seeExamResultModal" tabindex="-1" aria-labelledby="seeExamResultModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="seeExamResultModalLabel">See Exam Result</h5>
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
                                                <div class="table-responsive">
                                                    <table id="exam_result_table"
                                                        class="table table-hover table-bordered text-center">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">Listening</th>
                                                                <th scope="col">Structure</th>
                                                                <th scope="col">Reading</th>
                                                                <th scope="col">Final Score</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                        </tbody>
                                                    </table>
                                                    <small id="caption_table"></small>
                                                </div>
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
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Batch Modal -->
                    <div class="modal fade" id="deleteExamResultModal" tabindex="-1"
                        aria-labelledby="deleteExamResultModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="deleteExamResultModalLabel">Delete Exam Result</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="deleteExamResult">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="id" id="delete_batchid" required>
                                        <input type="hidden" name="user_id" id="delete_userid" required>
                                        <p>Are you sure to delete this Exam Result?</p>
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
        document.addEventListener('DOMContentLoaded', function() {
            let batchesData = @json($batches);

            if (batchesData && Array.isArray(batchesData.data) && batchesData.data.length > 0) {
                window.batchUsers = batchesData.data.map(batch => ({
                    id: batch.id,
                    users: batch.users
                }));
            } else {
                window.batchUsers = [];
            }

            // Determine the admin status
            let is_admin = @json(Auth::user()->is_admin);

            // Set URLs based on admin status
            window.showStudentsExamResultUrl = is_admin == 1 ?
                @json(route('showAdminStudentsExamResult', ['studentId' => ':studentId', 'batchId' => ':batchId'])) :
                is_admin == 2 ?
                @json(route('showLecturerStudentsExamResult', ['studentId' => ':studentId', 'batchId' => ':batchId'])) :
                '#';

            window.deleteExamResultUrl = is_admin == 1 ?
                @json(route('deleteAdminExamResult')) :
                is_admin == 2 ?
                @json(route('deleteLecturerExamResult')) :
                '#';
        });
    </script>
    @vite('resources/js/admin_lecturer/exam_results.js')
@endsection
