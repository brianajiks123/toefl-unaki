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
                                    <li class="breadcrumb-item active" aria-current="page">Listening Questions</li>
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
                            <i class="link-icon me-2 addListenQuestBtn" data-feather="plus" role="button"
                                data-bs-toggle="modal" data-bs-target="#addListenQuestModal"></i>
                            <i class="link-icon mx-2 reloadButton" data-feather="refresh-ccw" role="button"
                                aria-label="Reload Page"></i>
                        </div>
                    </div>

                    @if ($batches_file_listenings->isNotEmpty())
                        @foreach ($batches_file_listenings as $batches_file_listening)
                            <div class="card mb-3">
                                <div class="card-header">
                                    Listening Questions Details - {{ $batches_file_listening->name }}
                                </div>
                                <div class="card-body">
                                    @if ($batches_file_listening->fileListenings->isNotEmpty())
                                        @foreach ($batches_file_listening->fileListenings as $file_listening)
                                            <hr>
                                            <h5>File audio: {{ $file_listening->name }}
                                                <i class="link-icon ms-3 editAudioFileBtn" role="button"
                                                    data-feather="edit-2"
                                                    data-batch_name="{{ $batches_file_listening->name }}"
                                                    data-file_listening_id="{{ $file_listening->id }}"
                                                    data-bs-toggle="modal" data-bs-target="#editAudioFileModal"></i>
                                            </h5>
                                            <!-- Display audio file -->
                                            @php
                                                $filePath =
                                                    str_replace(' ', '_', $batches_file_listening->name) .
                                                    '/' .
                                                    str_replace(' ', '_', $file_listening->name); // Ensure consistent path formatting
                                                $fullFilePath = 'user_' . Auth::user()->id . '/' . $filePath;
                                                $fileUrl = asset('storage/' . $fullFilePath);
                                                $fileExists = Storage::disk('public')->exists($fullFilePath);
                                                $fileTimestamp = $fileExists
                                                    ? Storage::disk('public')->lastModified($fullFilePath)
                                                    : null;
                                            @endphp
                                            @if ($fileExists)
                                                <br>
                                                <audio controls src="{{ $fileUrl }}?v={{ $fileTimestamp }}"
                                                    type="audio/mpeg" style="width: 100%;"></audio>
                                            @else
                                                <p>Audio file listening not found.</p>
                                            @endif
                                            <small>
                                                Please refresh the page a few times if the audio has not updated.
                                                <i href="{{ route('adminListenQuestions') }}" role="button"
                                                    class="link-icon reloadButton" data-feather="refresh-ccw"
                                                    aria-label="Reload Page"></i>
                                            </small>
                                            <hr>
                                            <h6 class="mb-2">Part {{ $file_listening->part }}</h6>
                                            <div class="input-group mb-3">
                                                <button
                                                    class="btn btn-sm btn-outline-primary text-light me-3 addListenOptAnsFileBtn"
                                                    data-audio_file_id="{{ $file_listening->id }}"
                                                    data-batch_id="{{ $batches_file_listening->id }}"
                                                    data-audio_part="{{ $file_listening->part }}" data-bs-toggle="modal"
                                                    data-bs-target="#addListenOptAnsFileModal">
                                                    <i class="link-icon" data-feather="plus"></i> With File
                                                </button>
                                                <button
                                                    class="btn btn-sm btn-outline-bitbucket text-light addListenOptAnsBtn"
                                                    data-audio_file_id="{{ $file_listening->id }}" data-bs-toggle="modal"
                                                    data-bs-target="#addListenOptAnsModal">
                                                    <i class="link-icon" data-feather="plus"></i> Manual
                                                </button>
                                            </div>

                                            <div class="table-responsive">
                                                <table id="listening_quests_{{ $file_listening->id }}"
                                                    class="table table-hover table-bordered text-center">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">#</th>
                                                            <th scope="col">Option 1</th>
                                                            <th scope="col">Option 2</th>
                                                            <th scope="col">Option 3</th>
                                                            <th scope="col">Option 4</th>
                                                            <th scope="col">Answer</th>
                                                            <th scope="col">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($file_listening->questions as $flq => $question)
                                                            <tr>
                                                                <td>{{ $flq + 1 }}</td>
                                                                <td>{{ $question->option_1 }}</td>
                                                                <td>{{ $question->option_2 }}</td>
                                                                <td>{{ $question->option_3 }}</td>
                                                                <td>{{ $question->option_4 }}</td>
                                                                <td>{{ $question->ans_correct }}</td>
                                                                <td>
                                                                    <div
                                                                        class="d-flex justify-content-center align-items-center act_btn_question">
                                                                        <div class="col">
                                                                            <i class="link-icon editListenOptAnsBtn"
                                                                                data-feather="edit-2" role="button"
                                                                                data-id="{{ $question->id }}"
                                                                                data-option_1="{{ $question->option_1 }}"
                                                                                data-option_2="{{ $question->option_2 }}"
                                                                                data-option_3="{{ $question->option_3 }}"
                                                                                data-option_4="{{ $question->option_4 }}"
                                                                                data-ans_correct="{{ $question->ans_correct }}"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#editListenOptAnsModal"></i>
                                                                        </div>
                                                                        <div class="col">
                                                                            <i class="link-icon deleteListenOptAnsBtn"
                                                                                data-feather="trash" role="button"
                                                                                data-id="{{ $question->id }}"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#deleteListenOptAnsModal"></i>
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
                                        <h5 class="text-center">Listening question not found!</h5>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <!-- Pagination for batches_file_listenings -->
                        <div class="mt-4">
                            {{ $batches_file_listenings->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <h5 class="text-center">Batch not found!</h5>
                    @endif

                    <!-- Add Listening Question Modal -->
                    <div class="modal fade" id="addListenQuestModal" tabindex="-1"
                        aria-labelledby="addListenQuestModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="addListenQuestModalLabel">Add Listening Question</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                @php
                                    $is_admin = Auth::user()->is_admin;
                                @endphp
                                <form id="addListenQuest"  enctype="multipart/form-data">
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
                                            <label for="audio_file" class="form-label">Audio File</label>
                                            <input type="file" name="audio_files[]" class="form-control"
                                                id="audio_file" accept=".mp3" multiple required>
                                            <div class="form-text" id="basic-addon4">Select audio file with .mp3
                                                format.</div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="listening_quest_file" class="form-label">Option Answer
                                                File</label>
                                            <input type="file" name="listening_quest_file" class="form-control"
                                                id="listening_quest_file" accept=".xlsx" required>
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

                    <!-- Change Audio File Listening Modal -->
                    <div class="modal fade" id="editAudioFileModal" tabindex="-1"
                        aria-labelledby="editAudioFileModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="editAudioFileModalLabel">Change Audio File Listening
                                    </h5>
                                </div>
                                <form id="editAudioFile" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="edit_batch_name" id="batch_name" required>
                                        <input type="hidden" name="edit_audio_file_id" id="audio_file_id" required>
                                        <div class="row mb-3">
                                            <label for="edit_audio_file" class="form-label">Audio File</label>
                                            <input type="file" name="edit_audio_file_listening" class="form-control"
                                                id="edit_audio_file" accept=".mp3" required>
                                            <div class="form-text" id="basic-addon4">Select listening file with audio
                                                format.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit"
                                            class="btn btn-primary updateAudioFileListenBtn">Change</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Add Listening Option Answer Modal (Manual) -->
                    <div class="modal fade" id="addListenOptAnsModal" tabindex="-1"
                        aria-labelledby="addListenOptAnsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="addListenOptAnsModalLabel">Add Option
                                        Answer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="addListenOptAns">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="audio_file_id_manual" id="audio_file_id_manual"
                                            required>
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

                    <!-- Add Listening Option Answer Modal (File) -->
                    <div class="modal fade" id="addListenOptAnsFileModal" tabindex="-1"
                        aria-labelledby="addListenOptAnsFileModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="addListenOptAnsFileModalLabel">Add Option
                                        Answer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="addListenOptAnsFile">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="audio_file_id_file" id="audio_file_id_file" required>
                                        <input type="hidden" name="audio_file_id_batch" id="audio_file_id_batch"
                                            required>
                                        <input type="hidden" name="audio_file_id_part" id="audio_file_id_part" required>
                                        <div id="file-upload-form">
                                            <div class="row mb-3">
                                                <label for="option_ans_file" class="form-label">Option Answer File</label>
                                                <input type="file" name="option_ans_file" class="form-control"
                                                    id="option_ans_file" accept=".xlsx" required>
                                                <div class="form-text" id="basic-addon4">Select option answer file with
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

                    <!-- Edit Listening Option Answer Modal -->
                    <div class="modal fade" id="editListenOptAnsModal" tabindex="-1"
                        aria-labelledby="editListenOptAnsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="editListenOptAnsModalLabel">Edit Option Answer
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="editListenOptAns">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="id" id="edit_option_1Id">
                                        <div class="row mb-3">
                                            <div class="col">
                                                <label for="option_ans_1" class="form-label">Option Answer 1</label>
                                                <input type="text" name="option_ans_1" class="form-control"
                                                    id="edit_option_ans_1" placeholder="option answer 1" required>
                                            </div>
                                            <div class="col">
                                                <label for="option_ans_2" class="form-label">Option Answer 2</label>
                                                <input type="text" name="option_ans_2" class="form-control"
                                                    id="edit_option_ans_2" placeholder="option answer 2" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <label for="option_ans_3" class="form-label">Option Answer 3</label>
                                                <input type="text" name="option_ans_3" class="form-control"
                                                    id="edit_option_ans_3" placeholder="option answer 3" required>
                                            </div>
                                            <div class="col">
                                                <label for="option_ans_4" class="form-label">Option Answer 4</label>
                                                <input type="text" name="option_ans_4" class="form-control"
                                                    id="edit_option_ans_4" placeholder="option answer 4" required>
                                            </div>
                                        </div>
                                        <div class="row text-center mb-3">
                                            <div class="col">
                                                <label for="ans_correct" class="form-label">Answer Correct</label>
                                                <input type="text" name="ans_correct" class="form-control"
                                                    id="edit_ans_correct" placeholder="answer correct" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary updateListenOptAnsBtn">Update <span
                                                id="spinner_edit" class="spinner-border spinner-border-sm d-none"
                                                role="status"aria-hidden="true"></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Listening Option Answer Modal -->
                    <div class="modal fade" id="deleteListenOptAnsModal" tabindex="-1"
                        aria-labelledby="deleteListenOptAnsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fs-5" id="deleteListenOptAnsModalLabel">Delete Option
                                        Answer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="deleteListenOptAns">
                                    @csrf
                                    <div class="modal-body text-center">
                                        <p class="fw-semibold">Are you sure to delete this Option Answer?</p>
                                        <input type="hidden" name="id" id="delete_listenOptAnsId">
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

        let addListeningQuestionUrl = is_admin == 1 ? @json(route('addAdminListeningQuestion')) :
            (is_admin == 2 ? @json(route('addLecturerListeningQuestion')) : '#');

        let updateFileListeningUrl = is_admin == 1 ? @json(route('updateAdminFileListening')) :
            (is_admin == 2 ? @json(route('updateLecturerFileListening')) : '#');

        let listenOptionAnswerManualUrl = is_admin == 1 ? @json(route('adminListenOptionAnswerManual')) :
            (is_admin == 2 ? @json(route('lecturerListenOptionAnswerManual')) : '#');

        let listenOptionAnswerFileUrl = is_admin == 1 ? @json(route('adminListenOptionAnswerFile')) :
            (is_admin == 2 ? @json(route('lecturerListenOptionAnswerFile')) : '#');

        let editListenOptionAnswerUrl = is_admin == 1 ? @json(route('editAdminListenOptionAnswer')) :
            (is_admin == 2 ? @json(route('editLecturerListenOptionAnswer')) : '#');

        let deleteListenOptionAnswerUrl = is_admin == 1 ? @json(route('deleteAdminListenOptionAnswer')) :
            (is_admin == 2 ? @json(route('deleteLecturerListenOptionAnswer')) : '#');
    </script>
    @vite('resources/js/admin_lecturer/listen_quest.js')
@endsection
