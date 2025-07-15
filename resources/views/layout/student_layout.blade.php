<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="TOEFL UNAKI">
    <meta name="keywords" content="toefl unaki, kursus unaki, plugin unaki">
    <meta name="author" content="<?php echo $author_name; ?>">
    <title><?php echo $title; ?></title>
    <link href="<?php echo asset('admin/fonts/feather-font/css/iconfont.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset('student/css/remixicon.css'); ?>" rel="stylesheet">
    <link href="<?php echo asset('student/images/plugin-icon.jpg'); ?>" rel="shortcut icon">
    @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/css/student/style.css'])
</head>

<body>
    <aside class="sidebar">
        <button type="button" class="sidebar-close-btn">
            <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
        </button>
        <div>
            <a href="{{ route('studentExams') }}" class="sidebar-logo">
                <img src="{{ asset('student/images/logo_unaki_yellow.png') }}" alt="site logo" class="light-logo" />
                <img src="{{ asset('student/images/logo_unaki_yellow.png') }}" alt="site logo" class="dark-logo" />
                <img src="{{ asset('student/images/plugin-icon.jpg') }}" alt="site logo" class="logo-icon" />
            </a>
        </div>
        <div class="sidebar-menu-area">
            <ul class="sidebar-menu" id="sidebar-menu">
                <li class="sidebar-menu-group-title">Student Exam</li>
                <li>
                    <a href="{{ route('studentExams') }}">
                        <iconify-icon icon="heroicons:document" class="menu-icon"></iconify-icon>
                        <span>TOEFL Exams</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('studentExamResult') }}">
                        <iconify-icon icon="solar:document-text-outline" class="menu-icon"></iconify-icon>
                        <span>TOEFL Results</span>
                    </a>
                </li>
                <li class="sidebar-menu-group-title">Student Settings</li>
                <li>
                    <a href="{{ route('studentSettings') }}">
                        <iconify-icon icon="icon-park-outline:setting-one" class="menu-icon"></iconify-icon>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <main class="dashboard-main">
        <div class="navbar-header">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto">
                    <div class="d-flex flex-wrap align-items-center gap-4">
                        <button type="button" class="sidebar-toggle">
                            <iconify-icon icon="heroicons:bars-3-solid" class="icon text-2xl non-active"></iconify-icon>
                            <iconify-icon icon="iconoir:arrow-right" class="icon text-2xl active"></iconify-icon>
                        </button>
                        <button type="button" class="sidebar-mobile-toggle">
                            <iconify-icon icon="heroicons:bars-3-solid" class="icon"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <button type="button" data-theme-toggle
                            class="w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center"></button>
                        <div class="dropdown">
                            <button class="d-flex justify-content-center align-items-center rounded-circle"
                                type="button" data-bs-toggle="dropdown">
                                <img src="{{ asset('student/images/user.png') }}" alt="image"
                                    class="w-40-px h-40-px object-fit-cover rounded-circle" />
                            </button>
                            <div class="dropdown-menu to-top dropdown-menu-sm">
                                <div
                                    class="py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
                                    <div>
                                        <h6 class="text-lg text-primary-light fw-semibold mb-2">{{ Auth::user()->name }}
                                        </h6>
                                        <span class="text-secondary-light fw-medium text-sm">Student</span>
                                    </div>
                                    <button type="button" class="hover-text-danger">
                                        <iconify-icon icon="radix-icons:cross-1" class="icon text-xl"></iconify-icon>
                                    </button>
                                </div>
                                <ul class="to-top-list">
                                    <li>
                                        <a class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-primary d-flex align-items-center gap-3"
                                            href="{{ route('studentSettings') }}">
                                            <iconify-icon icon="icon-park-outline:setting-two"
                                                class="icon text-xl"></iconify-icon>
                                            Setting
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-danger d-flex align-items-center gap-3"
                                            href="{{ route('logout') }}">
                                            <iconify-icon icon="lucide:power" class="icon text-xl"></iconify-icon>
                                            Log Out
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-main-body">
            @yield('space-work')
        </div>

        <footer class="d-footer">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto">
                    <p class="mb-0">© Design and Developed by <a href="http://www.kursusplugin.com/" target="_blank"
                            class="text-primary">ATC</a> @php echo date('Y'); @endphp</p>
                </div>
            </div>
        </footer>
    </main>

    @vite(['resources/js/student/template.js'])
    <script src="{{ asset('student/js/iconify-icon.min.js') }}"></script>
    <script src="{{ asset('admin/vendors/feather-icons/feather.min.js') }}"></script>
</body>

</html>
