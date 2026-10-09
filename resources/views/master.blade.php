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
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, div, a, td, th, button, input, select, textarea, label,
        .main-wrapper, .main-sidebar, .sidebar-menu, .navbar, .card, .dropdown-menu {
            font-family: "Plus Jakarta Sans", sans-serif !important;
            font-optical-sizing: auto;
        }
        body {
            font-weight: 400;
            font-size: 13px !important;
            line-height: 1.55;
        }
        /* Headings — turun 2-4px dari default Stisla/Bootstrap */
        h1 { font-size: 1.45rem !important; }
        h2 { font-size: 1.3rem !important; }
        h3 { font-size: 1.12rem !important; }
        h4 { font-size: 1rem !important; }
        h5 { font-size: 0.88rem !important; }
        h6 { font-size: 0.8rem !important; }
        .card .card-header h4 { font-size: 14px !important; }
        .card .card-body { font-size: 13px !important; }
        .section-header h1 { font-size: 20px !important; }
        .section-header .section-header-breadcrumb { font-size: 12px !important; }

        /* Form — lebih ramping (default Stisla 42px / 14px) */
        .form-group { margin-bottom: 14px !important; }
        .form-group .control-label,
        .form-group > label {
            font-size: 11.5px !important;
            margin-bottom: 4px !important;
        }
        .input-group-text,
        select.form-control:not([size]):not([multiple]),
        .form-control:not(.form-control-sm):not(.form-control-lg) {
            font-size: 12.5px !important;
            padding: 6px 10px !important;
            height: 35px !important;
            min-height: 35px !important;
            line-height: 1.5 !important;
        }
        textarea.form-control,
        textarea.form-control:not(.form-control-sm):not(.form-control-lg) {
            height: auto !important;
            min-height: 68px !important;
            padding: 7px 10px !important;
        }
        .custom-select,
        .custom-file-label {
            font-size: 12.5px !important;
            height: 35px !important;
            line-height: 1.5 !important;
        }
        .custom-file-label:after { height: 33px !important; line-height: 1.7 !important; font-size: 12.5px !important; }
        .col-form-label { font-size: 12.5px !important; padding-top: 7px !important; padding-bottom: 7px !important; }
        .form-text { font-size: 11px !important; line-height: 1.5 !important; }
        .invalid-feedback { font-size: 11px !important; }

        /* Input group addons sejajar dengan input 35px */
        .input-group-prepend .input-group-text,
        .input-group-append .input-group-text,
        .input-group-prepend .btn,
        .input-group-append .btn { height: 35px !important; min-height: 35px !important; }

        /* Buttons — normal + lg dinormalisasi (banyak view pakai btn-lg untuk tombol biasa) */
        .btn {
            font-size: 12.5px !important;
            padding: 7px 14px !important;
            line-height: 1.5 !important;
            border-radius: 5px !important;
        }
        .btn-lg, .btn-group-lg > .btn {
            font-size: 13px !important;
            padding: 8px 18px !important;
            border-radius: 6px !important;
        }
        .btn-sm, .btn-group-sm > .btn {
            font-size: 11.5px !important;
            padding: 4px 10px !important;
        }
        .btn-icon.icon-right { padding-right: 14px !important; }

        /* Ukuran lg/sm untuk form — dijinakkan */
        .form-control-lg {
            font-size: 13.5px !important;
            height: 40px !important;
            min-height: 40px !important;
            padding: 7px 12px !important;
        }
        .form-control-sm {
            font-size: 11.5px !important;
            height: 30px !important;
            min-height: 30px !important;
            padding: 4px 8px !important;
        }

        /* Table, badge, dropdown, modal */
        .table { font-size: 12.5px !important; }
        .table th, .table td { padding: 8px 10px !important; }
        .badge, .label { font-size: 10.5px !important; }
        .dropdown-menu { font-size: 12.5px !important; }
        .dropdown-item { padding: 6px 14px !important; }
        .modal-title { font-size: 15px !important; }
        .modal-body { font-size: 12.5px !important; }
        .alert { font-size: 12.5px !important; padding: 10px 14px !important; }
        .breadcrumb { font-size: 12px !important; }
        .pagination .page-link { font-size: 12px !important; padding: 6px 11px !important; }

        /* Sidebar & navbar ikut mengecil */
        .main-sidebar .sidebar-menu li a { font-size: 12.5px !important; }
        .main-sidebar .sidebar-menu li.menu-header { font-size: 10.5px !important; }
        .navbar .nav-link { font-size: 12.5px !important; }

        /* Select2 sejajar input 35px */
        .select2-container .select2-selection--single,
        .select2-container .select2-selection--multiple {
            min-height: 35px !important;
            height: 35px !important;
            font-size: 12.5px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 33px !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 33px !important; }
        .select2-container .select2-dropdown, .select2-results__option { font-size: 12.5px !important; }

        /* Pengecualian POS kasir — tetap touch-friendly tapi tidak raksasa */
        .btn.tablet-btn { font-size: 13.5px !important; min-height: 44px !important; }
        .pos-total-amount { font-size: 2rem !important; }
        .pos-total-display { padding: 14px 16px !important; }
    </style>
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
