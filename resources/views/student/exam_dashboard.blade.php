@extends('layout/student_layout')

@section('space-work')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Exams</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="{{ route('studentExams') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Home
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Exams</li>
        </ul>
    </div>

    @if (count($examLinks) > 0)
        <div class="row">
            <div class="col">
                <h5 class="text-warning fw-semibold text-center"><i class="fa-solid fa-triangle-exclamation"></i> The test
                    link is only valid once!</h5>
            </div>
        </div>

        @forelse ($batchExams as $batchName => $exams)
            <p class="fw-bold my-3">{{ $batchName }}</p>

            <div class="row gy-4 mb-2">
                <!-- Dashboard Widget Start -->
                @forelse ($exams as $exam)
                    <div class="col-xxl-3 col-sm-6">
                        <div class="card px-24 py-16 shadow-none radius-8 border h-100 bg-gradient-start-3">
                            <div class="card-body p-0">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="w-64-px h-64-px radius-16 bg-base-50 d-flex justify-content-center align-items-center me-20">
                                            <span
                                                class="mb-0 w-40-px h-40-px bg-primary-600 flex-shrink-0 text-white d-flex justify-content-center align-items-center radius-8 h6 mb-0">
                                                <iconify-icon icon="heroicons:document" class="icon"></iconify-icon>
                                            </span>
                                        </div>

                                        <div>
                                            <div class="col">
                                                <span
                                                    class="mb-2 fw-medium text-secondary-light text-md">{{ $exam->exam_name }}</span>
                                            </div>
                                            <div class="col">
                                                <span class="fw-semibold my-1">Date :
                                                    {{ \Carbon\Carbon::parse($exam->exam_date)->format('d-m-Y') }}</span>
                                            </div>
                                            <div class="col">
                                                <span class="fw-semibold my-1">Time :
                                                    {{ \Carbon\Carbon::parse($exam->exam_time)->format('i') }}
                                                    minutes</span>
                                            </div>
                                            <a href="{{ $exam->link }}"
                                                class="btn btn-outline-primary text-sm mb-0 mt-3">Go
                                                Test</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p>No exams available for this batch.</p>
                @endforelse
                <!-- Dashboard Widget End -->
            </div>
        @empty
            <p>No exams batch found.</p>
        @endforelse

        <div class="mt-3">
            {{ $batches->links('pagination::bootstrap-5') }}
        </div>
    @else
        <p>No exams found.</p>
    @endif
@endsection
