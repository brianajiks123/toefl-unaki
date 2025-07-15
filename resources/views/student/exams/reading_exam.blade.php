@extends('student.exams.layout.exam_layout')

@section('space-work')
    <style>
        .img-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .img-fluid {
            width: 100%;
            height: auto;
        }
    </style>

    <div class="container">
        <div class="row justify-content-between">
            <div class="col"></div>
            <div class="col-10">
                <div class="question">
                    <form id="examFinished">
                        @csrf
                        <input type="hidden" name="exam_id" value="{{ $exam->id }}">

                        @php
                            $rq = 1;
                            $optionsLetters = ['A', 'B', 'C', 'D'];
                        @endphp

                        @foreach ($readings as $reading)
                            <div class="mb-4">
                                @php
                                    $filePath = str_replace(' ', '_', $batchName) . '/' . $reading->name;
                                    $fileUrl = asset('storage/user_' . Auth::user()->id . '/' . $filePath);
                                    $fileTimestamp = Storage::disk('public')->lastModified(
                                        'user_' . Auth::user()->id . '/' . $filePath,
                                    );
                                @endphp
                                @if (Storage::disk('public')->exists('user_' . Auth::user()->id . '/' . $filePath))
                                    <div class="img-container">
                                        <img src="{{ $fileUrl }}?v={{ $fileTimestamp }}" id="img-{{ $reading->id }}"
                                            class="img-fluid" alt="Reading Image">
                                    </div>
                                @else
                                    <p>Image file not found.</p>
                                @endif

                                @foreach ($reading->questions as $question)
                                    <div class="mt-4">
                                        <p>{{ $rq }}. {{ $question->question }}</p>

                                        @foreach ($question->shuffled_options as $opq => $option)
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="question_{{ $question->id }}" value="{{ $option }}"
                                                    id="option_{{ $question->id }}_{{ $opq }}">
                                                <label class="form-check-label"
                                                    for="option_{{ $question->id }}_{{ $opq }}">
                                                    {{ $optionsLetters[$opq] }}. {{ $option }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    @php
                                        $rq++;
                                    @endphp
                                @endforeach
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-success finish-btn">Finish</button>
                    </form>
                </div>
            </div>
            <div class="col"></div>
        </div>
    </div>

    <script>
        window.user_id = @json(Auth::user()->id);
        window.exam_id = @json($exam->id);
        window.saveUpdateRemainingTimeUrl = @json(route('saveOrUpdateRemainingTime'));
        window.updateCurrentPartUrl = @json(route('updateCurrentPart'));
        window.updateExamSessionUrl = @json(route('updateExamSession'));
        window.submitExamUrl = @json(route('submitExam'));
    </script>
@endsection
