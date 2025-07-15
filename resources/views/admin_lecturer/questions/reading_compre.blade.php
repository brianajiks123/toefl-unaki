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
                                    <li class="breadcrumb-item active" aria-current="page">Reading Comprehension
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
                            <i class="link-icon me-2 addReadingQuestsBtn" data-feather="plus" role="button"
                                data-bs-toggle="modal" data-bs-target="#addReadingQuestsModal"></i>
                            <i class="link-icon mx-2 reloadButton" data-feather="refresh-ccw" role="button"
                                aria-label="Reload Page"></i>
                        </div>
                    </div>

                    @if ($batches_readings->isNotEmpty())
                        @foreach ($batches_readings as $batches_reading)
                            <div class="card mb-3">
                                <div class="card-header">
                                    Reading Questions Details - {{ $batches_reading->name }}
                                </div>
                                <div class="card-body">
                                    @if ($batches_reading->readings->isNotEmpty())
                                        @foreach ($batches_reading->readings as $reading)
                                            <hr>
                                            <h5>File image: {{ $reading->name }}
                                                <i class="link-icon ms-3 editImageFileBtn" role="button"
                                                    data-feather="edit-2" data-batch_name="{{ $batches_reading->name }}"
                                                    data-reading_part="{{ $reading->part }}"
                                                    data-reading_id="{{ $reading->id }}" data-bs-toggle="modal"
                                                    data-bs-target="#editImageFileModal"></i>
                                            </h5>
                                            <!-- Display image file -->
                                            @php
                                                $filePath =
                                                    str_replace(' ', '_', $batches_reading->name) .
                                                    '/' .
                                                    str_replace(' ', '_', $reading->name); // Ensure consistent path formatting
                                                $fullFilePath = 'user_' . Auth::user()->id . '/' . $filePath;
                                                $fileUrl = asset('storage/' . $fullFilePath);
                                                $fileExists = Storage::disk('public')->exists($fullFilePath);
                                                $fileTimestamp = $fileExists
                                                    ? Storage::disk('public')->lastModified($fullFilePath)
                                                    : null;
                                            @endphp
                                            @if ($fileExists)
                                                <br>
                                                <div class="row d-flex justify-content-center px-3">
                                                    <div class="container">
                                                        <img src="{{ $fileUrl }}?v={{ $fileTimestamp }}"
                                                            class="img-fluid">
                                                    </div>
                                                </div>
                                            @else
                                                <p>Image file reading not found.</p>
                                            @endif
                                            <hr>
                                            <h6 class="mb-2">Q. {{ $reading->part }}</h6>
                                            <div class="input-group mb-3">
                                                <button
                                                    class="btn btn-sm btn-outline-primary text-light me-3 addReadingQuestFileBtn"
                                                    data-image_file_id="{{ $reading->id }}"
                                                    data-batch_id="{{ $batches_reading->id }}"
                                                    data-reading_part="{{ $reading->part }}" data-bs-toggle="modal"
                                                    data-bs-target="#addReadingQuestFileModal">
                                                    <i class="link-icon" data-feather="plus"></i> With File
                                                </button>
                                                <button
                                                    class="btn btn-sm btn-outline-bitbucket text-light addReadingQuestBtn"
                                                    data-image_file_id="{{ $reading->id }}" data-bs-toggle="modal"
                                                    data-bs-target="#addReadingQuestModal">
                                                    <i class="link-icon" data-feather="plus"></i> Manual
                                                </button>
                                            </div>

                                            <div class="table-responsive">
                                                <table id="reading_quests_{{ $reading->id }}"
                                                    class="table table-hover table-bordered text-center">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">#</th>
                                                            <th scope="col">Question</th>
                                                            <th scope="col">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($reading->questions as $rq => $question)
                                                            <tr>
                                                                <td>{{ $rq + 1 }}</td>
                                                                <td class="text-start"
                                                                    style="max-width: 300px; white-space: normal;">
                                                                    {{ $question->question }}<br>
                                                                    A. {{ $question->option_1 }}<br>
                                                                    B. {{ $question->option_2 }}<br>
                                                                    C. {{ $question->option_3 }}<br>
                                                                    D. {{ $question->option_4 }}<br>
                                                                    <hr>
                                                                    Answer: {{ $question->ans_correct }}<br>
                                                                    <hr>
                                                                </td>
                                                                <td>
                                                                    <div
                                                                        class="d-flex justify-content-center align-items-center act_btn_question">
                                                                        <div class="col">
                                                                            <i class="link-icon editReadingQuestBtn"
                                                                                data-feather="edit-2" role="button"
                                                                                data-id="{{ $question->id }}"
                                                                                data-question="{{ $question->question }}"
                                                                                data-option_1="{{ $question->option_1 }}"
                                                                                data-option_2="{{ $question->option_2 }}"
                                                                                data-option_3="{{ $question->option_3 }}"
                                                                                data-option_4="{{ $question->option_4 }}"
                                                                                data-ans_correct="{{ $question->ans_correct }}"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#editReadingQuestModal"></i>
                                                                        </div>
                                                                        <div class="col">
                                                                            <i class="link-icon deleteReadingQuestBtn"
                                                                                data-feather="trash" role="button"
                                                                                data-id="{{ $question->id }}"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#deleteReadingQuestModal"></i>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endforeach
                                    @else
                                        <h5 class="text-center">Reading question not found!</h5>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <!-- Pagination for batches_readings -->
                        <div class="mt-4">
                            {{ $batches_readings->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <h5 class="text-center">Batch not found!</h5>
                    @endif

                    <!-- Add Reading Question Modal -->
                    <div class="modal fade" id="addReadingQuestsModal" tabindex="-1"
                        aria-labelledby="addReadingQuestsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="addReadingQuestsModalLabel">Add Reading Question</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="addReadingQuests" enctype="multipart/form-data">
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
                                            <label for="reading_file" class="form-label">Reading File</label>
                                            <input type="file" name="reading_files[]" class="form-control"
                                                id="reading_file" accept=".jpg, .png" multiple required>
                                            <div class="form-text" id="basic-addon4">Select reading file with .jpg or png
                                                format.</div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="reading_quest_file" class="form-label">Question
                                                File</label>
                                            <input type="file" name="reading_quest_file" class="form-control"
                                                id="reading_quest_file" accept=".xlsx" required>
                                            <div class="form-text" id="basic-addon4">Select option answer file with .xlsx
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

                    <!-- Change Image File Reading Modal -->
                    <div class="modal fade" id="editImageFileModal" tabindex="-1"
                        aria-labelledby="editImageFileModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="editImageFileModalLabel">Change Image File Reading
                                    </h5>
                                </div>
                                <form id="editImageFile" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="edit_batch_name" id="batch_name" required>
                                        <input type="hidden" name="edit_image_file_id" id="image_file_id" required>
                                        <input type="hidden" name="edit_image_file_part" id="image_file_part" required>
                                        <div class="row mb-3">
                                            <label for="edit_image_file" class="form-label">Image File</label>
                                            <input type="file" name="edit_image_file_reading" class="form-control"
                                                id="edit_image_file" accept=".jpg, .png" required>
                                            <div class="form-text" id="basic-addon4">Select reading file with image
                                                format.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit"
                                            class="btn btn-primary updateImageFileReadingBtn">Change</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Add Reading Question Modal (Manual) -->
                    <div class="modal fade" id="addReadingQuestModal" tabindex="-1"
                        aria-labelledby="addReadingQuestModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="addReadingQuestModalLabel">Add Question</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="addReadingQuest">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="image_file_id_manual" id="image_file_id_manual"
                                            required>
                                        <div class="row text-center mb-3">
                                            <div class="col">
                                                <label for="reading_quest" class="form-label">Question</label>
                                                <textarea name="reading_quest" class="form-control" id="reading_quest" cols="30" rows="3"
                                                    placeholder="question" required></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <label for="option_ans_1" class="form-label">Option Answer 1</label>
                                                <input type="text" name="option_ans_1" class="form-control"
                                                    id="option_ans_1" placeholder="option answer 1" required>
                                            </div>
                                            <div class="col">
                                                <label for="option_ans_2" class="form-label">Option Answer 2</label>
                                                <input type="text" name="option_ans_2" class="form-control"
                                                    id="option_ans_2" placeholder="option answer 2" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <label for="option_ans_3" class="form-label">Option Answer 3</label>
                                                <input type="text" name="option_ans_3" class="form-control"
                                                    id="option_ans_3" placeholder="option answer 3" required>
                                            </div>
                                            <div class="col">
                                                <label for="option_ans_4" class="form-label">Option Answer 4</label>
                                                <input type="text" name="option_ans_4" class="form-control"
                                                    id="option_ans_4" placeholder="option answer 4" required>
                                            </div>
                                        </div>
                                        <div class="row text-center mb-3">
                                            <div class="col">
                                                <label for="ans_correct" class="form-label">Answer Correct</label>
                                                <input type="text" name="ans_correct" class="form-control"
                                                    id="ans_correct" placeholder="answer correct" required>
                                            </div>
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

                    <!-- Add Reading Question Modal (File) -->
                    <div class="modal fade" id="addReadingQuestFileModal" tabindex="-1"
                        aria-labelledby="addReadingQuestFileModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="addReadingQuestFileModalLabel">Add Question</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="addReadingQuestFile">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="image_file_id_file" id="image_file_id_file" required>
                                        <input type="hidden" name="image_file_id_batch" id="image_file_id_batch"
                                            required>
                                        <input type="hidden" name="image_file_id_part" id="image_file_id_part" required>
                                        <div id="file-upload-form">
                                            <div class="row mb-3">
                                                <label for="quest_file" class="form-label">Question File</label>
                                                <input type="file" name="quest_file" class="form-control"
                                                    id="quest_file" accept=".xlsx" required>
                                                <div class="form-text" id="basic-addon4">Select question file with
                                                    .xlsx format.</div>
                                            </div>
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

                    <!-- Edit Reading Question Modal -->
                    <div class="modal fade" id="editReadingQuestModal" tabindex="-1"
                        aria-labelledby="editReadingQuestModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="editReadingQuestModalLabel">Edit Reading
                                        Comprehension Question
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="editReadingQuestion">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="id" id="edit_reading_quest_id">
                                        <div class="row text-center mb-3">
                                            <div class="col">
                                                <label for="edit_reading_quest" class="form-label">Question</label>
                                                <textarea name="edit_reading_quest" class="form-control" id="edit_reading_quest" cols="30" rows="3"
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
                                        <button type="submit" class="btn btn-primary updateReadingQuestBtn">Update <span
                                                id="spinner_edit" class="spinner-border spinner-border-sm d-none"
                                                role="status"aria-hidden="true"></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Reading Question Modal -->
                    <div class="modal fade" id="deleteReadingQuestModal" tabindex="-1"
                        aria-labelledby="deleteReadingQuestModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="deleteReadingQuestModalLabel">Delete Reading
                                        Comprehension Question</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="deleteReadingQuestion">
                                    @csrf
                                    <div class="modal-body text-center">
                                        <p class="fw-semibold">Are you sure to delete this Question?</p>
                                        <input type="hidden" name="id" id="delete_readingQuestId">
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

        let addReadingQuestionUrl = is_admin == 1 ? @json(route('addAdminReadingQuestion')) :
            (is_admin == 2 ? @json(route('addLecturerReadingQuestion')) : '#');

        let updateFileReadingUrl = is_admin == 1 ? @json(route('updateAdminFileReading')) :
            (is_admin == 2 ? @json(route('updateLecturerFileReading')) : '#');

        let readingQuestUrl = is_admin == 1 ? @json(route('adminReadingQuest')) :
            (is_admin == 2 ? @json(route('lecturerReadingQuest')) : '#');

        let readingQuestFileUrl = is_admin == 1 ? @json(route('adminReadingQuestFile')) :
            (is_admin == 2 ? @json(route('lecturerReadingQuestFile')) : '#');

        let editReadingQuestUrl = is_admin == 1 ? @json(route('editAdminReadingQuest')) :
            (is_admin == 2 ? @json(route('editLecturerReadingQuest')) : '#');

        let deleteReadingQuestUrl = is_admin == 1 ? @json(route('deleteAdminReadingQuest')) :
            (is_admin == 2 ? @json(route('deleteLecturerReadingQuest')) : '#');
    </script>
    @vite('resources/js/admin_lecturer/reading_quest.js')
@endsection
