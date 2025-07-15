@extends('student/exams/layout/exam_layout')

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
                            $lq = 1;
                        @endphp

                        @foreach ($fileListenings as $fl => $fileListening)
                            <div class="mb-4 part-section" id="part_{{ $fl + 1 }}"
                                style="{{ $fl + 1 == $currentPart ? '' : 'display: none;' }}">
                                <h6>Part {{ $fl + 1 }}</h6>
                                @php
                                    $filePath = str_replace(' ', '_', $batchName) . '/' . $fileListening->name;
                                    $fileUrl = asset('storage/user_' . Auth::user()->id . '/' . $filePath);
                                    $fileTimestamp = Storage::disk('public')->lastModified(
                                        'user_' . Auth::user()->id . '/' . $filePath,
                                    );
                                @endphp
                                @if (Storage::disk('public')->exists('user_' . Auth::user()->id . '/' . $filePath))
                                    @php
                                        $userAudioPlay = $userAudioPlays[$fileListening->id] ?? null;
                                    @endphp

                                    @if ($userAudioPlay)
                                        <audio id="audio-player-{{ $fileListening->id }}"
                                            src="{{ $fileUrl }}?v={{ $fileTimestamp }}" type="audio/mpeg"
                                            style="width: 100%;"></audio>
                                        @php
                                            $buttonClass = $userAudioPlay->status_played
                                                ? 'btn-info'
                                                : 'btn-outline-info';
                                            $buttonText = $userAudioPlay->status_played ? 'Has been played' : 'Play';
                                            $disabled = $userAudioPlay->status_played ? 'disabled' : '';
                                        @endphp
                                        <button class="btn {{ $buttonClass }} w-100 btn-rounded"
                                            id="play-button-{{ $fileListening->id }}" data-user_id="{{ Auth::user()->id }}"
                                            data-file_listening_id="{{ $fileListening->id }}" {{ $disabled }}>
                                            {{ $buttonText }}
                                        </button>
                                    @endif
                                @else
                                    <p>Audio file listening not found.</p>
                                @endif

                                @php
                                    $optionsLetters = ['A', 'B', 'C', 'D'];
                                @endphp

                                @foreach ($fileListening->questions as $question)
                                    <div class="my-3">
                                        <div class="row">
                                            <div class="col-1">
                                                <p>{{ $lq }}.</p>
                                            </div>
                                            <div class="col">
                                                @foreach ($question->shuffled_options as $opq => $option)
                                                    <div class="form-check">
                                                        <input type="radio"
                                                            id="option_{{ $loop->index + 1 }}_{{ $question->id }}"
                                                            name="question_{{ $question->id }}"
                                                            value="{{ $option }}" class="form-check-input">
                                                        <label for="option_{{ $loop->index + 1 }}_{{ $question->id }}"
                                                            class="form-check-label">{{ $optionsLetters[$opq] }}.
                                                            {{ $option }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        $lq++;
                                    @endphp
                                @endforeach

                                @if ($fl + 1 < count($fileListenings))
                                    <button type="button" class="btn btn-primary next-part"
                                        data-next-part="{{ $fl + 2 }}"
                                        data-current-part="{{ $fl + 1 }}">Next</button>
                                @else
                                    <button type="submit" class="btn btn-success finish-btn">Finish</button>
                                @endif
                            </div>
                        @endforeach
                    </form>
                </div>
            </div>
            <div class="col"></div>
        </div>
    </div>

    <script>
        window.user_id = @json(Auth::id());
        window.exam_id = @json($exam->id);
        window.updateStatusFileListeningUrl = @json(route('updateStatusFileListening'));
        window.saveUpdateRemainingTimeUrl = @json(route('saveOrUpdateRemainingTime'));
        window.updateCurrentPartUrl = @json(route('updateCurrentPart'));
        window.updateExamSessionUrl = @json(route('updateExamSession'));
        window.submitExamUrl = @json(route('submitExam'));
    </script>
    @vite('resources/js/student/listening_exam.js')
@endsection
