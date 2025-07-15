<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="TOEFL UNAKI">
    <meta name="author" content="Brian Aji Pamungkas">
    <meta name="keywords" content="toefl unaki, kursus unaki, plugin unaki">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Web Icon -->
    <link rel="shortcut icon" href="{{ asset('student/images/plugin-icon.jpg') }}" />
    <!-- End Web Icon -->

    <title>{{ $title }}</title>

    @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/css/student/style_exams.css', 'resources/js/student/template_exams.js'])
</head>

<body>
    @if ($currentPart == null && $exam->exam_date == date('Y-m-d') && $exam->exam_time !== '00:00:00')
<!-- Header Section -->
        <div class="header">
    <div class="container">
        <div class="row justify-content-between align-items-center">
            <div class="col-auto">
                <div class="logo my-2">
                    <img src="{{ asset('student/images/logo_unaki_yellow.png') }}" alt="Logo">
                </div>
            </div>
            <div class="col text-center"></div>
            <div class="col-auto">
                <span class="text-danger bg-danger text-white py-2 px-3 exam-timer"
                    id="exam-timer">{{ $exam->exam_time }}</span>
                <input type="hidden" id="remaining-time" name="remaining_time" value="{{ $exam->exam_time }}">
            </div>
        </div>
    </div>
    </div>

    <!-- Content Section -->
    <div class="content">
        @yield('space-work')
    </div>

    <script>
        window.examTime = @json($exam->exam_time);
        window.examId = @json($exam->id);
    </script>
@elseif ($exam->exam_date > date('Y-m-d') && $exam->exam_time !== '00:00:00')
    <h5 class="mt-5 text-center text-danger"><i class="fa-solid fa-triangle-exclamation"></i> TOEFL test has not
        started yet.</h5>
    <center><a href="/" class="btn btn-secondary mt-3">Back</a></center>
@else
    <h5 class="mt-5 text-center text-danger"> <i class="fa-solid fa-triangle-exclamation"></i> TOEFL Test has
        expired.</h5>
    <center><a href="/" class="btn btn-secondary mt-3">Back</a></center>
    @endif
    @vite('resources/js/student/template_exams.js')
    </body>

</html>
