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
    <link href="<?php echo asset('student/images/plugin-icon.jpg'); ?>" rel="shortcut icon">
    <title><?php echo $title; ?></title>
    @vite(['resources/css/auth/style.css', 'resources/js/auth/template.js'])
</head>

<body>
    @yield('space-work')
</body>

</html>
