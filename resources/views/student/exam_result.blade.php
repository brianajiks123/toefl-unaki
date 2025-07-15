@extends('layout/student_layout')

@section('space-work')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Exam Result</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="{{ route('studentExams') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Home
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Exam Result</li>
        </ul>
    </div>

    @forelse ($batches as $batch)
        <h5 class="fw-semibold my-3">{{ $batch->name }}</h5>

        <div class="row gy-4 justify-content-center">
            @if (isset($batchExamResults[$batch->id]))
                @foreach ($batchExamResults[$batch->id] as $result)
                    @if (isset($result['category_name']))
                        <div class="col-xxl-3 col-sm-4">
                            <div class="card px-24 py-16 shadow-none radius-8 border h-100 bg-gradient-start-3">
                                <div class="card-body p-0">
                                    <div
                                        class="d-flex flex-column align-items-center justify-content-between gap-1 mb-8 text-center">
                                        <span
                                            class="mb-2 fw-medium text-secondary-light text-md">{{ $result['category_name'] }}</span>
                                        <h6 class="fw-semibold my-1">{{ $result['score'] }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach

                <!-- Final Score Card -->
                <div class="col-xxl-3 col-sm-4">
                    <div class="card px-24 py-16 shadow-none radius-8 border h-100 bg-gradient-start-1">
                        <div class="card-body p-0">
                            <div
                                class="d-flex flex-column align-items-center justify-content-between gap-1 mb-8 text-center">
                                <span class="mb-2 fw-bold text-secondary-light text-md">Final Score</span>
                                <h6 class="fw-bold my-1">{{ round($batchExamResults[$batch->id]['final_score'], 2) }}
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <p>No exam results available for this batch.</p>
            @endif
        </div>
    @empty
        <p>No exam results found.</p>
    @endforelse

    <div class="mt-3">
        {{ $batches->links('pagination::bootstrap-5') }}
    </div>
@endsection
