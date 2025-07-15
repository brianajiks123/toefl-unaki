<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="TOEFL UNAKI">
    <meta name="keywords" content="toefl unaki, kursus unaki, plugin unaki">
    <meta name="author" content="<?php echo $author_name; ?>">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title><?php echo $title; ?></title>
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="<?php echo asset('admin/vendors/core/core.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset('admin/fonts/feather-font/css/iconfont.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset('student/images/plugin-icon.jpg'); ?>" rel="shortcut icon">
    @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/css/admin_lecturer/style.css', 'resources/js/admin_lecturer/template.js'])
</head>

<body>
    <div class="main-wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <a href="/" class="sidebar-brand">
                    UNAKI<span> TOEFL</span>
                </a>
                <div class="sidebar-toggler not-active">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
            <div class="sidebar-body">
                <ul class="nav">
                    <li class="nav-item nav-category">HOME</li>
                    <li class="nav-item">
                        <a href="{{ Auth::user()->is_admin == 1 ? route('adminDashboard') : (Auth::user()->is_admin == 2 ? route('lecturerDashboard') : '#') }}"
                            class="nav-link">
                            <i class="link-icon" data-feather="home"></i>
                            <span class="link-title">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item nav-category">TASKS</li>
                    <li class="nav-item">
                        <a href="{{ Auth::user()->is_admin == 1 ? route('adminBatches') : (Auth::user()->is_admin == 2 ? route('lecturerBatches') : '#') }}"
                            class="nav-link">
                            <i class="link-icon" data-feather="list"></i>
                            <span class="link-title">Batches</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#questions" role="button"
                            aria-expanded="false" aria-controls="questions">
                            <i class="link-icon" data-feather="book"></i>
                            <span class="link-title">Questions</span>
                            <i class="link-arrow" data-feather="chevron-down"></i>
                        </a>
                        <div class="collapse" id="questions">
                            <ul class="nav sub-menu">
                                <li class="nav-item">
                                    <a href="{{ Auth::user()->is_admin == 1 ? route('adminListenQuestions') : (Auth::user()->is_admin == 2 ? route('lecturerListenQuestions') : '#') }}"
                                        class="nav-link">Listening Comprehension</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ Auth::user()->is_admin == 1 ? route('adminSWEQuestions') : (Auth::user()->is_admin == 2 ? route('lecturerSWEQuestions') : '#') }}"
                                        class="nav-link">Structure & Written Expression</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ Auth::user()->is_admin == 1 ? route('adminReadingQuestions') : (Auth::user()->is_admin == 2 ? route('lecturerReadingQuestions') : '#') }}"
                                        class="nav-link">Reading Comprehension</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#exam-results" role="button"
                            aria-expanded="false" aria-controls="exam-results">
                            <i class="link-icon" data-feather="book-open"></i>
                            <span class="link-title">Exams</span>
                            <i class="link-arrow" data-feather="chevron-down"></i>
                        </a>
                        <div class="collapse" id="exam-results">
                            <ul class="nav sub-menu">
                                <li class="nav-item">
                                    <a href="{{ Auth::user()->is_admin == 1 ? route('adminExams') : (Auth::user()->is_admin == 2 ? route('lecturerExams') : '#') }}"
                                        class="nav-link">Exam</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ Auth::user()->is_admin == 1 ? route('adminExamResults') : (Auth::user()->is_admin == 2 ? route('lecturerExamResults') : '#') }}"
                                        class="nav-link">Exam Results</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="{{ Auth::user()->is_admin == 1 ? route('adminUsers') : (Auth::user()->is_admin == 2 ? route('lecturerUsers') : '#') }}"
                            class="nav-link">
                            <i class="link-icon" data-feather="users"></i>
                            <span class="link-title">Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ Auth::user()->is_admin == 1 ? route('adminSettings') : (Auth::user()->is_admin == 2 ? route('lecturerSettings') : '#') }}"
                            class="nav-link">
                            <i class="link-icon" data-feather="settings"></i>
                            <span class="link-title">Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('logout') }}" class="nav-link">
                            <i class="link-icon" data-feather="log-out"></i>
                            <span class="link-title">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- Sidebar End -->

        <div class="page-wrapper">
            <!-- Navbar -->
            <nav class="navbar">
                <a href="" class="sidebar-toggler">
                    <i data-feather="menu"></i>
                </a>
                <div class="navbar-content">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="link-icon" data-feather="user"></i>
                            </a>
                            @if (Auth::check())
                                <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                                    <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                                        <div class="text-center">
                                            <p class="tx-16 fw-bolder">{{ Auth::user()->name }}</p>
                                            <p class="tx-12 text-muted">{{ Auth::user()->email }}</p>
                                        </div>
                                    </div>
                                    <ul class="list-unstyled p-1">
                                        <li class="dropdown-item py-2">
                                            <a href="{{ Auth::user()->is_admin == 1 ? route('adminSettings') : (Auth::user()->is_admin == 2 ? route('lecturerSettings') : '#') }}"
                                                class="text-body ms-0">
                                                <i class="me-2 icon-md" data-feather="user"></i>
                                                <span>Profile</span>
                                            </a>
                                        </li>
                                        <li class="dropdown-item py-2">
                                            <a href="{{ route('logout') }}" class="text-body ms-0">
                                                <i class="me-2 icon-md" data-feather="log-out"></i>
                                                <span>Log Out</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- Navbar End -->

            <div class="page-content">
                @yield('space-work')
            </div>

            <!-- Footer -->
            <footer
                class="footer d-flex flex-column flex-md-row align-items-center justify-content-center px-4 py-3 border-top small">
                <p class="text-muted mb-1 mb-md-0">© Design and Developed by <a href="http://www.kursusplugin.com/"
                        target="_blank">ATC</a> @php echo date('Y'); @endphp</p>
            </footer>
            <!-- Footer End -->
        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('admin/vendors/core/core.js') }}"></script>
    <!-- Feather Icons -->
    <script src="{{ asset('admin/vendors/feather-icons/feather.min.js') }}"></script>
</body>

</html>
