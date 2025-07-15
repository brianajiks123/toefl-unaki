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
                                    <li class="breadcrumb-item active" aria-current="page">Structure & Written Expression
                                    </li>
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
                            <button class="btn btn-sm btn-outline-primary text-light me-3" data-audio_file_id="" data-bs-toggle="modal"
                                data-bs-target="#addSWEQuestFileModal">
                                <i class="link-icon" data-feather="plus"></i> With File
                            </button>
                            <button class="btn btn-sm btn-outline-bitbucket text-light me-3" data-audio_file_id=""
                                data-bs-toggle="modal" data-bs-target="#addSWEQuestModal">
                                <i class="link-icon" data-feather="plus"></i> Manual
                            </button>
                            <i class="link-icon mx-2 reloadButton" data-feather="refresh-ccw" role="button"
                                aria-label="Reload Page"></i>
                        </div>
                    </div>

                    @if ($sweQuestions->isNotEmpty())
                        @foreach ($sweQuestions as $batchName => $questions)
                            <div class="card mb-3">
                                <div class="card-header">
                                    Structure & Written Expression Questions Details - {{ $batchName }}
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="swe_quests_batch_{{ $batchName }}"
                                            class="table table-hover table-bordered text-center">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Question</th>
                                                    <th scope="col">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($questions as $sweq => $question)
                                                    <tr>
                                                        <td>{{ $sweq + 1 }}</td>
                                                        <td class="text-start"
                                                            style="max-width: 300px; white-space: normal;">
                                                            {!! $question['question'] !!} <br>
                                                            A. {{ $question['option_1'] }} <br>
                                                            B. {{ $question['option_2'] }} <br>
                                                            C. {{ $question['option_3'] }} <br>
                                                            D. {{ $question['option_4'] }} <br>
                                                            <hr>
                                                            Answer: {{ $question['ans_correct'] }} <br>
                                                            <hr>
                                                        </td>
                                                        <td>
                                                            <div
                                                                class="d-flex justify-content-center align-items-center act_btn_question">
                                                                <div class="col">
                                                                    <i class="fa-solid fa-pencil editSweQuestBtn"
                                                                        role="button" data-id="{{ $question['id'] }}"
                                                                        data-question="{{ $question['question'] }}"
                                                                        data-option_1="{{ $question['option_1'] }}"
                                                                        data-option_2="{{ $question['option_2'] }}"
                                                                        data-option_3="{{ $question['option_3'] }}"
                                                                        data-option_4="{{ $question['option_4'] }}"
                                                                        data-ans_correct="{{ $question['ans_correct'] }}"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editSweQuestModal"></i>
                                                                </div>
                                                                <div class="col">
                                                                    <i class="fa-solid fa-trash deleteSweQuestBtn"
                                                                        role="button" data-id="{{ $question['id'] }}"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#deleteSweQuestModal"></i>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $sweQuestions->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <h5 class="text-center">Questions not found!</h5>
                    @endif

                    <!-- Add Structure & Written Expression Question Modal (File) -->
                    <div class="modal fade" id="addSWEQuestFileModal" tabindex="-1"
                        aria-labelledby="addSWEQuestFileModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="addSWEQuestFileModalLabel">Add Structure & Written
                                        Expression Question</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="addSweQuestFile" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="row mb-3">
                                            <select name="batch_id" id="batch_id" class="form-select fs-6" required>
                                                <option value="" selected disabled>-- Select Batch --</option>
                                                @if ($batches)
                                                    @foreach ($batches as $batch)
                                                        <option value="{{ $batch->id }}">{{ $batch->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="swe_quest_file" class="form-label">Question
                                                File</label>
                                            <input type="file" name="swe_quest_file" class="form-control"
                                                id="swe_quest_file" accept=".xlsx" required>
                                            <div class="form-text" id="basic-addon4">Select question file with .xlsx
                                                format.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-success">Add <span id="spinner_add"
                                                class="spinner-border spinner-border-sm d-none" role="status"
                                                aria-hidden="true"></span></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Add Structure & Written Expression Question Modal (Manual) -->
                    <div class="modal fade" id="addSWEQuestModal" tabindex="-1" aria-labelledby="addSWEQuestModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="addSWEQuestModalLabel">Add Structure & Written
                                        Expression Question
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="addSweQuestManual" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="row mb-3">
                                            <div class="col">
                                                <select name="batch_id" id="batch_id" class="form-select fs-6" required>
                                                    <option value="" selected disabled>-- Select Batch --</option>
                                                    @if ($batches)
                                                        @foreach ($batches as $batch)
                                                            <option value="{{ $batch->id }}">{{ $batch->name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row text-center mb-3">
                                            <div class="col">
                                                <label for="swe_quest_manual" class="form-label">Question</label>
                                                <textarea name="swe_quest_manual" class="form-control" id="swe_quest_manual" cols="30" rows="3"
                                                    required></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <label for="option_ans_1" class="form-label">Option Answer 1</label>
                                                <input type="text" name="option_ans_1" class="form-control"
                                                    id="option_ans_1" required>
                                            </div>
                                            <div class="col">
                                                <label for="option_ans_2" class="form-label">Option Answer 2</label>
                                                <input type="text" name="option_ans_2" class="form-control"
                                                    id="option_ans_2" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <label for="option_ans_3" class="form-label">Option Answer 3</label>
                                                <input type="text" name="option_ans_3" class="form-control"
                                                    id="option_ans_3" required>
                                            </div>
                                            <div class="col">
                                                <label for="option_ans_4" class="form-label">Option Answer 4</label>
                                                <input type="text" name="option_ans_4" class="form-control"
                                                    id="option_ans_4" required>
                                            </div>
                                        </div>
                                        <div class="row text-center mb-3">
                                            <label for="ans_correct" class="form-label">Answer</label>
                                            <input type="text" name="ans_correct" class="form-control"
                                                id="ans_correct" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-success">Add <span id="spinner_add"
                                                class="spinner-border spinner-border-sm d-none" role="status"
                                                aria-hidden="true"></span></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Structure & Written Expression Modal -->
                    <div class="modal fade" id="editSweQuestModal" tabindex="-1"
                        aria-labelledby="editSweQuestModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="editSweQuestModalLabel">Edit Structure & Written
                                        Expression Question
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="editSweQuestion">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="id" id="edit_swe_quest_id">
                                        <div class="row text-center mb-3">
                                            <div class="col">
                                                <label for="edit_swe_quest" class="form-label">Question</label>
                                                <textarea name="edit_swe_quest" class="form-control" id="edit_swe_quest" cols="30" rows="3"
                                                    placeholder="question" required></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <label for="edit_option_ans_1" class="form-label">Option Answer 1</label>
                                                <input type="text" name="edit_option_ans_1" class="form-control"
                                                    id="edit_option_ans_1" placeholder="option answer 1" required>
                                            </div>
                                            <div class="col">
                                                <label for="edit_option_ans_2" class="form-label">Option Answer 2</label>
                                                <input type="text" name="edit_option_ans_2" class="form-control"
                                                    id="edit_option_ans_2" placeholder="option answer 2" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <label for="edit_option_ans_3" class="form-label">Option Answer 3</label>
                                                <input type="text" name="edit_option_ans_3" class="form-control"
                                                    id="edit_option_ans_3" placeholder="option answer 3" required>
                                            </div>
                                            <div class="col">
                                                <label for="edit_option_ans_4" class="form-label">Option Answer 4</label>
                                                <input type="text" name="edit_option_ans_4" class="form-control"
                                                    id="edit_option_ans_4" placeholder="option answer 4" required>
                                            </div>
                                        </div>
                                        <div class="row text-center mb-3">
                                            <div class="col">
                                                <label for="edit_ans_correct" class="form-label">Answer Correct</label>
                                                <input type="text" name="edit_ans_correct" class="form-control"
                                                    id="edit_ans_correct" placeholder="answer correct" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary updateSweQuestBtn">Update <span
                                                id="spinner_edit" class="spinner-border spinner-border-sm d-none"
                                                role="status"aria-hidden="true"></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Structure & Written Expression Modal -->
                    <div class="modal fade" id="deleteSweQuestModal" tabindex="-1"
                        aria-labelledby="deleteSweQuestModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="deleteSweQuestModalLabel">Delete Structure & Written
                                        Expression Question</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="deleteSweQuestion">
                                    @csrf
                                    <div class="modal-body text-center">
                                        <p class="fw-semibold">Are you sure to delete this Question?</p>
                                        <input type="hidden" name="id" id="delete_sweQuestId">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
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
        let is_admin = @json(Auth::user()->is_admin);

        let addSweQuestionManualUrl = is_admin == 1 ? @json(route('adminAddSweQuestionManual')) :
            (is_admin == 2 ? @json(route('lecturerAddSweQuestionManual')) : '#');

        let addSweQuestionFileUrl = is_admin == 1 ? @json(route('adminAddSweQuestionFile')) :
            (is_admin == 2 ? @json(route('lecturerAddSweQuestionFile')) : '#');

        let editSweQuestionUrl = is_admin == 1 ? @json(route('adminEditSweQuestion')) :
            (is_admin == 2 ? @json(route('lecturerEditSweQuestion')) : '#');

        let deleteSweQuestionUrl = is_admin == 1 ? @json(route('adminDeleteSweQuestion')) :
            (is_admin == 2 ? @json(route('lecturerDeleteSweQuestion')) : '#');
    </script>
    @vite('resources/js/admin_lecturer/swe_quest.js')
@endsection
