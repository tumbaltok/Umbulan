<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-cache">
    <title>@yield('title') | PT META Adhya Tirta Umbulan</title>

    {{-- Favicon dan Ikon Aplikasi --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo-circle.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-circle.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-circle.png') }}">
    <script>
        (function() {
            @auth
                // Default: Tema Light untuk user awal / login pertama
                var savedTheme = localStorage.getItem('theme');
                if (savedTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    if (!savedTheme) {
                        localStorage.setItem('theme', 'light');
                    }
                }
            @else
                document.documentElement.classList.remove('dark');
            @endauth
        })();

        // Pre-init: deteksi status sematan sidebar desktop untuk hindari layout shift
        (function() {
            var savedPinnedStatus = localStorage.getItem('umbulan_sidebar_pinned');
            var savedCollapsedStatus = localStorage.getItem('umbulan_sidebar_manual_collapsed');
            if ((savedPinnedStatus === 'true' || (savedPinnedStatus === null && window.innerWidth >= 1024)) && window.innerWidth >= 768) {
                if (savedCollapsedStatus !== 'true') {
                    document.documentElement.classList.add('sidebar-pinned-init');
                }
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Sembunyikan scrollbar bawaan peramban */
        html, body, div, nav, aside, main, section {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        html::-webkit-scrollbar, body::-webkit-scrollbar, div::-webkit-scrollbar,
        nav::-webkit-scrollbar, aside::-webkit-scrollbar, main::-webkit-scrollbar,
        section::-webkit-scrollbar {
            display: none;
        }

        /* Animasi dasar dropdown menu */
        .dropdown-content, .sub-dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .dropdown-container.dropdown-open > .dropdown-content {
            max-height: 1000px;
        }

        .sub-dropdown-container.sub-dropdown-open > .sub-dropdown-content {
            max-height: 500px;
        }

        .chevron-icon, .sub-chevron-icon, .navbar-profile-chevron {
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .dropdown-open > .dropdown-btn .chevron-icon,
        .sub-dropdown-open > .sub-dropdown-btn .sub-chevron-icon,
        .navbar-profile-open .navbar-profile-chevron {
            transform: rotate(180deg) !important;
        }

        .sub-dropdown-btn {
            width: calc(100% - 1.25rem) !important;
        }

        /* Custom scrollbar halus untuk navigasi sidebar */
        .sidebar-nav-container::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-nav-container::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-nav-container::-webkit-scrollbar-thumb {
            background: rgba(14, 165, 233, 0.25);
            border-radius: 9999px;
        }
        .sidebar-nav-container::-webkit-scrollbar-thumb:hover {
            background: rgba(14, 165, 233, 0.5);
        }
        html.dark .sidebar-nav-container::-webkit-scrollbar-thumb {
            background: rgba(56, 189, 248, 0.25);
        }
        html.dark .sidebar-nav-container::-webkit-scrollbar-thumb:hover {
            background: rgba(56, 189, 248, 0.5);
        }

        /* Transisi sidebar mobile & desktop yang Halus, Terpadu & Modern */
        :root {
            --sidebar-easing: cubic-bezier(0.4, 0, 0.2, 1);
            --sidebar-duration: 0.32s;
        }

        #sidebarApp {
            will-change: transform, width;
            transition: transform var(--sidebar-duration) var(--sidebar-easing),
                        width var(--sidebar-duration) var(--sidebar-easing),
                        box-shadow var(--sidebar-duration) var(--sidebar-easing),
                        background-color 0.25s ease,
                        border-color 0.25s ease;
        }

        #sidebarBackdrop {
            transition: opacity var(--sidebar-duration) var(--sidebar-easing),
                        visibility var(--sidebar-duration) var(--sidebar-easing);
        }

        /* Mobile sidebar */
        @media (max-width: 767px) {
            #sidebarApp {
                width: 18rem !important; /* 288px */
            }
            #sidebarApp #pinSidebarBtn {
                display: none !important;
            }
            #sidebarApp .hide-on-collapse {
                opacity: 1 !important;
                max-width: none !important;
                transform: none !important;
                pointer-events: auto !important;
                white-space: normal !important;
                display: block !important;
            }
            #sidebarApp .sidebar-header-bg .hide-on-collapse {
                display: block !important;
            }
            #sidebarApp .sidebar-nav-container .hide-on-collapse {
                display: inline-block !important;
            }
            #sidebarApp .dropdown-btn .hide-on-collapse,
            #sidebarApp a .hide-on-collapse {
                display: inline-block !important;
            }
        }

        /* Desktop Sidebar (Hover-mode & Pinned Mode) */
        @media (min-width: 768px) {
            #sidebarApp {
                transition: width var(--sidebar-duration) var(--sidebar-easing),
                            box-shadow var(--sidebar-duration) var(--sidebar-easing);
            }

            .sidebar-hover-mode,
            .sidebar-pinned-mode {
                width: 5.25rem; /* ~84px saat kuncup */
                overflow: hidden !important;
                will-change: width;
            }

            .sidebar-hover-mode:hover,
            .sidebar-pinned-mode:not(.sidebar-pinned-collapsed),
            html.sidebar-pinned-init .sidebar-hover-mode,
            html.sidebar-pinned-init .sidebar-pinned-mode {
                width: 18rem !important;
                box-shadow: 10px 0 30px -5px rgba(0, 0, 0, 0.08);
            }

            html.dark .sidebar-hover-mode:hover,
            html.dark .sidebar-pinned-mode:not(.sidebar-pinned-collapsed),
            html.dark.sidebar-pinned-init .sidebar-hover-mode,
            html.dark.sidebar-pinned-init .sidebar-pinned-mode {
                box-shadow: 14px 0 35px -5px rgba(0, 0, 0, 0.5);
            }

            .sidebar-hover-mode .sidebar-nav-container,
            .sidebar-pinned-mode .sidebar-nav-container {
                overflow-y: hidden;
            }
            .sidebar-hover-mode:hover .sidebar-nav-container,
            .sidebar-pinned-mode:not(.sidebar-pinned-collapsed) .sidebar-nav-container,
            html.sidebar-pinned-init .sidebar-hover-mode .sidebar-nav-container,
            html.sidebar-pinned-init .sidebar-pinned-mode .sidebar-nav-container {
                overflow-y: auto;
            }

            /* --- Animasi Teks & Label Halus (Fade + Slide + Width Collapse) --- */
            .hide-on-collapse {
                display: inline-block;
                max-width: 0;
                opacity: 0;
                overflow: hidden;
                white-space: nowrap;
                pointer-events: none;
                transform: translateX(-8px);
                transition: opacity 0.18s var(--sidebar-easing),
                            transform 0.22s var(--sidebar-easing),
                            max-width var(--sidebar-duration) var(--sidebar-easing),
                            margin 0.25s var(--sidebar-easing),
                            padding 0.25s var(--sidebar-easing);
                vertical-align: middle;
            }

            .sidebar-hover-mode:hover .hide-on-collapse,
            .sidebar-pinned-mode:not(.sidebar-pinned-collapsed) .hide-on-collapse,
            html.sidebar-pinned-init .sidebar-hover-mode .hide-on-collapse,
            html.sidebar-pinned-init .sidebar-pinned-mode .hide-on-collapse {
                max-width: 220px !important;
                opacity: 1 !important;
                transform: translateX(0) !important;
                pointer-events: auto !important;
                transition: opacity 0.24s var(--sidebar-easing) 0.07s,
                            transform 0.24s var(--sidebar-easing) 0.07s,
                            max-width var(--sidebar-duration) var(--sidebar-easing);
            }

            /* Judul Grup Navigasi (Menu Utama, Administrasi ERP, Sistem) */
            div.hide-on-collapse {
                display: block;
                transition: max-height var(--sidebar-duration) var(--sidebar-easing),
                            opacity 0.2s var(--sidebar-easing),
                            padding var(--sidebar-duration) var(--sidebar-easing),
                            margin var(--sidebar-duration) var(--sidebar-easing);
            }

            .sidebar-hover-mode:not(:hover) div.hide-on-collapse,
            .sidebar-pinned-mode.sidebar-pinned-collapsed div.hide-on-collapse {
                max-height: 0 !important;
                opacity: 0 !important;
                padding-top: 0 !important;
                padding-bottom: 0 !important;
                margin-top: 0 !important;
                margin-bottom: 0 !important;
                overflow: hidden !important;
            }

            .sidebar-hover-mode:hover div.hide-on-collapse,
            .sidebar-pinned-mode:not(.sidebar-pinned-collapsed) div.hide-on-collapse,
            html.sidebar-pinned-init .sidebar-hover-mode div.hide-on-collapse,
            html.sidebar-pinned-init .sidebar-pinned-mode div.hide-on-collapse {
                max-height: 3rem !important;
                opacity: 1 !important;
            }

            /* Saat unpinned dan tidak dihover, tutup dropdown dengan transisi halus */
            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .dropdown-content,
            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .sub-dropdown-content {
                max-height: 0 !important;
                opacity: 0 !important;
                overflow: hidden !important;
                transition: max-height 0.25s var(--sidebar-easing), opacity 0.2s ease !important;
            }

            /* --- Refinement Header Sidebar Mode Mini (Collapsed ~84px) --- */
            .sidebar-header-bg {
                transition: padding var(--sidebar-duration) var(--sidebar-easing);
            }

            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .sidebar-header-bg {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
                justify-content: center !important;
            }

            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .sidebar-header-bg > div:first-child {
                width: 100% !important;
                justify-content: center !important;
                margin: 0 auto !important;
            }

            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .sidebar-header-bg .sidebar-logo-box {
                margin: 0 auto !important;
            }

            .sidebar-header-bg #pinSidebarBtn {
                transition: opacity 0.2s var(--sidebar-easing),
                            transform 0.2s var(--sidebar-easing),
                            max-width var(--sidebar-duration) var(--sidebar-easing);
            }

            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .sidebar-header-bg #pinSidebarBtn {
                opacity: 0 !important;
                max-width: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                pointer-events: none !important;
                transform: scale(0.8) !important;
                overflow: hidden !important;
            }

            /* --- Kotak Ikon Seragam (38px x 38px) dengan Pergerakan Halus --- */
            .sidebar-icon-box {
                width: 38px !important;
                height: 38px !important;
                min-width: 38px !important;
                max-width: 38px !important;
                min-height: 38px !important;
                max-height: 38px !important;
                border-radius: 0.75rem !important; /* rounded-xl */
                flex-shrink: 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            }

            /* Transisi Padding Menu Item yang Mulus */
            .sidebar-nav-container a,
            .sidebar-nav-container .dropdown-btn {
                transition: padding var(--sidebar-duration) var(--sidebar-easing),
                            background-color 0.2s ease,
                            border-color 0.2s ease,
                            box-shadow 0.2s ease;
            }

            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .sidebar-nav-container {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .sidebar-nav-container a,
            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .sidebar-nav-container .dropdown-btn {
                padding-left: 0 !important;
                padding-right: 0 !important;
                justify-content: center !important;
                width: 100% !important;
                margin: 0 auto !important;
                background: transparent !important;
                border-color: transparent !important;
                box-shadow: none !important;
            }

            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .sidebar-nav-container a > div:first-of-type,
            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .sidebar-nav-container .dropdown-btn > div:first-of-type {
                width: auto !important;
                justify-content: center !important;
                margin: 0 auto !important;
            }

            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .sidebar-icon-box {
                width: 38px !important;
                height: 38px !important;
                min-width: 38px !important;
                max-width: 38px !important;
                min-height: 38px !important;
                max-height: 38px !important;
                margin: 0 auto !important;
            }

            /* Hilangkan margin space-x saat mode kuncup agar ikon tidak terdorong */
            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .hide-on-collapse {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            /* Indikator kiri saat mode mini */
            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) .sidebar-indicator-active {
                left: 0 !important;
                width: 4px !important;
                border-radius: 0 9999px 9999px 0 !important;
            }

            /* --- Refinement Footer Mode Mini yang Halus --- */
            #sidebarApp > div:last-child {
                transition: padding var(--sidebar-duration) var(--sidebar-easing);
            }

            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) #sidebarApp > div:last-child {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) #sidebarApp > div:last-child > div:first-child,
            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) #sidebarApp > div:last-child button {
                padding-left: 0 !important;
                padding-right: 0 !important;
                justify-content: center !important;
                width: 100% !important;
                margin: 0 auto !important;
            }

            :is(.sidebar-hover-mode:not(:hover), .sidebar-pinned-mode.sidebar-pinned-collapsed) #sidebarApp > div:last-child .sidebar-icon-box {
                width: 38px !important;
                height: 38px !important;
                min-width: 38px !important;
                max-width: 38px !important;
                min-height: 38px !important;
                max-height: 38px !important;
                margin: 0 auto !important;
            }
        }

        /* --- Solusi Anti-Miring (Anti Right-Shift Saat Terpilih) --- */
        .sidebar-indicator-active {
            pointer-events: none;
        }

        .sidebar-indicator-active + div,
        .sidebar-indicator-active ~ div,
        .sidebar-nav-container a > div:first-of-type,
        .sidebar-nav-container .dropdown-btn > div:first-of-type,
        .sidebar-nav-container .dropdown-btn > div:first-of-type > div:first-child {
            margin-left: 0 !important;
        }

        /* --- Styling Ikon Aktif & Inaktif yang Bersih dan Elegan --- */
        #sidebarApp .sidebar-icon-active {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%) !important;
            color: #ffffff !important;
            border-color: transparent !important;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35) !important;
        }
        #sidebarApp .sidebar-icon-active i {
            color: #ffffff !important;
        }

        html.dark #sidebarApp .sidebar-icon-active {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%) !important;
            color: #ffffff !important;
            border-color: transparent !important;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.45) !important;
        }
        html.dark #sidebarApp .sidebar-icon-active i {
            color: #ffffff !important;
        }

        /* Animasi Transisi Halaman Halus */
        @keyframes pageFadeSlideIn {
            0% {
                opacity: 0;
                transform: translateY(6px);
            }
            100% {
                opacity: 1;
                transform: none;
            }
        }

        .page-transition-enter {
            animation: pageFadeSlideIn 0.24s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Animasi Overlay Berbasis Logo */
        @keyframes logoBreathe {
            0%, 100% {
                transform: scale(1);
                filter: drop-shadow(0 0 10px rgba(14, 165, 233, 0.4));
            }
            50% {
                transform: scale(1.06);
                filter: drop-shadow(0 0 20px rgba(6, 182, 212, 0.7));
            }
        }

        @keyframes ambientPulse {
            0%, 100% {
                opacity: 0.3;
                transform: scale(0.92);
            }
            50% {
                opacity: 0.75;
                transform: scale(1.1);
            }
        }

        @keyframes spinSmooth {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes spinReverseSlow {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
        }

        .animate-logo-breathe {
            animation: logoBreathe 2s ease-in-out infinite;
        }

        .animate-ambient-pulse {
            animation: ambientPulse 2.5s ease-in-out infinite;
        }

        .animate-spin-smooth {
            animation: spinSmooth 1.4s linear infinite;
        }

        .animate-spin-reverse-slow {
            animation: spinReverseSlow 3s linear infinite;
        }

        /* Turbo Progress Bar */
        .turbo-progress-bar {
            height: 3px !important;
            background: linear-gradient(90deg, #0284c7, #06b6d4, #38bdf8) !important;
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.8), 0 0 4px rgba(6, 182, 212, 0.6) !important;
            z-index: 99999 !important;
            border-radius: 0 9999px 9999px 0;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen text-slate-800 dark:text-slate-100 flex overflow-hidden transition-colors duration-200">

    {{-- Overlay Loading / Transisi Berbasis Logo Aplikasi --}}
    <div id="pageLoadingOverlay"
        class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-slate-950/75 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300 hidden select-none"
        aria-hidden="true">
        
        <div class="relative flex items-center justify-center">
            {{-- Ambient Aura Glow Khas Umbulan --}}
            <div class="absolute w-36 h-36 rounded-full bg-gradient-to-tr from-sky-500/35 to-cyan-400/35 blur-2xl animate-ambient-pulse"></div>

            {{-- Cincin Pemutar Luar (Spinner Ring) --}}
            <div class="absolute w-24 h-24 rounded-full border-2 border-transparent border-t-cyan-400 border-r-sky-500 animate-spin-smooth"></div>

            {{-- Cincin Pemutar Dalam Aksen Halus --}}
            <div class="absolute w-20 h-20 rounded-full border border-sky-400/25 border-dashed animate-spin-reverse-slow"></div>

            {{-- Wadah Logo Putih Elegan --}}
            <div class="relative w-16 h-16 rounded-2xl bg-white p-2 shadow-2xl shadow-cyan-500/30 border border-white/80 flex items-center justify-center animate-logo-breathe">
                <img src="{{ asset('images/favicon.png') }}"
                    alt="Logo Umbulan"
                    class="w-full h-full object-contain drop-shadow-sm">
            </div>
        </div>

        {{-- Indikator Teks Memuat Halaman --}}
        <div class="mt-6 flex flex-col items-center text-center">
            <span class="text-xs font-bold tracking-wider text-slate-100 uppercase drop-shadow-sm">
                META ADHYA TIRTA UMBULAN
            </span>
            <div class="mt-1 flex items-center gap-1.5 text-[11px] font-medium text-cyan-300/90">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span>
                <span>Memuat halaman...</span>
            </div>
        </div>
    </div>

    @php
        $authUser     = Auth::user();
        $userLevel    = (int)($authUser->level ?? 3);
        $isLevel1     = $userLevel === 1;
        $isLevel1Or2  = $userLevel <= 2;
        $hasAccess    = $isLevel1Or2;
        $isAdminRole  = $isLevel1Or2;
    @endphp

    {{-- Sidebar Navigasi Utama --}}
    <aside id="sidebarApp" class="sidebar-hover-mode bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 flex flex-col h-screen justify-between border-r border-slate-200/90 dark:border-slate-800 shrink-0 z-40 fixed md:relative -translate-x-full md:translate-x-0 shadow-2xl md:shadow-none select-none transition-colors duration-200">
        
        {{-- Ambient top glow background --}}
        <div class="absolute top-0 left-0 right-0 h-48 bg-gradient-to-b from-sky-400/5 via-cyan-400/5 to-transparent dark:from-sky-500/10 dark:via-sky-500/5 dark:to-transparent pointer-events-none"></div>

        <div class="flex flex-col h-full overflow-hidden relative z-10">
            {{-- Header Sidebar & Logo Perusahaan --}}
            <div class="sidebar-header-bg px-4 py-3 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between bg-white/95 dark:bg-slate-900/90 backdrop-blur-md h-20 shrink-0 transition-colors duration-200">
                <div class="z-10 flex items-center space-x-3 overflow-hidden min-w-0">
                    <div class="sidebar-logo-box relative flex items-center justify-center w-11 h-11 rounded-full bg-gradient-to-br from-white via-sky-50 to-cyan-50 dark:from-slate-800 dark:via-slate-800 dark:to-slate-900 p-1 shadow-sm dark:shadow-md dark:shadow-slate-950/40 border border-slate-200/90 dark:border-slate-700/80 dark:ring-1 dark:ring-slate-700 shrink-0 group transition-all overflow-hidden">
                        <img src="{{ asset('images/iconfav.png') }}"
                            alt="Logo META"
                            class="w-full h-full object-contain rounded-full transition-transform duration-300 group-hover:scale-105">
                    </div>

                    <div class="hide-on-collapse min-w-0 flex-1">
                        <div class="flex items-center space-x-1.5">
                            <h2 class="font-extrabold tracking-wide text-xs text-slate-800 dark:text-white leading-tight truncate">
                                META UMBULAN
                            </h2>
                        </div>
                        <div class="flex items-center space-x-1 mt-0.5">
                            <span class="text-[9px] font-bold tracking-widest text-sky-600 dark:text-sky-400 uppercase">TRANSMISI AIR</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-1 shrink-0">
                    {{-- Desktop Pin / Unpin Button (Hanya tampil di Desktop) --}}
                    <button id="pinSidebarBtn"
                        type="button"
                        class="hidden md:flex text-slate-400 hover:text-sky-600 dark:text-slate-400 dark:hover:text-sky-400 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition-all shrink-0 cursor-pointer"
                        title="Sematkan Sidebar (Tetap Terbuka)">
                        <i class="fa-solid fa-thumbtack text-xs"></i>
                    </button>

                    {{-- Mobile Close Button --}}
                    <button id="closeSidebarBtn"
                        type="button"
                        class="md:hidden text-slate-400 hover:text-rose-600 dark:hover:text-rose-300 p-2 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-500/10 border border-transparent hover:border-rose-200 dark:hover:border-rose-500/20 transition-all shrink-0 cursor-pointer"
                        title="Tutup Menu">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Menu Navigasi Sidebar --}}
            <nav class="sidebar-nav-container px-3 py-3 space-y-1.5 flex-1 overflow-y-auto">
                {{-- Group Label: Menu Utama --}}
                <div class="px-2 pt-1 pb-1 hide-on-collapse">
                    <span class="text-[10px] font-bold tracking-wider text-slate-400 dark:text-slate-400 uppercase flex items-center gap-2">
                        <span>Menu Utama</span>
                        <span class="flex-1 h-px bg-slate-200 dark:bg-slate-800"></span>
                    </span>
                </div>

                {{-- Menu Dashboard --}}
                @php $isDashboardActive = request()->is('dashboard'); @endphp
                <a href="/dashboard"
                    title="Dashboard Operasional"
                    class="sidebar-nav-link relative flex items-center justify-between px-3 py-2.5 rounded-2xl text-[13px] font-medium transition-all duration-200 group {{ $isDashboardActive ? 'sidebar-nav-active bg-sky-500/10 dark:bg-gradient-to-r dark:from-sky-500/20 dark:via-sky-500/10 dark:to-transparent text-sky-700 dark:text-sky-200 font-bold dark:font-semibold border border-sky-300/80 dark:border-sky-500/30 shadow-xs dark:shadow-md dark:shadow-slate-950/40' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/90 dark:hover:bg-slate-800/70 border border-transparent hover:border-slate-200/80 dark:border-transparent dark:hover:border-slate-700/60' }}">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="sidebar-icon-box w-[38px] h-[38px] rounded-xl flex items-center justify-center shrink-0 transition-all duration-200 {{ $isDashboardActive ? 'sidebar-icon-active bg-gradient-to-br from-sky-500 to-cyan-600 text-white shadow-md shadow-sky-500/25 border-transparent' : 'sidebar-icon-inactive bg-slate-100 text-slate-500 group-hover:bg-sky-50 group-hover:text-sky-600 group-hover:border-sky-300 dark:bg-slate-800/80 dark:text-slate-400 dark:group-hover:bg-slate-800 dark:group-hover:text-sky-300 group-hover:scale-105 border border-slate-200/80 dark:border-slate-700/60 dark:group-hover:border-sky-500/30' }}">
                            <i class="fa-solid fa-gauge-high text-sm text-center"></i>
                        </div>
                        <span class="hide-on-collapse">Dashboard</span>
                    </div>
                </a>

                {{-- Menu Fasilitas Cuti --}}
                @php $isCutiActive = request()->is('cuti/*') || request()->is('admin/persetujuan/cuti*'); @endphp
                <div class="dropdown-container" data-active="{{ $isCutiActive ? 'true' : 'false' }}">
                    <button type="button"
                        class="dropdown-btn w-full relative flex items-center justify-between px-3 py-2.5 rounded-2xl text-[13px] font-medium transition-all duration-200 group cursor-pointer {{ $isCutiActive ? 'sidebar-nav-active bg-sky-500/10 dark:bg-gradient-to-r dark:from-sky-500/20 dark:via-sky-500/10 dark:to-transparent text-sky-700 dark:text-sky-200 font-bold dark:font-semibold border border-sky-300/80 dark:border-sky-500/30 shadow-xs dark:shadow-md dark:shadow-slate-950/40' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/90 dark:hover:bg-slate-800/70 border border-transparent hover:border-slate-200/80 dark:border-transparent dark:hover:border-slate-700/60' }}"
                        title="Fasilitas Cuti Karyawan">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="sidebar-icon-box relative w-[38px] h-[38px] rounded-xl flex items-center justify-center shrink-0 transition-all duration-200 {{ $isCutiActive ? 'sidebar-icon-active bg-gradient-to-br from-sky-500 to-cyan-600 text-white shadow-md shadow-sky-500/25 border-transparent' : 'sidebar-icon-inactive bg-slate-100 text-slate-500 group-hover:bg-sky-50 group-hover:text-sky-600 group-hover:border-sky-300 dark:bg-slate-800/80 dark:text-slate-400 dark:group-hover:bg-slate-800 dark:group-hover:text-sky-300 group-hover:scale-105 border border-slate-200/80 dark:border-slate-700/60 dark:group-hover:border-sky-500/30' }}">
                                <i class="fa-solid fa-calendar-check text-sm text-center"></i>
                                @if(isset($jumlahSaranCuti) && $jumlahSaranCuti > 0)
                                    <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 ring-2 ring-white dark:ring-slate-900"></span>
                                    </span>
                                @endif
                            </div>
                            <span class="hide-on-collapse">Fasilitas CUTI</span>
                        </div>
                        <div class="flex items-center space-x-2 hide-on-collapse shrink-0">
                            @if(isset($jumlahSaranCuti) && $jumlahSaranCuti > 0)
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-gradient-to-r from-rose-500 to-pink-500 text-white shadow-xs animate-pulse">
                                    {{ $jumlahSaranCuti }}
                                </span>
                            @endif
                            <i class="fa-solid fa-chevron-down text-xs chevron-icon text-slate-400 group-hover:text-sky-600 dark:text-slate-500 dark:group-hover:text-sky-300 transition-transform"></i>
                        </div>
                    </button>

                    <div class="dropdown-content space-y-1 pl-4 pr-1 mt-1 relative before:absolute before:left-7 before:top-1.5 before:bottom-1.5 before:w-[1.5px] before:bg-gradient-to-b before:from-sky-400/50 before:via-slate-200 before:to-transparent dark:before:from-sky-500/30 dark:before:via-slate-800 dark:before:to-transparent">
                        <a href="/cuti/ajukan"
                            class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs transition-all relative ml-5 group/sub {{ request()->routeIs('cuti.create') || request()->is('cuti/ajukan*') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:font-semibold dark:border-sky-500/30 shadow-2xs' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/60' }}">
                            <span class="w-1.5 h-1.5 rounded-full transition-all shrink-0 {{ request()->routeIs('cuti.create') || request()->is('cuti/ajukan*') ? 'bg-sky-600 shadow-[0_0_6px_#0284c7] dark:bg-sky-400 dark:shadow-[0_0_8px_#38bdf8]' : 'bg-slate-300 group-hover/sub:bg-sky-500 dark:bg-slate-600 dark:group-hover/sub:bg-sky-400' }}"></span>
                            <span class="hide-on-collapse">Ajukan CUTI</span>
                        </a>
                        <a href="/cuti/riwayat"
                            class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs transition-all relative ml-5 group/sub {{ request()->is('cuti/riwayat*') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:font-semibold dark:border-sky-500/30 shadow-2xs' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/60' }}">
                            <span class="w-1.5 h-1.5 rounded-full transition-all shrink-0 {{ request()->is('cuti/riwayat*') ? 'bg-sky-600 shadow-[0_0_6px_#0284c7] dark:bg-sky-400 dark:shadow-[0_0_8px_#38bdf8]' : 'bg-slate-300 group-hover/sub:bg-sky-500 dark:bg-slate-600 dark:group-hover/sub:bg-sky-400' }}"></span>
                            <span class="hide-on-collapse">Riwayat CUTI</span>
                        </a>

                        @if($hasAccess)
                            <a href="{{ route('admin.persetujuan.cuti') }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all relative ml-5 group/sub {{ request()->is('admin/persetujuan/cuti*') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:font-semibold dark:border-sky-500/30 shadow-2xs' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/60' }}">
                                <div class="flex items-center space-x-2.5">
                                    <span class="w-1.5 h-1.5 rounded-full transition-all shrink-0 {{ request()->is('admin/persetujuan/cuti*') ? 'bg-sky-600 shadow-[0_0_6px_#0284c7] dark:bg-sky-400 dark:shadow-[0_0_8px_#38bdf8]' : 'bg-slate-300 group-hover/sub:bg-sky-500 dark:bg-slate-600 dark:group-hover/sub:bg-sky-400' }}"></span>
                                    <span class="hide-on-collapse">Persetujuan CUTI</span>
                                </div>
                                @if(isset($jumlahSaranCuti) && $jumlahSaranCuti > 0)
                                    <span class="hide-on-collapse flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-bold text-white shadow-xs animate-pulse">
                                        {{ $jumlahSaranCuti }}
                                    </span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Menu Fasilitas MPR --}}
                @php $isMprActive = request()->is('mpr/*') || request()->is('admin/persetujuan/mpr*'); @endphp
                <div class="dropdown-container" data-active="{{ $isMprActive ? 'true' : 'false' }}">
                    <button type="button"
                        class="dropdown-btn w-full relative flex items-center justify-between px-3 py-2.5 rounded-2xl text-[13px] font-medium transition-all duration-200 group cursor-pointer {{ $isMprActive ? 'sidebar-nav-active bg-sky-500/10 dark:bg-gradient-to-r dark:from-sky-500/20 dark:via-sky-500/10 dark:to-transparent text-sky-700 dark:text-sky-200 font-bold dark:font-semibold border border-sky-300/80 dark:border-sky-500/30 shadow-xs dark:shadow-md dark:shadow-slate-950/40' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/90 dark:hover:bg-slate-800/70 border border-transparent hover:border-slate-200/80 dark:border-transparent dark:hover:border-slate-700/60' }}"
                        title="Fasilitas Material Purchase Requisition">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="sidebar-icon-box relative w-[38px] h-[38px] rounded-xl flex items-center justify-center shrink-0 transition-all duration-200 {{ $isMprActive ? 'sidebar-icon-active bg-gradient-to-br from-sky-500 to-cyan-600 text-white shadow-md shadow-sky-500/25 border-transparent' : 'sidebar-icon-inactive bg-slate-100 text-slate-500 group-hover:bg-sky-50 group-hover:text-sky-600 group-hover:border-sky-300 dark:bg-slate-800/80 dark:text-slate-400 dark:group-hover:bg-slate-800 dark:group-hover:text-sky-300 group-hover:scale-105 border border-slate-200/80 dark:border-slate-700/60 dark:group-hover:border-sky-500/30' }}">
                                <i class="fa-solid fa-boxes-stacked text-sm text-center"></i>
                                @if(isset($jumlahSaranMpr) && $jumlahSaranMpr > 0)
                                    <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 ring-2 ring-white dark:ring-slate-900"></span>
                                    </span>
                                @endif
                            </div>
                            <span class="hide-on-collapse">Fasilitas MPR</span>
                        </div>
                        <div class="flex items-center space-x-2 hide-on-collapse shrink-0">
                            @if(isset($jumlahSaranMpr) && $jumlahSaranMpr > 0)
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-gradient-to-r from-rose-500 to-pink-500 text-white shadow-xs animate-pulse">
                                    {{ $jumlahSaranMpr }}
                                </span>
                            @endif
                            <i class="fa-solid fa-chevron-down text-xs chevron-icon text-slate-400 group-hover:text-sky-600 dark:text-slate-500 dark:group-hover:text-sky-300 transition-transform"></i>
                        </div>
                    </button>

                    <div class="dropdown-content space-y-1 pl-4 pr-1 mt-1 relative before:absolute before:left-7 before:top-1.5 before:bottom-1.5 before:w-[1.5px] before:bg-gradient-to-b before:from-sky-400/50 before:via-slate-200 before:to-transparent dark:before:from-sky-500/30 dark:before:via-slate-800 dark:before:to-transparent">
                        <a href="/mpr/ajukan"
                            class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs transition-all relative ml-5 group/sub {{ request()->routeIs('mpr.create') || request()->is('mpr/ajukan*') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:font-semibold dark:border-sky-500/30 shadow-2xs' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/60' }}">
                            <span class="w-1.5 h-1.5 rounded-full transition-all shrink-0 {{ request()->routeIs('mpr.create') || request()->is('mpr/ajukan*') ? 'bg-sky-600 shadow-[0_0_6px_#0284c7] dark:bg-sky-400 dark:shadow-[0_0_8px_#38bdf8]' : 'bg-slate-300 group-hover/sub:bg-sky-500 dark:bg-slate-600 dark:group-hover/sub:bg-sky-400' }}"></span>
                            <span class="hide-on-collapse">Ajukan MPR</span>
                        </a>
                        <a href="/mpr/riwayat"
                            class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs transition-all relative ml-5 group/sub {{ request()->is('mpr/riwayat*') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:font-semibold dark:border-sky-500/30 shadow-2xs' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/60' }}">
                            <span class="w-1.5 h-1.5 rounded-full transition-all shrink-0 {{ request()->is('mpr/riwayat*') ? 'bg-sky-600 shadow-[0_0_6px_#0284c7] dark:bg-sky-400 dark:shadow-[0_0_8px_#38bdf8]' : 'bg-slate-300 group-hover/sub:bg-sky-500 dark:bg-slate-600 dark:group-hover/sub:bg-sky-400' }}"></span>
                            <span class="hide-on-collapse">Riwayat MPR</span>
                        </a>

                        @if($hasAccess)
                            <a href="{{ route('admin.persetujuan.mpr') }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all relative ml-5 group/sub {{ request()->is('admin/persetujuan/mpr*') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:font-semibold dark:border-sky-500/30 shadow-2xs' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/60' }}">
                                <div class="flex items-center space-x-2.5">
                                    <span class="w-1.5 h-1.5 rounded-full transition-all shrink-0 {{ request()->is('admin/persetujuan/mpr*') ? 'bg-sky-600 shadow-[0_0_6px_#0284c7] dark:bg-sky-400 dark:shadow-[0_0_8px_#38bdf8]' : 'bg-slate-300 group-hover/sub:bg-sky-500 dark:bg-slate-600 dark:group-hover/sub:bg-sky-400' }}"></span>
                                    <span class="hide-on-collapse">Persetujuan MPR</span>
                                </div>
                                @if(isset($jumlahSaranMpr) && $jumlahSaranMpr > 0)
                                    <span class="hide-on-collapse flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-bold text-white shadow-xs animate-pulse">
                                        {{ $jumlahSaranMpr }}
                                    </span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Menu Fasilitas CAR --}}
                @php $isCarActive = request()->is('car/*') || request()->is('admin/persetujuan/car*'); @endphp
                <div class="dropdown-container" data-active="{{ $isCarActive ? 'true' : 'false' }}">
                    <button type="button"
                        class="dropdown-btn w-full relative flex items-center justify-between px-3 py-2.5 rounded-2xl text-[13px] font-medium transition-all duration-200 group cursor-pointer {{ $isCarActive ? 'sidebar-nav-active bg-sky-500/10 dark:bg-gradient-to-r dark:from-sky-500/20 dark:via-sky-500/10 dark:to-transparent text-sky-700 dark:text-sky-200 font-bold dark:font-semibold border border-sky-300/80 dark:border-sky-500/30 shadow-xs dark:shadow-md dark:shadow-slate-950/40' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/90 dark:hover:bg-slate-800/70 border border-transparent hover:border-slate-200/80 dark:border-transparent dark:hover:border-slate-700/60' }}"
                        title="Fasilitas Cash Advance Requisition">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="sidebar-icon-box relative w-[38px] h-[38px] rounded-xl flex items-center justify-center shrink-0 transition-all duration-200 {{ $isCarActive ? 'sidebar-icon-active bg-gradient-to-br from-sky-500 to-cyan-600 text-white shadow-md shadow-sky-500/25 border-transparent' : 'sidebar-icon-inactive bg-slate-100 text-slate-500 group-hover:bg-sky-50 group-hover:text-sky-600 group-hover:border-sky-300 dark:bg-slate-800/80 dark:text-slate-400 dark:group-hover:bg-slate-800 dark:group-hover:text-sky-300 group-hover:scale-105 border border-slate-200/80 dark:border-slate-700/60 dark:group-hover:border-sky-500/30' }}">
                                <i class="fa-solid fa-file-invoice-dollar text-sm text-center"></i>
                                @if(isset($jumlahSaranCar) && $jumlahSaranCar > 0)
                                    <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 ring-2 ring-white dark:ring-slate-900"></span>
                                    </span>
                                @endif
                            </div>
                            <span class="hide-on-collapse">Fasilitas CAR</span>
                        </div>
                        <div class="flex items-center space-x-2 hide-on-collapse shrink-0">
                            @if(isset($jumlahSaranCar) && $jumlahSaranCar > 0)
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-gradient-to-r from-rose-500 to-pink-500 text-white shadow-xs animate-pulse">
                                    {{ $jumlahSaranCar }}
                                </span>
                            @endif
                            <i class="fa-solid fa-chevron-down text-xs chevron-icon text-slate-400 group-hover:text-sky-600 dark:text-slate-500 dark:group-hover:text-sky-300 transition-transform"></i>
                        </div>
                    </button>

                    <div class="dropdown-content space-y-1 pl-4 pr-1 mt-1 relative before:absolute before:left-7 before:top-1.5 before:bottom-1.5 before:w-[1.5px] before:bg-gradient-to-b before:from-sky-400/50 before:via-slate-200 before:to-transparent dark:before:from-sky-500/30 dark:before:via-slate-800 dark:before:to-transparent">
                        <a href="/car/ajukan"
                            class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs transition-all relative ml-5 group/sub {{ request()->routeIs('car.create') || request()->is('car/ajukan*') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:font-semibold dark:border-sky-500/30 shadow-2xs' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/60' }}">
                            <span class="w-1.5 h-1.5 rounded-full transition-all shrink-0 {{ request()->routeIs('car.create') || request()->is('car/ajukan*') ? 'bg-sky-600 shadow-[0_0_6px_#0284c7] dark:bg-sky-400 dark:shadow-[0_0_8px_#38bdf8]' : 'bg-slate-300 group-hover/sub:bg-sky-500 dark:bg-slate-600 dark:group-hover/sub:bg-sky-400' }}"></span>
                            <span class="hide-on-collapse">Ajukan CAR</span>
                        </a>
                        <a href="/car/riwayat"
                            class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs transition-all relative ml-5 group/sub {{ request()->is('car/riwayat*') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:font-semibold dark:border-sky-500/30 shadow-2xs' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/60' }}">
                            <span class="w-1.5 h-1.5 rounded-full transition-all shrink-0 {{ request()->is('car/riwayat*') ? 'bg-sky-600 shadow-[0_0_6px_#0284c7] dark:bg-sky-400 dark:shadow-[0_0_8px_#38bdf8]' : 'bg-slate-300 group-hover/sub:bg-sky-500 dark:bg-slate-600 dark:group-hover/sub:bg-sky-400' }}"></span>
                            <span class="hide-on-collapse">Riwayat CAR</span>
                        </a>

                        @if($hasAccess)
                            <a href="{{ route('admin.persetujuan.car') }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all relative ml-5 group/sub {{ request()->is('admin/persetujuan/car*') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:font-semibold dark:border-sky-500/30 shadow-2xs' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/60' }}">
                                <div class="flex items-center space-x-2.5">
                                    <span class="w-1.5 h-1.5 rounded-full transition-all shrink-0 {{ request()->is('admin/persetujuan/car*') ? 'bg-sky-600 shadow-[0_0_6px_#0284c7] dark:bg-sky-400 dark:shadow-[0_0_8px_#38bdf8]' : 'bg-slate-300 group-hover/sub:bg-sky-500 dark:bg-slate-600 dark:group-hover/sub:bg-sky-400' }}"></span>
                                    <span class="hide-on-collapse">Persetujuan CAR</span>
                                </div>
                                @if(isset($jumlahSaranCar) && $jumlahSaranCar > 0)
                                    <span class="hide-on-collapse flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-bold text-white shadow-xs animate-pulse">
                                        {{ $jumlahSaranCar }}
                                    </span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Menu Khusus Administrator & Atasan --}}
                @if($isAdminRole)
                @php
                    $isAdminActive = (request()->is('admin/*') || request()->routeIs('admin.*'))
                                    && !request()->is('admin/persetujuan/*');
                    $isDaftarActive = request()->routeIs('admin.karyawan.*') || request()->routeIs('admin.stations.*') || request()->routeIs('admin.role.*');
                    $isRecordActive = request()->is('admin/record/*');
                @endphp
                    <div class="px-2 pt-3 pb-1 hide-on-collapse">
                        <span class="text-[10px] font-bold tracking-wider text-slate-400 dark:text-slate-400 uppercase flex items-center gap-2">
                            <span>Administrasi ERP</span>
                            <span class="flex-1 h-px bg-slate-200 dark:bg-slate-800"></span>
                        </span>
                    </div>

                    <div class="dropdown-container" data-active="{{ $isAdminActive ? 'true' : 'false' }}">
                        <button type="button"
                            class="dropdown-btn w-full relative flex items-center justify-between px-3 py-2.5 rounded-2xl text-[13px] font-medium transition-all duration-200 group cursor-pointer {{ $isAdminActive ? 'sidebar-nav-active bg-sky-500/10 dark:bg-gradient-to-r dark:from-sky-500/20 dark:via-sky-500/10 dark:to-transparent text-sky-700 dark:text-sky-200 font-bold dark:font-semibold border border-sky-300/80 dark:border-sky-500/30 shadow-xs dark:shadow-md dark:shadow-slate-950/40' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/90 dark:hover:bg-slate-800/70 border border-transparent hover:border-slate-200/80 dark:border-transparent dark:hover:border-slate-700/60' }}"
                            title="Panel Administrator Sistem">
                            <div class="flex items-center space-x-3 min-w-0">
                                <div class="sidebar-icon-box w-[38px] h-[38px] rounded-xl flex items-center justify-center shrink-0 transition-all duration-200 {{ $isAdminActive ? 'sidebar-icon-active bg-gradient-to-br from-sky-500 to-cyan-600 text-white shadow-md shadow-sky-500/25 border-transparent' : 'sidebar-icon-inactive bg-slate-100 text-slate-500 group-hover:bg-sky-50 group-hover:text-sky-600 group-hover:border-sky-300 dark:bg-slate-800/80 dark:text-slate-400 dark:group-hover:bg-slate-800 dark:group-hover:text-sky-300 group-hover:scale-105 border border-slate-200/80 dark:border-slate-700/60 dark:group-hover:border-sky-500/30' }}">
                                    <i class="fa-solid fa-shield-halved text-sm text-center"></i>
                                </div>
                                <span class="hide-on-collapse">Administrator</span>
                            </div>
                            <div class="flex items-center space-x-2 hide-on-collapse shrink-0">
                                <i class="fa-solid fa-chevron-down text-xs chevron-icon text-slate-400 group-hover:text-sky-600 dark:text-slate-500 dark:group-hover:text-sky-300 transition-transform"></i>
                            </div>
                        </button>

                        <div class="dropdown-content space-y-1 pl-4 pr-1 mt-1 relative before:absolute before:left-7 before:top-1.5 before:bottom-1.5 before:w-[1.5px] before:bg-gradient-to-b before:from-sky-400/50 before:via-slate-200 before:to-transparent dark:before:from-sky-500/30 dark:before:via-slate-800 dark:before:to-transparent">

                            {{-- Rekap Absensi Harian --}}
                            <a href="{{ Route::has('admin.absensi.index') ? route('admin.absensi.index') : '/admin/absensi' }}"
                                class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs transition-all relative ml-5 group/sub {{ request()->is('admin/absensi*') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:font-semibold dark:border-sky-500/30 shadow-2xs' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/60' }}">
                                <i class="fa-solid fa-clipboard-user text-xs {{ request()->is('admin/absensi*') ? 'text-sky-600 dark:text-sky-400' : 'text-slate-400 group-hover/sub:text-sky-600 dark:text-slate-500 dark:group-hover/sub:text-sky-300' }}"></i>
                                <span class="hide-on-collapse">Rekap Absensi Harian</span>
                            </a>

                            {{-- Sub-Menu Daftar Data Master --}}
                            <div class="sub-dropdown-container" data-active="{{ $isDaftarActive ? 'true' : 'false' }}">
                                <button type="button"
                                    class="sub-dropdown-btn w-[calc(100%-1.25rem)] flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/60 dark:hover:text-slate-100 transition-all ml-5 relative cursor-pointer">
                                    <div class="flex items-center space-x-2.5">
                                        <i class="fa-solid fa-database text-xs text-slate-400 dark:text-slate-500"></i>
                                        <span class="hide-on-collapse">Data Master</span>
                                    </div>
                                    <div class="hide-on-collapse flex items-center shrink-0">
                                        <i class="fa-solid fa-chevron-down text-xs sub-chevron-icon text-slate-400 dark:text-slate-500 transition-transform"></i>
                                    </div>
                                </button>
                                <div class="sub-dropdown-content space-y-1 pl-3 mt-1 border-l border-sky-300/50 dark:border-slate-700/80 ml-8">
                                    <a href="{{ route('admin.role.index') }}"
                                        class="flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->routeIs('admin.role.*') ? 'text-sky-700 font-bold bg-sky-50 border border-sky-200 dark:text-sky-300 dark:font-semibold dark:bg-sky-500/15 dark:border-sky-500/30' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/60 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/50' }}">
                                        <span class="w-1 h-1 rounded-full {{ request()->routeIs('admin.role.*') ? 'bg-sky-600 dark:bg-sky-400' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                                        <span class="hide-on-collapse">Role Jabatan</span>
                                    </a>
                                    <a href="{{ route('admin.karyawan.index') }}"
                                        class="flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->routeIs('admin.karyawan.*') ? 'text-sky-700 font-bold bg-sky-50 border border-sky-200 dark:text-sky-300 dark:font-semibold dark:bg-sky-500/15 dark:border-sky-500/30' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/60 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/50' }}">
                                        <span class="w-1 h-1 rounded-full {{ request()->routeIs('admin.karyawan.*') ? 'bg-sky-600 dark:bg-sky-400' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                                        <span class="hide-on-collapse">Data Karyawan</span>
                                    </a>
                                    <a href="{{ route('admin.stations.index') }}"
                                        class="flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->routeIs('admin.stations.*') ? 'text-sky-700 font-bold bg-sky-50 border border-sky-200 dark:text-sky-300 dark:font-semibold dark:bg-sky-500/15 dark:border-sky-500/30' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/60 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/50' }}">
                                        <span class="w-1 h-1 rounded-full {{ request()->routeIs('admin.stations.*') ? 'bg-sky-600 dark:bg-sky-400' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                                        <span class="hide-on-collapse">Stasiun Transmisi</span>
                                    </a>
                                </div>
                            </div>

                            {{-- Sub-Menu Rekap Record --}}
                            <div class="sub-dropdown-container" data-active="{{ $isRecordActive ? 'true' : 'false' }}">
                                <button type="button"
                                    class="sub-dropdown-btn w-[calc(100%-1.25rem)] flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/60 dark:hover:text-slate-100 transition-all ml-5 relative cursor-pointer">
                                    <div class="flex items-center space-x-2.5">
                                        <i class="fa-solid fa-clock-rotate-left text-xs text-slate-400 dark:text-slate-500"></i>
                                        <span class="hide-on-collapse">Arsip Record</span>
                                    </div>
                                    <div class="hide-on-collapse flex items-center shrink-0">
                                        <i class="fa-solid fa-chevron-down text-xs sub-chevron-icon text-slate-400 dark:text-slate-500 transition-transform"></i>
                                    </div>
                                </button>
                                <div class="sub-dropdown-content space-y-1 pl-3 mt-1 border-l border-sky-300/50 dark:border-slate-700/80 ml-8">
                                    <a href="{{ route('admin.record.cuti') }}"
                                        class="flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->is('admin/record/cuti*') ? 'text-sky-700 font-bold bg-sky-50 border border-sky-200 dark:text-sky-300 dark:font-semibold dark:bg-sky-500/15 dark:border-sky-500/30' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/60 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/50' }}">
                                        <span class="w-1 h-1 rounded-full {{ request()->is('admin/record/cuti*') ? 'bg-sky-600 dark:bg-sky-400' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                                        <span class="hide-on-collapse">Record Cuti</span>
                                    </a>
                                    <a href="{{ route('admin.record.mpr') }}"
                                        class="flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->is('admin/record/mpr*') ? 'text-sky-700 font-bold bg-sky-50 border border-sky-200 dark:text-sky-300 dark:font-semibold dark:bg-sky-500/15 dark:border-sky-500/30' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/60 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/50' }}">
                                        <span class="w-1 h-1 rounded-full {{ request()->is('admin/record/mpr*') ? 'bg-sky-600 dark:bg-sky-400' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                                        <span class="hide-on-collapse">Record MPR</span>
                                    </a>
                                    <a href="{{ route('admin.record.car') }}"
                                        class="flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->is('admin/record/car*') ? 'text-sky-700 font-bold bg-sky-50 border border-sky-200 dark:text-sky-300 dark:font-semibold dark:bg-sky-500/15 dark:border-sky-500/30' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/60 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800/50' }}">
                                        <span class="w-1 h-1 rounded-full {{ request()->is('admin/record/car*') ? 'bg-sky-600 dark:bg-sky-400' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                                        <span class="hide-on-collapse">Record CAR</span>
                                    </a>
                                </div>
                            </div>

                            {{-- Pengaturan WhatsApp Gateway --}}
                            <a href="{{ route('admin.whatsapp.index') }}"
                                class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs transition-all relative ml-5 group/sub {{ request()->is('admin/whatsapp*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:font-semibold dark:border-emerald-500/30' : 'text-slate-500 hover:text-emerald-700 hover:bg-emerald-50/60 dark:text-slate-400 dark:hover:text-emerald-300 dark:hover:bg-emerald-950/30' }}">
                                <i class="fa-brands fa-whatsapp text-xs text-emerald-500 dark:text-emerald-400"></i>
                                <span class="hide-on-collapse">WhatsApp Gateway</span>
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Group Label: Konfigurasi --}}
                <div class="px-2 pt-3 pb-1 hide-on-collapse">
                    <span class="text-[10px] font-bold tracking-wider text-slate-400 dark:text-slate-400 uppercase flex items-center gap-2">
                        <span>Sistem</span>
                        <span class="flex-1 h-px bg-slate-200 dark:bg-slate-800"></span>
                    </span>
                </div>

                {{-- Menu Pengaturan Akun --}}
                @php $isAccountActive = request()->routeIs('account.*'); @endphp
                <a href="{{ route('account.index') }}"
                    title="Pengaturan Profil & Akun"
                    class="sidebar-nav-link relative flex items-center justify-between px-3 py-2.5 rounded-2xl text-[13px] font-medium transition-all duration-200 group {{ $isAccountActive ? 'sidebar-nav-active bg-sky-500/10 dark:bg-gradient-to-r dark:from-sky-500/20 dark:via-sky-500/10 dark:to-transparent text-sky-700 dark:text-sky-200 font-bold dark:font-semibold border border-sky-300/80 dark:border-sky-500/30 shadow-xs dark:shadow-md dark:shadow-slate-950/40' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/90 dark:hover:bg-slate-800/70 border border-transparent hover:border-slate-200/80 dark:border-transparent dark:hover:border-slate-700/60' }}">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="sidebar-icon-box w-[38px] h-[38px] rounded-xl flex items-center justify-center shrink-0 transition-all duration-200 {{ $isAccountActive ? 'sidebar-icon-active bg-gradient-to-br from-sky-500 to-cyan-600 text-white shadow-md shadow-sky-500/25 border-transparent' : 'sidebar-icon-inactive bg-slate-100 text-slate-500 group-hover:bg-sky-50 group-hover:text-sky-600 group-hover:border-sky-300 dark:bg-slate-800/80 dark:text-slate-400 dark:group-hover:bg-slate-800 dark:group-hover:text-sky-300 group-hover:scale-105 border border-slate-200/80 dark:border-slate-700/60 dark:group-hover:border-sky-500/30' }}">
                            <i class="fa-solid fa-user-gear text-sm text-center"></i>
                        </div>
                        <span class="hide-on-collapse">Pengaturan Akun</span>
                    </div>
                </a>
            </nav>
        </div>

        {{-- Footer Sidebar: User / Station Status Widget & Tombol Keluar --}}
        <div class="p-3 border-t border-slate-200/90 dark:border-slate-800 bg-white/95 dark:bg-slate-900/90 backdrop-blur-md shrink-0 space-y-2 relative z-10 transition-colors duration-200">
            {{-- Tombol Pemicu Logout --}}
            <button type="button"
                onclick="openLogoutModal()"
                title="Keluar dari Aplikasi"
                class="w-full flex items-center space-x-3 px-2.5 py-2 rounded-xl text-xs font-semibold text-rose-600 dark:text-rose-400/90 hover:text-rose-700 dark:hover:text-rose-200 hover:bg-rose-50 dark:hover:bg-rose-500/15 border border-transparent hover:border-rose-200/80 dark:hover:border-rose-500/25 transition-all duration-200 group cursor-pointer">
                <div class="sidebar-icon-box w-[38px] h-[38px] rounded-xl bg-rose-50 text-rose-600 group-hover:bg-rose-100 border border-rose-200/80 dark:bg-rose-500/10 dark:text-rose-400 dark:group-hover:bg-rose-500/20 dark:group-hover:text-rose-200 dark:border-rose-500/20 flex items-center justify-center shrink-0 transition-all">
                    <i class="fa-solid fa-arrow-right-from-bracket text-sm text-center transition-transform group-hover:translate-x-1"></i>
                </div>
                <span class="hide-on-collapse">Keluar Aplikasi</span>
            </button>
        </div>
    </aside>

    {{-- Backdrop Sidebar Mobile --}}
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 opacity-0 pointer-events-none md:hidden"></div>

    {{-- Area Konten Utama --}}
    <div class="flex-1 flex flex-col h-screen overflow-y-auto bg-slate-50 dark:bg-slate-950 transition-colors duration-200">
        {{-- Header Utama dengan Jam Digital & Switcher Tema --}}
        <header class="bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 px-6 py-3 flex justify-between items-center sticky top-0 z-20 shadow-xs transition-colors">
            <div class="flex items-center space-x-3">
                <button id="toggleSidebarBtn" class="text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white p-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 active:scale-95 transition-all shadow-xs cursor-pointer" title="Buka / Tutup Sidebar">
                    <i class="fa-solid fa-bars-staggered text-lg"></i>
                </button>
                <div>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tempat Kerja,</p>
                    <h1 class="text-base font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ Auth::user()->station->name ?? 'Stasiun Umbulan' }}</h1>
                </div>
            </div>

            <div class="fixed bottom-4 right-4 z-20 sm:static sm:right-auto flex flex-col items-center justify-center text-center px-4 py-1.5 sm:px-5 bg-slate-900/90 sm:bg-slate-50 dark:sm:bg-slate-800 text-white sm:text-slate-800 dark:sm:text-slate-100 backdrop-blur-md sm:backdrop-blur-none border border-slate-700/50 sm:border-slate-200/60 dark:sm:border-slate-700 rounded-2xl shadow-xl sm:shadow-inner transition-all duration-300">
                <div class="flex items-center space-x-2 text-sky-400 sm:text-sky-600 dark:sm:text-sky-400 font-mono font-black text-xs sm:text-base md:text-lg tracking-wider">
                    <i class="fa-solid fa-clock text-[10px] sm:text-xs text-sky-400 sm:text-sky-500"></i>
                    <span id="headerDigitalClock">00:00:00 WIB</span>
                </div>
                <span id="headerDateDisplay" class="text-[9px] sm:text-[10px] text-slate-300 sm:text-slate-500 dark:sm:text-slate-400 font-medium tracking-tight">--</span>
            </div>

            <div class="flex items-center space-x-3">
                @auth
                {{-- TOMBOL THEME SWITCHER DARK / LIGHT MODE ELEGAN --}}
                <button type="button"
                    id="themeToggleBtn"
                    onclick="toggleThemeMode()"
                    class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 text-amber-500 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 flex items-center justify-center transition-all cursor-pointer shadow-xs active:scale-95"
                    title="Ganti Mode Tema">
                    <i id="themeToggleIcon" class="fa-solid fa-sun text-amber-500 text-sm"></i>
                </button>
                <script>
                    (function() {
                        var icon = document.getElementById('themeToggleIcon');
                        var btn = document.getElementById('themeToggleBtn');
                        if (document.documentElement.classList.contains('dark')) {
                            if (icon) icon.className = 'fa-solid fa-moon text-slate-300 text-sm';
                            if (btn) btn.setAttribute('title', 'Beralih ke Mode Terang (Light Mode)');
                        } else {
                            if (icon) icon.className = 'fa-solid fa-sun text-amber-500 text-sm';
                            if (btn) btn.setAttribute('title', 'Beralih ke Mode Gelap (Dark Mode)');
                        }
                    })();
                </script>
                @endauth

                {{-- PROFILE DROPDOWN NAVBAR --}}
                <div class="relative" id="navbarProfileDropdownContainer">
                    <button type="button"
                        id="navbarProfileDropdownBtn"
                        class="flex items-center space-x-2 sm:space-x-3 cursor-pointer group p-1.5 rounded-2xl hover:bg-slate-100/80 dark:hover:bg-slate-800/80 transition-all border border-transparent hover:border-slate-200 dark:hover:border-slate-700"
                        title="Menu Profil Pengguna"
                        aria-expanded="false"
                        aria-haspopup="true">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                                {{ Auth::user()->role->role_name ?? 'USER' }}
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold shadow-md shadow-sky-100 overflow-hidden border border-slate-100 dark:border-slate-700 shrink-0 group-hover:ring-2 group-hover:ring-sky-500 transition-all">
                            @if(Auth::user()->profile_photo)
                                <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="User" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            @endif
                        </div>
                        <i class="fa-solid fa-chevron-down text-[11px] text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-transform duration-200 navbar-profile-chevron"></i>
                    </button>

                    {{-- MENU DROPDOWN MELAYANG (FLOATING PANEL) --}}
                    <div id="navbarProfileDropdownMenu"
                        class="hidden absolute right-0 mt-2 w-72 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700/80 py-2 z-50 transition-all duration-200">
                        {{-- User Header Info --}}
                        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700/60 flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold shadow-sm overflow-hidden shrink-0">
                                @if(Auth::user()->profile_photo)
                                    <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="User" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                                    {{ Auth::user()->role->role_name ?? 'USER' }}
                                </span>
                            </div>
                        </div>

                        {{-- Stasiun Info --}}
                        <div class="px-4 py-2 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700/60 text-xs">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Penempatan Stasiun</span>
                            <span class="font-semibold text-sky-600 dark:text-sky-400">{{ Auth::user()->station->name ?? 'Stasiun Umbulan' }}</span>
                        </div>

                        {{-- Dropdown Action Links --}}
                        <div class="py-1.5 px-1 space-y-0.5">
                            <button type="button"
                                onclick="closeNavbarProfileDropdown(); openProfileDetailModal();"
                                class="w-full flex items-center space-x-3 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-sky-600 dark:hover:text-sky-400 rounded-xl transition-colors cursor-pointer text-left">
                                <div class="w-7 h-7 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-id-card text-xs"></i>
                                </div>
                                <span>Detail Profil Lengkap</span>
                            </button>

                            <a href="{{ route('account.index') }}"
                                onclick="closeNavbarProfileDropdown()"
                                class="flex items-center space-x-3 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-sky-600 dark:hover:text-sky-400 rounded-xl transition-colors">
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-gear text-xs"></i>
                                </div>
                                <span>Pengaturan Akun</span>
                            </a>
                        </div>

                        {{-- Logout Trigger Button --}}
                        <div class="pt-1.5 mt-1 border-t border-slate-100 dark:border-slate-700/60 px-1">
                            <button type="button"
                                onclick="closeNavbarProfileDropdown(); openLogoutModal();"
                                class="w-full flex items-center space-x-3 px-3 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition-colors cursor-pointer">
                                <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                                </div>
                                <span>Keluar Aplikasi</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main id="mainContent" class="p-6 max-w-7xl w-full mx-auto pb-20 sm:pb-6 page-transition-enter">
            {{-- ALERT BANNER: WHATSAPP GATEWAY BELUM TERHUBUNG (KHUSUS LEVEL 1 / ADMIN) --}}
            @if($isAdminRole && !request()->routeIs('admin.whatsapp.*'))
                @php
                    $waStatusData = \App\Services\WhatsAppService::getStatusCached();
                @endphp
                @if(($waStatusData['status'] ?? 'disconnected') !== 'connected')
                    <div class="mb-6 p-4.5 bg-gradient-to-r from-amber-500/15 via-rose-500/10 to-amber-500/15 border border-amber-300/80 dark:border-amber-700/60 rounded-2xl shadow-sm backdrop-blur-md flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all">
                        <div class="flex items-start sm:items-center space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-xl shrink-0 shadow-md shadow-amber-500/20 mt-0.5 sm:mt-0">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-sm text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <span>⚠️ WhatsApp Gateway Belum Terhubung</span>
                                    <span class="text-[10px] uppercase font-black px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        {{ strtoupper($waStatusData['status'] ?? 'TERPUTUS') }}
                                    </span>
                                </h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5 leading-relaxed">
                                    Sistem tidak dapat mengirim OTP & notifikasi persetujuan. Sambungkan perangkat sekarang.
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('admin.whatsapp.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition-all shrink-0 whitespace-nowrap active:scale-95">
                            <i class="fa-solid fa-qrcode"></i>
                            <span>Sambungkan Perangkat</span>
                        </a>
                    </div>
                @endif
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Handler Skrip JavaScript --}}
    <script>
        function updateHeaderClock() {
            const now = new Date();
            const optionsDate = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };

            const dateString = now.toLocaleDateString('id-ID', optionsDate);
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            const clockElement = document.getElementById('headerDigitalClock');
            const dateElement = document.getElementById('headerDateDisplay');

            if (clockElement) clockElement.innerText = `${hours}:${minutes}:${seconds} WIB`;
            if (dateElement) dateElement.innerText = dateString;
        }

        function toggleThemeMode() {
            @auth
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            syncThemeIcon();
            window.dispatchEvent(new CustomEvent('themeChanged', { detail: { isDark } }));
            @endauth
        }

        function syncThemeIcon() {
            const icon = document.getElementById('themeToggleIcon');
            const btn = document.getElementById('themeToggleBtn');
            if (!icon) return;
            const isDark = document.documentElement.classList.contains('dark');
            if (isDark) {
                icon.className = 'fa-solid fa-moon text-slate-300 text-sm';
                if (btn) btn.setAttribute('title', 'Beralih ke Mode Terang (Light Mode)');
            } else {
                icon.className = 'fa-solid fa-sun text-amber-500 text-sm';
                if (btn) btn.setAttribute('title', 'Beralih ke Mode Gelap (Dark Mode)');
            }
        }
        window.syncThemeIcon = syncThemeIcon;
        window.toggleThemeMode = toggleThemeMode;

        {{-- DROPDOWN NAVBAR PROFIL --}}
        function toggleNavbarProfileDropdown(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const menu = document.getElementById('navbarProfileDropdownMenu');
            const btn = document.getElementById('navbarProfileDropdownBtn');
            if (!menu || !btn) return;

            const isHidden = menu.classList.contains('hidden');
            if (isHidden) {
                openNavbarProfileDropdown();
            } else {
                closeNavbarProfileDropdown();
            }
        }

        function openNavbarProfileDropdown() {
            const menu = document.getElementById('navbarProfileDropdownMenu');
            const btn = document.getElementById('navbarProfileDropdownBtn');
            if (!menu || !btn) return;

            menu.classList.remove('hidden');
            btn.classList.add('navbar-profile-open');
            btn.setAttribute('aria-expanded', 'true');
        }

        function closeNavbarProfileDropdown() {
            const menu = document.getElementById('navbarProfileDropdownMenu');
            const btn = document.getElementById('navbarProfileDropdownBtn');
            if (!menu || !btn) return;

            menu.classList.add('hidden');
            btn.classList.remove('navbar-profile-open');
            btn.setAttribute('aria-expanded', 'false');
        }

        {{-- DROPDOWN SIDEBAR (ACCORDION) --}}
        function toggleDropdown(container) {
            if (!container) return;
            const sidebar = document.getElementById("sidebarApp");

            // Jika sidebar dalam mode pin tapi sedang kuncup/tertutup manual:
            if (sidebar && sidebar.classList.contains("sidebar-pinned-collapsed")) {
                // 1. Otomatis buka lebar sidebar penuh
                sidebar.classList.remove("sidebar-pinned-collapsed");
                try {
                    localStorage.setItem("umbulan_sidebar_manual_collapsed", "false");
                } catch (err) {}

                // 2. Tutup dropdown lain agar fokus pada menu yang baru diklik
                document.querySelectorAll('.dropdown-container.dropdown-open').forEach(c => {
                    if (c !== container) {
                        c.classList.remove('dropdown-open');
                    }
                });

                // 3. Pastikan dropdown yang diklik terbuka
                container.classList.add('dropdown-open');
                return;
            }

            // Normal toggle saat sidebar sudah dalam keadaan terbuka lebar
            const isOpen = container.classList.contains('dropdown-open');
            if (isOpen) {
                container.classList.remove('dropdown-open');
            } else {
                container.classList.add('dropdown-open');
            }
        }

        function toggleSubDropdown(subContainer) {
            if (!subContainer) return;
            const isOpen = subContainer.classList.contains('sub-dropdown-open');
            if (isOpen) {
                subContainer.classList.remove('sub-dropdown-open');
            } else {
                subContainer.classList.add('sub-dropdown-open');
            }
        }

        {{-- SIDEBAR MOBILE & PIN CONTROLS --}}
        function openSidebarMobile() {
            const sidebar = document.getElementById("sidebarApp");
            const backdrop = document.getElementById("sidebarBackdrop");
            if (sidebar && backdrop) {
                sidebar.classList.remove("-translate-x-full");
                sidebar.classList.add("translate-x-0");
                backdrop.classList.remove("pointer-events-none", "opacity-0");
                backdrop.classList.add("opacity-100");
            }
        }

        function closeSidebarMobile() {
            const sidebar = document.getElementById("sidebarApp");
            const backdrop = document.getElementById("sidebarBackdrop");
            if (sidebar && backdrop) {
                sidebar.classList.remove("translate-x-0");
                sidebar.classList.add("-translate-x-full");
                backdrop.classList.remove("opacity-100");
                backdrop.classList.add("opacity-0", "pointer-events-none");
            }
        }

        function applySidebarMode(isPinned, isCollapsed) {
            const sidebar = document.getElementById("sidebarApp");
            if (!sidebar) return;

            if (isPinned) {
                // Mode Pin Aktif: Matikan mode hover total!
                sidebar.classList.remove("sidebar-hover-mode");
                sidebar.classList.add("sidebar-pinned-mode", "sidebar-pinned");

                if (isCollapsed) {
                    sidebar.classList.add("sidebar-pinned-collapsed");
                } else {
                    sidebar.classList.remove("sidebar-pinned-collapsed");
                }
                updatePinButtonState(true);
            } else {
                // Mode Pin Nonaktif: Aktifkan mode hover dinamis!
                sidebar.classList.remove("sidebar-pinned-mode", "sidebar-pinned-collapsed", "sidebar-pinned");
                sidebar.classList.add("sidebar-hover-mode");
                updatePinButtonState(false);
            }
        }

        function toggleSidebarPinned() {
            const sidebar = document.getElementById("sidebarApp");
            if (!sidebar) return;
            const isCurrentlyPinned = sidebar.classList.contains("sidebar-pinned-mode");
            const nextPinned = !isCurrentlyPinned;

            try {
                localStorage.setItem("umbulan_sidebar_pinned", nextPinned ? "true" : "false");
                if (nextPinned) {
                    localStorage.setItem("umbulan_sidebar_manual_collapsed", "false");
                }
            } catch (err) {}

            applySidebarMode(nextPinned, false);
        }

        function toggleSidebarDesktop() {
            const sidebar = document.getElementById("sidebarApp");
            if (!sidebar) return;

            const isPinned = sidebar.classList.contains("sidebar-pinned-mode");
            if (isPinned) {
                // Saat mode pin aktif: buka/tutup manual TANPA merubah status pin
                const isCollapsed = sidebar.classList.toggle("sidebar-pinned-collapsed");
                try {
                    localStorage.setItem("umbulan_sidebar_manual_collapsed", isCollapsed ? "true" : "false");
                } catch (err) {}
            } else {
                // Saat mode hover: klik tombol navbar akan membuka dan menyematkan sidebar
                try {
                    localStorage.setItem("umbulan_sidebar_pinned", "true");
                    localStorage.setItem("umbulan_sidebar_manual_collapsed", "false");
                } catch (err) {}
                applySidebarMode(true, false);
            }
        }

        function updatePinButtonState(isPinned) {
            const pinBtn = document.getElementById("pinSidebarBtn");
            if (!pinBtn) return;
            const icon = pinBtn.querySelector("i");
            if (icon) {
                if (isPinned) {
                    icon.className = "fa-solid fa-thumbtack text-sky-500 dark:text-cyan-400 rotate-45 transition-transform";
                    pinBtn.setAttribute("title", "Lepas Sematan (Aktifkan Mode Hover Melayang)");
                } else {
                    icon.className = "fa-solid fa-thumbtack text-slate-400 transition-transform";
                    pinBtn.setAttribute("title", "Sematkan Sidebar (Kunci Tetap Terbuka)");
                }
            }
        }

        {{-- SINKRONISASI TAMPILAN SAAT NAVIGASI --}}
        function initLayoutHandlers() {
            updateHeaderClock();
            syncThemeIcon();
            closeNavbarProfileDropdown();
            closeSidebarMobile();
            closeLogoutModal();

            // Sinkronisasi status sematan sidebar desktop
            try {
                const savedPinned = localStorage.getItem("umbulan_sidebar_pinned");
                const savedCollapsed = localStorage.getItem("umbulan_sidebar_manual_collapsed");
                const sidebar = document.getElementById("sidebarApp");
                if (sidebar) {
                    const isPinned = (savedPinned === "true" || (savedPinned === null && window.innerWidth >= 1024));
                    const isCollapsed = (savedCollapsed === "true");
                    applySidebarMode(isPinned, isCollapsed);
                }
            } catch (err) {}
            if (document.documentElement.classList.contains("sidebar-pinned-init")) {
                document.documentElement.classList.remove("sidebar-pinned-init");
            }

            // Pemicu animasi transisi konten halaman
            if (typeof window.triggerPageTransition === 'function') {
                window.triggerPageTransition();
            } else {
                const mc = document.getElementById('mainContent');
                if (mc) {
                    mc.classList.remove('page-transition-enter');
                    void mc.offsetWidth;
                    mc.classList.add('page-transition-enter');
                }
            }

            document.querySelectorAll('.dropdown-container[data-active="true"]').forEach(container => {
                container.classList.add('dropdown-open');
            });
            document.querySelectorAll('.sub-dropdown-container[data-active="true"]').forEach(sub => {
                sub.classList.add('sub-dropdown-open');
            });
        }

        if (!window.__umbulanClockInterval) {
            window.__umbulanClockInterval = setInterval(updateHeaderClock, 1000);
        }

        if (!window.__umbulanLayoutEventsBound) {
            window.__umbulanLayoutEventsBound = true;

            document.addEventListener('click', function(e) {
                const navProfileBtn = e.target.closest('#navbarProfileDropdownBtn');
                if (navProfileBtn) {
                    toggleNavbarProfileDropdown(e);
                    return;
                }

                const profileDropdownContainer = document.getElementById('navbarProfileDropdownContainer');
                if (profileDropdownContainer && !profileDropdownContainer.contains(e.target)) {
                    closeNavbarProfileDropdown();
                }

                const dropdownBtn = e.target.closest('.dropdown-btn');
                if (dropdownBtn) {
                    e.preventDefault();
                    const container = dropdownBtn.closest('.dropdown-container');
                    if (container) {
                        toggleDropdown(container);
                    }
                    return;
                }

                const subBtn = e.target.closest('.sub-dropdown-btn');
                if (subBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const subContainer = subBtn.closest('.sub-dropdown-container');
                    if (subContainer) {
                        toggleSubDropdown(subContainer);
                    }
                    return;
                }

                if (e.target.closest('#toggleSidebarBtn')) {
                    e.preventDefault();
                    if (window.innerWidth < 768) {
                        openSidebarMobile();
                    } else {
                        toggleSidebarDesktop();
                    }
                    return;
                }
                if (e.target.closest('#pinSidebarBtn')) {
                    e.preventDefault();
                    toggleSidebarPinned();
                    return;
                }
                if (e.target.closest('#closeSidebarBtn') || (e.target.id === 'sidebarBackdrop')) {
                    e.preventDefault();
                    closeSidebarMobile();
                    return;
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeNavbarProfileDropdown();
                    closeProfileDetailModal();
                    closeSidebarMobile();
                    closeLogoutModal();
                }
            });
        }

        document.addEventListener("DOMContentLoaded", initLayoutHandlers);
        document.addEventListener("turbo:load", initLayoutHandlers);
        document.addEventListener("turbo:render", initLayoutHandlers);
    </script>

    {{-- MODAL POPUP DETAIL AKUN USER --}}
    <div id="profileDetailModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-md transition-opacity duration-300">
        <div id="profileDetailModalCard" class="relative bg-white dark:bg-slate-900 rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200/90 dark:border-slate-800 transform transition-all duration-300 scale-95 opacity-0">

            {{-- HEADER ELEGAN DENGAN DESAIN DEEP NAVY SAPPHIRE METALLIC SMART CARD --}}
            <div class="relative text-white p-6 sm:p-7 overflow-hidden border-b border-sky-400/20"
                 style="background: linear-gradient(135deg, #06152b 0%, #0c2748 45%, #0f365d 75%, #081d36 100%); box-shadow: inset 0 1px 0 0 rgba(255, 255, 255, 0.15);">
                
                <!-- Ambient Subtle Sapphire & Cyan Corner Glow -->
                <div class="absolute -top-12 -right-12 w-64 h-64 rounded-full pointer-events-none opacity-30 blur-2xl"
                     style="background: radial-gradient(circle, rgba(14, 165, 233, 0.4) 0%, rgba(14, 165, 233, 0) 70%);"></div>
                <div class="absolute -bottom-16 -left-16 w-64 h-64 rounded-full pointer-events-none opacity-25 blur-2xl"
                     style="background: radial-gradient(circle, rgba(6, 182, 212, 0.35) 0%, rgba(6, 182, 212, 0) 70%);"></div>

                <!-- Subtle Micro-Geometric Identity Texture -->
                <div class="absolute inset-0 pointer-events-none opacity-[0.04]"
                     style="background-image: radial-gradient(#38bdf8 1px, transparent 1px); background-size: 16px 16px;"></div>

                <!-- Watermark Wave Accent -->
                <div class="absolute -right-8 -bottom-8 pointer-events-none opacity-5 text-sky-300">
                    <i class="fa-solid fa-water text-9xl"></i>
                </div>

                {{-- TOP HEADER BAR --}}
                <div class="relative z-10 flex items-center justify-between gap-3 mb-5">
                    <div class="flex items-center space-x-2.5">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-sky-500/15 text-sky-400 border border-sky-400/30 shadow-xs shadow-sky-500/20">
                            <i class="fa-solid fa-id-card-clip text-xs"></i>
                        </span>
                        <div>
                            <span class="text-[10px] font-mono tracking-widest text-sky-300 font-extrabold uppercase block leading-none">Kartu Digital Karyawan</span>
                            <span class="text-xs text-slate-200 font-bold tracking-tight">PT META Adhya Tirta Umbulan</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Aktif</span>
                        </span>
                        <button type="button" onclick="closeProfileDetailModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition-all cursor-pointer border border-white/15 backdrop-blur-sm focus:outline-none active:scale-95" title="Tutup">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- PROFILE HERO SHOWCASE --}}
                <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-4">
                    <div class="relative shrink-0">
                        <div class="w-20 h-20 rounded-2xl text-white flex items-center justify-center font-black text-2xl shadow-xl overflow-hidden ring-4 ring-sky-400/25 border-2 border-sky-300/40"
                             style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                            @if(Auth::user()->profile_photo)
                                <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="Foto {{ Auth::user()->name }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            @endif
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-emerald-500 ring-2 ring-slate-900 flex items-center justify-center text-[10px] text-white shadow-md" title="Akun Terverifikasi & Aktif">
                            <i class="fa-solid fa-check text-[10px]"></i>
                        </span>
                    </div>

                    <div class="text-center sm:text-left space-y-2 flex-1 min-w-0">
                        <div>
                            <h4 class="font-extrabold text-white text-xl sm:text-2xl leading-tight tracking-tight drop-shadow-sm truncate">{{ Auth::user()->name }}</h4>
                            <p class="text-xs text-sky-200/90 font-medium flex items-center justify-center sm:justify-start gap-1.5 mt-0.5 truncate">
                                <i class="fa-regular fa-envelope text-[11px] text-sky-400"></i>
                                <span class="truncate">{{ Auth::user()->email }}</span>
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-1.5 justify-center sm:justify-start pt-0.5">
                            @if(Auth::user()->roles && Auth::user()->roles->count() > 0)
                                @foreach(Auth::user()->roles as $r)
                                    <span class="px-3 py-1 bg-sky-500/20 text-sky-200 border border-sky-400/35 rounded-xl text-[10px] font-bold uppercase tracking-wider backdrop-blur-md shadow-xs flex items-center gap-1.5">
                                        <i class="fa-solid fa-shield-halved text-[9px] text-sky-300"></i>
                                        <span>{{ $r->role_name }}</span>
                                    </span>
                                @endforeach
                            @else
                                <span class="px-3 py-1 bg-sky-500/20 text-sky-200 border border-sky-400/35 rounded-xl text-[10px] font-bold uppercase tracking-wider backdrop-blur-md shadow-xs flex items-center gap-1.5">
                                    <i class="fa-solid fa-user text-[9px] text-sky-300"></i>
                                    <span>{{ Auth::user()->role->role_name ?? 'Karyawan' }}</span>
                                </span>
                            @endif

                            @if(Auth::user()->nip)
                                <span class="px-3 py-1 bg-slate-950/80 text-cyan-300 border border-cyan-500/30 rounded-xl text-[10px] font-mono font-bold tracking-wide shadow-xs flex items-center gap-1.5">
                                    <span>NIP: {{ Auth::user()->nip }}</span>
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-slate-950/60 text-slate-400 border border-slate-700/60 rounded-xl text-[10px] font-mono tracking-wide shadow-xs flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-info text-[9px] text-amber-400"></i>
                                    <span>NIP: Belum diatur</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- BODY MODAL: GRID DATA INFORMASI TERSTRUKTUR --}}
            <div class="p-5 sm:p-6 space-y-4 max-h-[60vh] overflow-y-auto bg-slate-50/60 dark:bg-slate-900/60">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">

                    {{-- NOMOR IDENTITAS PEGAWAI (NIP) --}}
                    <div class="p-3.5 bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs hover:border-sky-300/60 dark:hover:border-sky-700/60 transition-all flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fa-solid fa-id-card text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1 space-y-0.5">
                            <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-400 uppercase tracking-wider block">Nomor Induk Pegawai</span>
                            @if(!empty(Auth::user()->nip))
                                <p class="font-bold text-slate-900 dark:text-slate-100 font-mono text-xs sm:text-sm truncate">{{ Auth::user()->nip }}</p>
                            @else
                                <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 italic flex items-center gap-1.5">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-400"></span> Belum diatur
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- NOMOR WHATSAPP & VERIFIKASI --}}
                    <div class="p-3.5 bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs hover:border-sky-300/60 dark:hover:border-sky-700/60 transition-all flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-400 uppercase tracking-wider block">WhatsApp</span>
                                @if(Auth::user()->phone_verified_at)
                                    <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800/70">
                                        <i class="fa-solid fa-check text-[8px]"></i> Terverifikasi
                                    </span>
                                @else
                                    <span class="text-[9px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-1.5 py-0.5 rounded-md border border-amber-200 dark:border-amber-800/70">
                                        Belum Verifikasi
                                    </span>
                                @endif
                            </div>
                            <p class="font-bold text-slate-900 dark:text-slate-100 text-xs sm:text-sm truncate">
                                {{ Auth::user()->phone_number ?? '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- ALAMAT EMAIL & VERIFIKASI --}}
                    <div class="p-3.5 bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs hover:border-sky-300/60 dark:hover:border-sky-700/60 transition-all flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fa-solid fa-envelope text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-400 uppercase tracking-wider block">Email</span>
                                @if(Auth::user()->email_verified_at)
                                    <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800/70">
                                        <i class="fa-solid fa-check text-[8px]"></i> Terverifikasi
                                    </span>
                                @else
                                    <span class="text-[9px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-1.5 py-0.5 rounded-md border border-amber-200 dark:border-amber-800/70">
                                        Belum Verifikasi
                                    </span>
                                @endif
                            </div>
                            <p class="font-bold text-slate-900 dark:text-slate-100 text-xs truncate" title="{{ Auth::user()->email }}">
                                {{ Auth::user()->email }}
                            </p>
                        </div>
                    </div>

                    {{-- JENIS KELAMIN --}}
                    <div class="p-3.5 bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs hover:border-sky-300/60 dark:hover:border-sky-700/60 transition-all flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fa-solid fa-venus-mars text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1 space-y-0.5">
                            <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-400 uppercase tracking-wider block">Jenis Kelamin</span>
                            <p class="font-bold text-slate-900 dark:text-slate-100 text-xs sm:text-sm">
                                {{ Auth::user()->gender->name_gender ?? (Auth::user()->gender_id == 2 ? 'Perempuan' : 'Laki-Laki') }}
                            </p>
                        </div>
                    </div>

                    {{-- STASIUN PENEMPATAN --}}
                    <div class="p-3.5 bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs hover:border-sky-300/60 dark:hover:border-sky-700/60 transition-all flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fa-solid fa-building-user text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1 space-y-0.5">
                            <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-400 uppercase tracking-wider block">Stasiun Penempatan</span>
                            <p class="font-bold text-sky-600 dark:text-sky-400 text-xs sm:text-sm truncate">
                                {{ Auth::user()->station->name ?? 'Stasiun Umbulan' }}
                            </p>
                        </div>
                    </div>

                    {{-- TIPE JADWAL KERJA --}}
                    <div class="p-3.5 bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs hover:border-sky-300/60 dark:hover:border-sky-700/60 transition-all flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1 space-y-0.5">
                            <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-400 uppercase tracking-wider block">Skema Jadwal Kerja</span>
                            <p class="font-bold text-indigo-600 dark:text-indigo-400 text-xs truncate">
                                @if(Auth::user()->schedule_type === 'roster')
                                    Sistem Roster (Shift 12 Jam)
                                @elseif(Auth::user()->schedule_type === 'reguler_6_hari')
                                    Reguler 6 Hari (Sen–Sab)
                                @elseif(Auth::user()->schedule_type === 'reguler_5_hari' || Auth::user()->schedule_type === 'normal')
                                    Reguler 5 Hari (07:00–16:00)
                                @else
                                    {{ Auth::user()->schedule_label ?? 'Belum Diatur' }}
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- AREA CAKUPAN RUMAH METER (PIPELINE ROLE) --}}
                    @if(Auth::user()->hasRole('AREA (PIPELINE)') || Auth::user()->hasRole(14))
                        <div class="p-3.5 bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs col-span-1 sm:col-span-2 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                        <i class="fa-solid fa-gauge-high text-xs"></i>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Area Cakupan Rumah Meter</span>
                                </div>
                                <span class="text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-800">
                                    {{ Auth::user()->assignedStations->count() }} Checkpoint
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @forelse(Auth::user()->assignedStations as $rm)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50/70 dark:bg-amber-950/30 text-amber-900 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/50 rounded-lg text-[11px] font-semibold">
                                        <strong class="font-mono text-amber-700 dark:text-amber-400">{{ $rm->kode_stasiun }}</strong>
                                        <span class="text-slate-600 dark:text-slate-300">{{ $rm->name }}</span>
                                    </span>
                                @empty
                                    <span class="text-slate-400 text-xs italic">Belum ada penugasan Rumah Meter khusus.</span>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- FOOTER MODAL --}}
            <div class="p-4 sm:px-6 border-t border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between gap-3">
                <a href="{{ route('account.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 active:scale-95 hover:brightness-110 cursor-pointer"
                   style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff !important; box-shadow: 0 4px 14px 0 rgba(2, 132, 199, 0.35);">
                    <i class="fa-solid fa-gear text-xs text-sky-200"></i>
                    <span class="text-white font-bold tracking-wide">Kelola Profil & Keamanan</span>
                </a>
                <button type="button" onclick="closeProfileDetailModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 active:scale-95 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-200/80 dark:border-slate-700 transition-all duration-200 cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL KONFIRMASI LOGOUT --}}
    <div id="logoutConfirmModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
        <div id="logoutConfirmModalCard" class="relative bg-white dark:bg-slate-900 rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 dark:border-slate-800 transform transition-all duration-300 scale-95 opacity-0">
            {{-- HEADER MODAL --}}
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-rose-600 to-rose-700 text-white">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                    </div>
                    <h3 class="text-xs font-bold tracking-wide uppercase">Konfirmasi Keluar Aplikasi</h3>
                </div>
                <button type="button" onclick="closeLogoutModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- BODY MODAL --}}
            <div class="p-6 text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-500 flex items-center justify-center mx-auto text-2xl shadow-inner border border-rose-100 dark:border-rose-900/40">
                    <i class="fa-solid fa-door-open"></i>
                </div>
                <div class="space-y-1.5">
                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-100">Apakah Anda yakin ingin keluar?</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-xs mx-auto">
                        Sesi aktif Anda akan diakhiri demi keamanan akun. Anda perlu login kembali untuk mengakses sistem absensi dan dashboard.
                    </p>
                </div>
            </div>

            {{-- FOOTER MODAL ACTIONS --}}
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/90 flex items-center justify-end gap-3">
                <button type="button" onclick="closeLogoutModal()" class="px-4 py-2.5 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-all cursor-pointer">
                    Batal
                </button>
                <form id="logoutModalForm" action="{{ route('logout') }}" method="POST" data-turbo="false" class="inline m-0">
                    @csrf
                    <button type="submit" id="btnConfirmLogout" class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/20 transition-all cursor-pointer">
                        <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                        <span>Ya, Keluar Aplikasi</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openProfileDetailModal() {
            const modal = document.getElementById('profileDetailModal');
            const modalCard = document.getElementById('profileDetailModalCard');
            if (!modal || !modalCard) return;

            modal.classList.remove('hidden');
            setTimeout(() => {
                modalCard.classList.remove('scale-95', 'opacity-0');
                modalCard.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeProfileDetailModal() {
            const modal = document.getElementById('profileDetailModal');
            const modalCard = document.getElementById('profileDetailModalCard');
            if (!modal || !modalCard) return;

            modalCard.classList.remove('scale-100', 'opacity-100');
            modalCard.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        document.getElementById('profileDetailModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeProfileDetailModal();
            }
        });

        // --- MODAL KONFIRMASI LOGOUT HANDLERS ---
        function openLogoutModal() {
            const modal = document.getElementById('logoutConfirmModal');
            const modalCard = document.getElementById('logoutConfirmModalCard');
            if (!modal || !modalCard) return;

            // Sinkronisasi token CSRF form dengan meta tag terbaru
            const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const tokenInput = document.querySelector('#logoutModalForm input[name="_token"]');
            if (metaToken && tokenInput) {
                tokenInput.value = metaToken;
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modalCard.classList.remove('scale-95', 'opacity-0');
                modalCard.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeLogoutModal() {
            const modal = document.getElementById('logoutConfirmModal');
            const modalCard = document.getElementById('logoutConfirmModalCard');
            if (!modal || !modalCard) return;

            modalCard.classList.remove('scale-100', 'opacity-100');
            modalCard.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        document.getElementById('logoutConfirmModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });

        document.getElementById('logoutModalForm')?.addEventListener('submit', function() {
            localStorage.setItem('theme', 'light');
            const btn = document.getElementById('btnConfirmLogout');
            if (btn && !btn.dataset.submitted) {
                btn.dataset.submitted = "true";
                setTimeout(() => {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-xs mr-1.5"></i><span>Memproses...</span>';
                }, 10);
            }
        });

        // --- BFCACHE BUSTER (Anti-Back-Button Setelah Logout) ---
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward")) {
                window.location.reload();
            }
        });
    </script>

    @stack('modals')
    @stack('scripts')
</body>
</html>
