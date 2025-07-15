@extends('student.exams.layout.exam_layout')

@section('space-work')
    <div class="container">
        <div class="row justify-content-between">
            <div class="col"></div>
            <div class="col-10">
                <div class="question">
                    <form id="examFinished">
                        @csrf
                        <input type="hidden" name="exam_id" value="{{ $exam->id }}">

                        @php
                            $optionsLetters = ['A', 'B', 'C', 'D'];
                            $sectionAQuestions = [];
                            $sectionBQuestions = [];
                            $part = 0;

                            foreach ($sweQuestions as $sweQuestion) {
                                // Count occurrences of <u>
                                $count = substr_count($sweQuestion->question, '<u>');

                                if ($count) {
                                    $sectionAQuestions[] = $sweQuestion;
                                } else {
                                    $sectionBQuestions[] = $sweQuestion;
                                }
                            }
                        @endphp

                        <!-- Section A -->
                        @if ($sectionAQuestions)
                            <div id="part_{{ $part + 1 }}"
                                style="{{ $part + 1 == $currentPart ? '' : 'display: none;' }}">
                                <h3>Section A</h3>
                                @foreach ($sectionAQuestions as $sq => $sweQuestion)
                                    <div class="mb-4">
                                        <p>{{ $sq + 1 }}. {!! $sweQuestion->question !!}</p>

                                        @php
                                            $shuffledOptions = [
                                                $sweQuestion->option_1,
                                                $sweQuestion->option_2,
                                                $sweQuestion->option_3,
                                                $sweQuestion->option_4,
                                            ];
                                            shuffle($shuffledOptions);
                                        @endphp

                                        @foreach ($shuffledOptions as $opq => $option)
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="question_{{ $sweQuestion->id }}" value="{{ $option }}"
                                                    id="option_{{ $sweQuestion->id }}_{{ $opq }}">
                                                <label class="form-check-label"
                                                    for="option_{{ $sweQuestion->id }}_{{ $opq }}">
                                                    {{ $optionsLetters[$opq] }}. {{ $option }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                                <button type="button" class="btn btn-primary next-part"
                                    data-next-part="{{ $part + 2 }}"
                                    data-current-part="{{ $part + 1 }}">Next</button>
                            </div>
                        @endif

                        <!-- Section B -->
                        @if ($sectionBQuestions)
                            <div id="part_{{ $part + 2 }}"
                                style="{{ $part + 2 == $currentPart ? '' : 'display: none;' }}">
                                <h3>Section B</h3>
                                @foreach ($sectionBQuestions as $sq => $sweQuestion)
                                    <div class="mb-4">
                                        <p>{{ count($sectionAQuestions) + $sq + 1 }}. {!! $sweQuestion->question !!}</p>

                                        @php
                                            $shuffledOptions = [
                                                $sweQuestion->option_1,
                                                $sweQuestion->option_2,
                                                $sweQuestion->option_3,
                                                $sweQuestion->option_4,
                                            ];
                                            shuffle($shuffledOptions);
                                        @endphp

                                        @foreach ($shuffledOptions as $opq => $option)
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="question_{{ $sweQuestion->id }}" value="{{ $option }}"
                                                    id="option_{{ $sweQuestion->id }}_{{ $opq }}">
                                                <label class="form-check-label"
                                                    for="option_{{ $sweQuestion->id }}_{{ $opq }}">
                                                    {{ $optionsLetters[$opq] }}. {{ $option }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                                <button type="submit" class="btn btn-success finish-btn">Finish</button>
                            </div>
                        @endif
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
    @vite('resources/js/student/swe_exam.js')
@endsection
