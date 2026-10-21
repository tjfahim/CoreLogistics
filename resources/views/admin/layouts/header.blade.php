<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Techlab33 - Custom Software</title>
    <meta name="description" content="Techlab33 - Custom Software">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="apple-icon.png">
    <link rel="shortcut icon" href="favicon.ico">

    {{-- Remember the collapsed sidebar before first paint (no flicker) --}}
    <script>try { if (localStorage.getItem('tl-collapsed') === '1') document.documentElement.classList.add('tl-collapsed'); } catch (e) {}</script>

    {{-- Bootstrap 5 --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">

    {{-- DataTables --}}
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/vendors/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/vendors/themify-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/vendors/selectFX/css/cs-skin-elastic.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/vendors/jqvmap/dist/jqvmap.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}">
    {{-- Premium core design (must stay after style.css) --}}
    <link rel="stylesheet" href="{{ asset('admin/assets/css/premium.css') }}">

    <style>
        /* dropdown arrow on every select */
        select.form-control, select.form-select, select.custom-select, .content select:not([multiple]):not([size]) {
            -webkit-appearance: none; -moz-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='none' stroke='%23555' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M1 1.5l5 5 5-5'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right .75rem center; background-size: 12px 8px;
            padding-right: 2rem;
        }
        .select2-container { width: 100% !important; }
        .select2-container .select2-selection--single { height: calc(2.25rem + 2px); }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: calc(2.25rem); }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: calc(2.25rem); }
    </style>
    @stack('styles')
</head>

<body>
<div class="tl-progress" id="tlProgress"></div>

@include('admin.layouts.sidebar')
<div class="tl-overlay" id="tlOverlay"></div>

<div class="tl-main" id="right-panel">

    <header class="tl-topbar" id="header">
        <button type="button" class="tl-iconbtn" id="tlToggle" aria-label="Toggle sidebar">
            <i class="fa fa-bars"></i>
        </button>

        <span class="tl-date">{{ now()->format('l, d F Y') }}</span>

        <div class="tl-spacer"></div>

        <button type="button" class="tl-iconbtn" id="tlFullscreen" aria-label="Toggle fullscreen" title="Fullscreen">
            <i class="fa fa-expand"></i>
        </button>

        <div class="tl-userwrap">
            <button type="button" class="tl-user" id="tlUserBtn" aria-haspopup="true" aria-expanded="false">
                <img src="{{ auth()->user()->image_url }}" alt="User Avatar">
                <span>{{ auth()->user()->name }}</span>
                <i class="fa fa-chevron-down"></i>
            </button>
            <div class="tl-menu" id="tlUserMenu">
                <div class="who">
                    <b>{{ auth()->user()->name }}</b>
                    <small>{{ auth()->user()->email }}</small>
                </div>
                <a href="{{ route('users.show', auth()->id()) }}"><i class="fa fa-user"></i> My Profile</a>
                <a class="out" href="{{ route('logout') }}"><i class="fa fa-power-off"></i> Logout</a>
            </div>
        </div>
    </header>

    <main class="tl-content">