<!DOCTYPE html>
<html lang="en">
<head>
    @include('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('dist/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/mix/app.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">
    <script src="{{ asset('assets/mix/app.js') }}"></script>
    <script src="{{ asset('dist/select2/js/select2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    @stack('styles')
</head>
<body>
    <div id="app">
        <div class="main-wrapper">
            @auth
                @include('layout.sb_admin')
            @endauth

            @yield('content')

            @auth
                @include('layout.footer')
            @endauth
        </div>
    </div>

    @stack('scripts')
    <script>
    $('.summernote').summernote({ tabsize: 2, height: 220 });
    </script>
</body>
</html>
