@extends('layout/admin_layout')

@section('space-work')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Dashboard</h4>
        </div>
    </div>

    <div class="row flex-grow-1 justify-content-center align-item-center text-center">
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-9 fw-semibold">Total Batch</h2>
                    <div class="row d-flex flex-column align-items-center justify-content-center">
                        <div class="col-lg">
                            @if ($totals['total_batch'])
                                <h4 class="fw-semibold mb-3">{{ $totals['total_batch'] }}</h4>
                            @else
                                <h4 class="fw-semibold mb-3">0</h4>
                            @endif
                        </div>
                        <div class="col-lg">
                            <a href="{{ Auth::user()->is_admin == 1 ? route('adminBatches') : (Auth::user()->is_admin == 2 ? route('lecturerBatches') : '#') }}"
                                class="btn btn-outline-success text-light">See more</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-9 fw-semibold">Total Exam</h2>
                    <div class="row d-flex flex-column align-items-center justify-content-center">
                        <div class="col-lg">
                            @if ($totals['total_exam'])
                                <h4 class="fw-semibold mb-3">{{ $totals['total_exam'] }}</h4>
                            @else
                                <h4 class="fw-semibold mb-3">0</h4>
                            @endif
                        </div>
                        <div class="col-lg">
                            <a href="{{ Auth::user()->is_admin == 1 ? route('adminExams') : (Auth::user()->is_admin == 2 ? route('lecturerExams') : '#') }}"
                                class="btn btn-outline-success text-light">See more</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-9 fw-semibold">Exam Result</h2>
                    <div class="row d-flex flex-column align-items-center justify-content-center">
                        <div class="col-lg">
                            <h4 class="fw-semibold mb-3"></h4>
                        </div>
                        <div class="col-lg">
                            <a href="{{ Auth::user()->is_admin == 1 ? route('adminExamResults') : (Auth::user()->is_admin == 2 ? route('lecturerExamResults') : '#') }}"
                                class="btn btn-outline-success text-light">See more</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-9 fw-semibold">Total User</h2>
                    <div class="row d-flex flex-column align-items-center justify-content-center">
                        <div class="col-lg">
                            @if ($totals['total_user'])
                                <h4 class="fw-semibold mb-3">{{ $totals['total_user'] }}</h4>
                            @else
                                <h4 class="fw-semibold mb-3">0</h4>
                            @endif
                        </div>
                        <div class="col-lg">
                            <a href="{{ Auth::user()->is_admin == 1 ? route('adminUsers') : (Auth::user()->is_admin == 2 ? route('lecturerUsers') : '#') }}"
                                class="btn btn-outline-success text-light">See more</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row flex-grow-1 justify-content-center align-item-center text-center">
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h2 class="card-title mb-9 fw-semibold">Total Listening Comprehension Question</h2>
                    <div class="row d-flex flex-column align-items-center">
                        <div class="col-lg">
                            @if ($totals['total_listen_question'])
                                <h4 class="fw-semibold mb-3">{{ $totals['total_listen_question'] }}</h4>
                            @else
                                <h4 class="fw-semibold mb-3">0</h4>
                            @endif
                        </div>
                        <div class="col-lg">
                            <a href="{{ Auth::user()->is_admin == 1 ? route('adminListenQuestions') : (Auth::user()->is_admin == 2 ? route('lecturerListenQuestions') : '#') }}"
                                class="btn btn-outline-primary text-light">See more</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h2 class="card-title mb-9 fw-semibold">Total Structure & Written Expression Question</h2>
                    <div class="row d-flex flex-column align-items-center">
                        <div class="col-lg">
                            @if ($totals['total_swe_question'])
                                <h4 class="fw-semibold mb-3">{{ $totals['total_swe_question'] }}</h4>
                            @else
                                <h4 class="fw-semibold mb-3">0</h4>
                            @endif
                        </div>
                        <div class="col-lg">
                            <a href="{{ Auth::user()->is_admin == 1 ? route('adminSWEQuestions') : (Auth::user()->is_admin == 2 ? route('lecturerSWEQuestions') : '#') }}"
                                class="btn btn-outline-primary text-light">See more</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h2 class="card-title mb-9 fw-semibold">Total Reading Comprehension Question</h2>
                    <div class="row d-flex flex-column align-items-center">
                        <div class="col-lg">
                            @if ($totals['total_reading_question'])
                                <h4 class="fw-semibold mb-3">{{ $totals['total_reading_question'] }}</h4>
                            @else
                                <h4 class="fw-semibold mb-3">0</h4>
                            @endif
                        </div>
                        <div class="col-lg">
                            <a href="{{ Auth::user()->is_admin == 1 ? route('adminReadingQuestions') : (Auth::user()->is_admin == 2 ? route('lecturerReadingQuestions') : '#') }}"
                                class="btn btn-outline-primary text-light">See more</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row d-flex justify-content-center">
        <div class="col stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-center">
                            <thead>
                                <tr>
                                    <th class="pt-0">#</th>
                                    <th class="pt-0">Exam Name</th>
                                    <th class="pt-0">Total Student</th>
                                    <th class="pt-0">Exam Date</th>
                                    <th class="pt-0">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($paginatedData as $b => $batch)
                                    <tr>
                                        <td>{{ $b + 1 }}</td>
                                        <td>{{ $batch['exam_name'] }}</td>
                                        <td>{{ $batch['total_users'] }}</td>

                                        @if ($batch['exam_date'] !== null)
                                            <td>{{ \Carbon\Carbon::parse($batch['exam_date'])->format('d-m-Y') }}</td>

                                            @php
                                                $examDate = \Carbon\Carbon::parse($batch['exam_date']);
                                                $today = \Carbon\Carbon::now()->format('d-m-Y');
                                            @endphp

                                            @if ($today > $examDate->format('d-m-Y'))
                                                <td><span class="badge bg-danger">Ended</span></td>
                                            @elseif ($today == $examDate->format('d-m-Y'))
                                                <td><span class="badge bg-success">Running</span></td>
                                            @else
                                                <td><span class="badge bg-warning">Waiting</span></td>
                                            @endif
                                        @else
                                            <td></td>
                                            <td></td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination links -->
                        <div class="mt-3">
                            {{ $paginatedData->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
