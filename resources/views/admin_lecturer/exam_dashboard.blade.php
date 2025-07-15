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
                                    <li class="breadcrumb-item active" aria-current="page">Exams</li>
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
                            <i class="link-icon me-2 addExamBtn" data-feather="plus" role="button" data-bs-toggle="modal"
                                data-bs-target="#addExamModal"></i>
                            <i class="link-icon mx-2 reloadButton" data-feather="refresh-ccw" role="button"
                                aria-label="Reload Page"></i>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Exams Details</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="exams_table" class="table table-hover table-bordered text-center">
                                    <thead>
                                        <tr>
                                            <th scope="col">Exam Name</th>
                                            <th scope="col">Exam Date</th>
                                            <th scope="col">Exam Time</th>
                                            <th scope="col">Exam Attempt</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($batchExam as $batch)
                                            @foreach ($batch->exams as $e => $exam)
                                                <tr>
                                                    <td>{{ $exam->exam_name }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($exam->exam_date)->format('d-m-Y') }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($exam->exam_time)->format('i') }} minutes
                                                    </td>
                                                    <td>{{ $exam->exam_attempt }}</td>
                                                    <td>
                                                        <div
                                                            class="d-flex justify-content-center align-items-center act_btn_exam">
                                                            <div class="col-lg-5">
                                                                <i class="link-icon editExamBtn" data-feather="edit-2"
                                                                    data-id="{{ $exam->id }}"
                                                                    data-name="{{ $exam->exam_name }}"
                                                                    data-date="{{ $exam->exam_date }}"
                                                                    data-time="{{ $exam->exam_time }}"
                                                                    data-attempt="{{ $exam->exam_attempt }}"
                                                                    data-bs-toggle="modal" data-bs-target="#editExamModal"
                                                                    role="button" aria-label="Edit Exam"></i>
                                                            </div>
                                                            <div class="col-lg-5">
                                                                <i class="link-icon deleteExamBtn" data-feather="trash"
                                                                    data-id="{{ $exam->id }}" data-bs-toggle="modal"
                                                                    data-bs-target="#deleteExamModal" role="button"
                                                                    aria-label="Delete Exam"></i>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="6">No exams found!</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                <div class="mt-3">
                                    {{ $batchExam->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Exam Modal -->
                    <div class="modal fade" id="addExamModal" tabindex="-1" aria-labelledby="addExamModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="addExamModalLabel">Add Exam</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="addExam">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <select name="batch_id" class="form-control form-control-lg fs-6" id="batch_id"
                                                required>
                                                <option value="">-- Select Batch --</option>
                                                @if (count($batches) > 0)
                                                    @foreach ($batches as $batch)
                                                        <option value="{{ $batch->id }}">{{ $batch->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="date" class="form-label">Exam Date</label>
                                            <input type="date" class="form-control form-control-lg fs-6" id="date"
                                                name="date" min="@php echo date('Y-m-d'); @endphp" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-success">Add <span id="spinner_add"
                                                class="spinner-border spinner-border-sm d-none"
                                                role="status"aria-hidden="true"></span></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Exam Modal -->
                    <div class="modal fade" id="editExamModal" tabindex="-1" aria-labelledby="editExamModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="editExamModalLabel">Edit Exam</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="updateExamForm">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="exam_id" id="exam_id">
                                        <div class="mb-3">
                                            <h3 id="edit_exam_name"></h3>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_exam_date" class="form-label">Exam Date</label>
                                            <input type="date" class="form-control form-control-lg fs-6"
                                                id="edit_exam_date" name="date" min="@php echo date('Y-m-d'); @endphp"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_exam_time" class="form-label">Exam Time</label>
                                            <input type="time" class="form-control form-control-lg fs-6"
                                                id="edit_exam_time" name="time" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_exam_attempt" class="form-label">Exam Attempt</label>
                                            <input type="number" class="form-control form-control-lg fs-6"
                                                id="edit_exam_attempt" name="attempt" placeholder="exam attempt time"
                                                min="1" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update <span id="spinner_update"
                                                class="spinner-border spinner-border-sm d-none"
                                                role="status"aria-hidden="true"></span></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Exam Modal -->
                    <div class="modal fade" id="deleteExamModal" tabindex="-1" aria-labelledby="deleteExamModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="deleteExamModalLabel">Delete Exam</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close">&times;</button>
                                </div>
                                <form id="deleteExamForm">
                                    @csrf
                                    <div class="modal-body text-center">
                                        <p class="fw-semibold">Are you sure to delete this Exam?</p>
                                        <input type="hidden" name="exam_id" id="delete_examId">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-danger">Delete <span id="spinner_delete"
                                                class="spinner-border spinner-border-sm d-none"
                                                role="status"aria-hidden="true"></span></button>
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
        let exams = @json($exams);
        let is_admin = @json(Auth::user()->is_admin);

        let addExamUrl = is_admin == 1 ? @json(route('addAdminExam')) : is_admin == 2 ? @json(route('addLecturerExam')) :
            '#';
        let updateExamUrl = is_admin == 1 ? @json(route('updateAdminExam')) : is_admin == 2 ? @json(route('updateLecturerExam')) :
            '#';
        let deleteExamUrl = is_admin == 1 ? @json(route('deleteAdminExam')) : is_admin == 2 ? @json(route('deleteLecturerExam')) :
            '#';
    </script>
    @vite('resources/js/admin_lecturer/exams.js')
@endsection
