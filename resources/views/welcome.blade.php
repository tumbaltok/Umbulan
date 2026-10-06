<!DOCTYPE html>
<html lang="id" class="scroll-smooth h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Deteksi PWA: Alihkan otomatis ke login jika dibuka dari aplikasi HP --}}
    <script>
        (function() {
            const isPWA = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
            if (isPWA) {
                window.location.replace("{{ route('login') }}");
            }
        })();
    </script>

    {{-- Pengaturan warna background bilah status peramban mobile --}}
    <meta name="theme-color" content="#ffffff">
    <meta name="background-color" content="#ffffff">

    <title>META Adhya Tirta Umbulan - Penyaluran Air Bersih 93 KM</title>
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}?v=2">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        document.documentElement.classList.remove('dark');
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        water: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                            950: '#082f49',
                        }
                    }
                }
            }
        }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        ::-webkit-scrollbar-thumb {
            background: #bfdbfe;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #60a5fa;
        }

        /* Animasi Aliran Air di Dalam Pipa */
        @keyframes waterFlowAnim {
            0% { background-position: 0 0; }
            100% { background-position: 0 40px; }
        }

        @keyframes waterFlowHorizontal {
            0% { background-position: 0 0; }
            100% { background-position: 40px 0; }
        }

        .water-stream-anim {
            background-image: repeating-linear-gradient(45deg, rgba(255,255,255,0.3) 0px, rgba(255,255,255,0.3) 12px, transparent 12px, transparent 24px);
            animation: waterFlowAnim 1.6s linear infinite;
        }

        .water-stream-horizontal {
            background-image: repeating-linear-gradient(90deg, rgba(255,255,255,0.3) 0px, rgba(255,255,255,0.3) 12px, transparent 12px, transparent 24px);
            animation: waterFlowHorizontal 1.6s linear infinite;
        }

        .water-pattern {
            background-image: radial-gradient(rgba(14, 165, 233, 0.12) 1.2px, transparent 1.2px);
            background-size: 20px 20px;
        }

        /* Efek Active Flow pada Rumah Meter saat dilewati air */
        .rm-node.active-flow .rm-indicator {
            background-color: #0284c7 !important;
            border-color: #38bdf8 !important;
            color: #ffffff !important;
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.6) !important;
        }

        .rm-node.active-flow .rm-connector {
            background-color: #0284c7 !important;
            box-shadow: 0 0 10px rgba(14, 165, 233, 0.5) !important;
        }

        .rm-node.active-flow .rm-card {
            border-color: #93c5fd !important;
            box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.15), 0 8px 10px -6px rgba(14, 165, 233, 0.1) !important;
            background-color: #ffffff !important;
        }

        .rm-node.active-flow .rm-badge {
            background-color: #eff6ff !important;
            color: #0284c7 !important;
            border-color: #bfdbfe !important;
        }

        .rm-node.active-flow .rm-ping {
            display: inline-flex !important;
        }
    </style>

    {{-- Komponen Head PWA --}}
    @pwaHead
</head>
<body class="font-sans text-slate-800 antialiased bg-white flex flex-col min-h-screen">

    {{-- ======================================================== --}}
    {{-- HEADER / NAVBAR (PUTIH & BIRU ELEGAN) --}}
    {{-- ======================================================== --}}
    <header class="sticky top-0 bg-white/95 backdrop-blur-md border-b border-blue-100 h-20 flex items-center justify-between px-6 md:px-12 z-50 shadow-xs">
        <a href="#" class="flex items-center space-x-3.5 group">
            <div class="w-11 h-11 bg-white rounded-2xl flex items-center justify-center p-1 shadow-md shadow-blue-500/10 border border-blue-100 transition duration-300 group-hover:scale-105 shrink-0">
                <img src="{{ asset('images/iconfav.png') }}" 
                    alt="Logo META" 
                    class="w-full h-full object-contain rounded-xl">
            </div>
            <div class="flex flex-col">
                <span class="text-base sm:text-lg font-black tracking-tight text-slate-900 font-heading leading-tight">
                    META <span class="text-blue-600">Adhya Tirta Umbulan</span>
                </span>
                <span class="text-[10px] font-bold text-blue-600/80 tracking-wider uppercase flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                    KPBU SPAM Umbulan • Penyaluran Air Bersih
                </span>
            </div>
        </a>

        <div class="hidden md:flex items-center space-x-8">
            <nav class="flex items-center space-x-6 text-sm font-semibold text-slate-600 mr-2">
                <a href="#jalur-pipa" class="hover:text-blue-600 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-route text-blue-500 text-xs"></i> Jalur Pipa 93 KM
                </a>
                <a href="#about-kami" class="hover:text-blue-600 transition">Tentang Proyek</a>
                <a href="#titik-distribusi" class="hover:text-blue-600 transition">Wilayah Layanan</a>
            </nav>
            <a href="/login" onclick="showToast('Mengarahkan ke Portal Karyawan...', 'info')" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-700 hover:to-sky-700 active:scale-95 text-white text-sm font-bold rounded-xl shadow-md shadow-blue-600/20 transition-all duration-150 flex items-center space-x-2">
                <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                <span>Portal Karyawan</span>
            </a>
        </div>

        <button id="mobile-menu-btn" class="md:hidden p-2 text-slate-600 hover:text-blue-600 focus:outline-none" aria-label="Toggle Menu">
            <i class="fa-solid fa-bars text-2xl"></i>
        </button>
    </header>

    {{-- Mobile Drawer Menu --}}
    <div id="mobile-drawer" class="fixed inset-0 bg-blue-950/40 z-50 backdrop-blur-xs transition-opacity duration-300 hidden opacity-0">
        <div class="fixed top-0 right-0 bottom-0 w-4/5 max-w-sm bg-white p-6 shadow-2xl flex flex-col justify-between transform translate-x-full transition-transform duration-300" id="mobile-drawer-content">
            <div>
                <div class="flex items-center justify-between pb-6 border-b border-blue-50">
                    <span class="font-extrabold text-lg text-slate-900 font-heading">Menu Layanan</span>
                    <button id="close-drawer-btn" class="p-2 text-slate-400 hover:text-blue-600">
                        <i class="fa-solid fa-xmark text-2xl"></i>
                    </button>
                </div>
                <nav class="flex flex-col space-y-3 mt-6">
                    <a href="#jalur-pipa" class="mobile-link text-sm font-semibold text-slate-700 hover:text-blue-600 transition py-2.5 border-b border-blue-50 flex items-center gap-2">
                        <i class="fa-solid fa-route text-blue-500"></i> Jalur Pipa 93 KM (18 Rumah Meter)
                    </a>
                    <a href="#about-kami" class="mobile-link text-sm font-semibold text-slate-700 hover:text-blue-600 transition py-2.5 border-b border-blue-50">
                        Tentang Proyek KPBU
                    </a>
                    <a href="#titik-distribusi" class="mobile-link text-sm font-semibold text-slate-700 hover:text-blue-600 transition py-2.5 border-b border-blue-50">
                        Wilayah Distribusi Jatim
                    </a>
                </nav>
            </div>
            <div class="pt-6 border-t border-blue-50">
                <a href="/login" onclick="showToast('Mengarahkan ke Portal Karyawan...', 'info')" class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-sky-600 text-white text-center font-bold rounded-2xl shadow-lg shadow-blue-600/20 block text-sm">
                    <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Portal Karyawan
                </a>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- HERO SECTION (BERSIH, PUTIH & BIRU DENGAN COCKPIT TRANSMISI) --}}
    {{-- ======================================================== --}}
    <section class="relative bg-gradient-to-b from-blue-50/60 via-white to-white pt-12 pb-20 md:pt-20 md:pb-28 px-6 md:px-12 overflow-hidden">
        <div class="absolute inset-0 water-pattern opacity-60 pointer-events-none"></div>
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-200/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-sky-200/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center gap-12 relative z-10">
            {{-- Sisi Kiri: Narasi Utama Penyaluran Air Bersih --}}
            <div class="w-full lg:w-1/2 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-full border border-blue-200 shadow-xs">
                    <i class="fa-solid fa-water text-blue-500"></i>
                    <span>PROYEK STRATEGIS NASIONAL • KPBU SPAM UMBULAN</span>
                </div>

                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black font-heading text-slate-900 leading-tight">
                    Penyaluran Air Bersih <br class="hidden md:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-sky-500">
                        Kapasitas 4.000 Liter/Detik
                    </span>
                </h1>

                <p class="text-slate-600 text-sm sm:text-base md:text-lg leading-relaxed max-w-xl mx-auto lg:mx-0 font-normal">
                    PT. Meta Adhya Tirta Umbulan mengalirkan air minum curah berkualitas tinggi melalui pipa transmisi baja sepanjang 93 KM, melintasi 18 titik Rumah Meter dari mata air Umbulan (Pasuruan) hingga Kabupaten Gresik.
                </p>

                {{-- 3 Tag Layanan Unggulan --}}
                <div class="flex flex-wrap justify-center lg:justify-start gap-3 pt-2">
                    <div class="flex items-center space-x-2 px-3.5 py-2 bg-white border border-blue-100 rounded-xl shadow-xs text-xs font-semibold text-slate-700">
                        <i class="fa-solid fa-route text-blue-600"></i>
                        <span>Pipa Transmisi ±93 KM</span>
                    </div>
                    <div class="flex items-center space-x-2 px-3.5 py-2 bg-white border border-blue-100 rounded-xl shadow-xs text-xs font-semibold text-slate-700">
                        <i class="fa-solid fa-gauge-high text-sky-600"></i>
                        <span>18 Titik Rumah Meter</span>
                    </div>
                    <div class="flex items-center space-x-2 px-3.5 py-2 bg-white border border-blue-100 rounded-xl shadow-xs text-xs font-semibold text-slate-700">
                        <i class="fa-solid fa-users text-blue-600"></i>
                        <span>1,3 Juta Jiwa Penerima</span>
                    </div>
                </div>

                {{-- Tombol Aksi CTA --}}
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="#jalur-pipa" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-700 hover:to-sky-700 active:scale-95 text-white font-bold rounded-2xl text-center shadow-lg shadow-blue-600/25 transition-all duration-150 flex items-center justify-center gap-2">
                        <span>Lihat Aliran Pipa 93 KM</span>
                        <i class="fa-solid fa-arrow-down text-xs"></i>
                    </a>
                    <a href="#titik-distribusi" class="w-full sm:w-auto px-8 py-4 bg-white hover:bg-blue-50/50 border border-blue-200 active:scale-95 text-blue-700 font-bold rounded-2xl text-center shadow-xs transition-all duration-150">
                        Cakupan 5 Wilayah
                    </a>
                </div>
            </div>

            {{-- Sisi Kanan: Kartu Cockpit Infrastruktur & Keandalan Transmisi Air --}}
            <div class="w-full lg:w-1/2 flex justify-center">
                <div class="relative w-full max-w-md md:max-w-lg">
                    <div class="absolute inset-0 bg-gradient-to-tr from-blue-300/30 to-sky-200/20 rounded-3xl blur-2xl transform rotate-3 scale-95 pointer-events-none"></div>

                    <div class="relative bg-white/95 backdrop-blur-xl border border-blue-100 p-6 sm:p-8 rounded-3xl shadow-xl shadow-blue-500/5 space-y-5 transition-all duration-300">
                        {{-- Status Operasional Transmisi --}}
                        <div class="flex items-center justify-between border-b border-blue-50 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-500"></span>
                                </span>
                                <span class="text-xs font-bold text-slate-800 uppercase tracking-wider font-heading">Transmisi Air Aktif</span>
                            </div>
                            <span class="inline-flex items-center text-xs font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-200/80">
                                93 KM BEROPERASI
                            </span>
                        </div>

                        {{-- Visual Simulasi Mini Pipa Transmisi Hulu ke Hilir --}}
                        <div class="p-4 bg-gradient-to-b from-blue-50/70 to-sky-50/40 border border-blue-100 rounded-2xl">
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-600 mb-2.5">
                                <span class="flex items-center gap-1.5 text-blue-700 font-bold">
                                    <i class="fa-solid fa-water text-xs"></i> Hulu: Umbulan
                                </span>
                                <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider">Ø 1.000 - 1.800 mm</span>
                                <span class="flex items-center gap-1.5 text-blue-700 font-bold">
                                    Hilir: Giri <i class="fa-solid fa-flag-checkered text-xs"></i>
                                </span>
                            </div>

                            {{-- Pipa Beranimasi Aliran Air --}}
                            <div class="relative h-10 bg-white rounded-xl border border-blue-200/80 overflow-hidden shadow-inner flex items-center p-1">
                                <div class="h-full w-full rounded-lg bg-gradient-to-r from-blue-600 via-sky-400 to-blue-500 relative overflow-hidden flex items-center justify-between px-3">
                                    <div class="absolute inset-0 opacity-40 water-stream-horizontal"></div>
                                    <span class="text-[10px] font-bold text-white uppercase tracking-wider z-10 flex items-center gap-1">
                                        <i class="fa-solid fa-arrow-right text-[8px] animate-pulse"></i> 0 KM
                                    </span>
                                    <span class="text-[10px] font-extrabold text-white tracking-widest z-10 bg-white/20 backdrop-blur-xs px-2.5 py-0.5 rounded-md font-mono">
                                        18 RUMAH METER
                                    </span>
                                    <span class="text-[10px] font-bold text-white uppercase tracking-wider z-10">
                                        93 KM
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-slate-500 mt-2.5">
                                <span class="flex items-center gap-1.5 text-emerald-600 font-semibold">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i> Aliran Terdistribusi Lancar
                                </span>
                                <span class="font-bold text-blue-700 font-mono">Gravitasi & Booster Pompa</span>
                            </div>
                        </div>

                        {{-- 2 Kotak Metrik Desain & Sambungan --}}
                        <div class="grid grid-cols-2 gap-3.5">
                            <div class="p-4 bg-white border border-blue-100 rounded-2xl shadow-xs">
                                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block">Kapasitas Desain</span>
                                <h4 class="text-xl font-extrabold text-slate-800 font-heading mt-1">4.000 <span class="text-xs font-semibold text-slate-400">lps</span></h4>
                                <span class="text-[10px] text-slate-400 mt-0.5 block">Debit Penuh Air Curah</span>
                            </div>
                            <div class="p-4 bg-white border border-blue-100 rounded-2xl shadow-xs">
                                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block">Target Sambungan</span>
                                <h4 class="text-xl font-extrabold text-slate-800 font-heading mt-1">310.000 <span class="text-xs font-semibold text-slate-400">SR</span></h4>
                                <span class="text-[10px] text-slate-400 mt-0.5 block">Pelanggan Rumah Tangga</span>
                            </div>
                        </div>

                        {{-- Karakteristik Sumber Air Alami --}}
                        <div class="p-4 bg-white border border-blue-100 rounded-2xl shadow-xs flex items-center space-x-3.5">
                            <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center text-xl shrink-0">
                                <i class="fa-solid fa-droplet"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block">Karakteristik Air Baku</span>
                                <h4 class="text-sm font-bold text-slate-800 leading-tight">Sumber Mata Air Alami Terlindungi</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Disalurkan melalui pipa transmisi baja tertutup guna menjaga kebersihan dan kejernihan air alami.</p>
                            </div>
                        </div>

                        {{-- Jaminan Pasokan 24 Jam Nonstop --}}
                        <div class="p-4 bg-gradient-to-r from-blue-600 to-sky-600 text-white rounded-2xl shadow-md shadow-blue-500/20 flex items-center space-x-3.5">
                            <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-xs text-white flex items-center justify-center text-xl shrink-0">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-blue-100 uppercase tracking-wider block">Kontinuitas Distribusi</span>
                                <h4 class="text-sm font-bold text-white leading-tight">Pasokan Andal 24 Jam Nonstop</h4>
                                <p class="text-[11px] text-blue-100/90 mt-0.5">Menyuplai PDAM di 5 Kabupaten/Kota Jawa Timur secara berkesinambungan.</p>
                            </div>
                        </div>

                        {{-- Tautan Langsung ke Jalur Pipa Scroll --}}
                        <a href="#jalur-pipa" class="w-full py-3 bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 font-bold rounded-xl flex items-center justify-center gap-2 text-xs transition duration-200 group">
                            <span>Telusuri Aliran 93 KM & 18 Titik Rumah Meter</span>
                            <i class="fa-solid fa-arrow-down text-blue-600 group-hover:translate-y-0.5 transition-transform text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ======================================================== --}}
    {{-- STATISTIK 4 PILAR UTAMA (PUTIH & BIRU BERSIH) --}}
    {{-- ======================================================== --}}
    <section class="py-14 bg-gradient-to-b from-white via-blue-50/40 to-white px-6 md:px-12 border-y border-blue-100 relative overflow-hidden">
        <div class="max-w-7xl mx-auto grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 relative z-10 text-center">
            <div class="p-6 bg-white rounded-2xl border border-blue-100 shadow-xs hover:border-blue-300 hover:shadow-md transition">
                <span class="text-3xl md:text-4xl font-black font-heading text-blue-600 block">4.000</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-1">Liter / Detik</span>
                <p class="text-xs font-semibold text-slate-700 mt-2">Kapasitas Desain Transmisi</p>
            </div>
            <div class="p-6 bg-white rounded-2xl border border-blue-100 shadow-xs hover:border-blue-300 hover:shadow-md transition">
                <span class="text-3xl md:text-4xl font-black font-heading text-blue-600 block">±93 KM</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-1">Pipa Baja Terpasang</span>
                <p class="text-xs font-semibold text-slate-700 mt-2">Jalur Transmisi Hulu ke Hilir</p>
            </div>
            <div class="p-6 bg-white rounded-2xl border border-blue-100 shadow-xs hover:border-blue-300 hover:shadow-md transition">
                <span class="text-3xl md:text-4xl font-black font-heading text-blue-600 block">18 Titik</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-1">Stasiun Rumah Meter</span>
                <p class="text-xs font-semibold text-slate-700 mt-2">Checkpoint Offtake Distribusi</p>
            </div>
            <div class="p-6 bg-white rounded-2xl border border-blue-100 shadow-xs hover:border-blue-300 hover:shadow-md transition">
                <span class="text-3xl md:text-4xl font-black font-heading text-blue-600 block">1,3 Juta</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-1">Jiwa Penerima Manfaat</span>
                <p class="text-xs font-semibold text-slate-700 mt-2">5 Kab/Kota di Jawa Timur</p>
            </div>
        </div>
    </section>

    {{-- ======================================================== --}}
    {{-- BAGIAN UTAMA: ANIMASI SCROLL PIPA 93 KM & 18 RUMAH METER --}}
    {{-- ======================================================== --}}
    <section id="jalur-pipa" class="py-20 md:py-28 px-4 sm:px-6 md:px-12 bg-white relative scroll-mt-16">
        <div class="max-w-7xl mx-auto space-y-12">

            {{-- Header Bagian Pipa Transmisi --}}
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold uppercase tracking-wider">
                    <i class="fa-solid fa-route text-blue-500"></i>
                    <span>Simulasi Interaktif Aliran Air Bersih</span>
                </div>

                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black font-heading text-slate-900 leading-tight">
                    Jalur Pipa Transmisi 93 KM <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-sky-500">
                        18 Titik Rumah Meter (Hulu ke Hilir)
                    </span>
                </h2>

                <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                    Air bersih mengalir dari mata air Umbulan (Pasuruan) menuju titik hilir terjauh di Giri (Gresik). Gulir layar (scroll) untuk menelusuri aliran air yang melintasi setiap stasiun Rumah Meter sepanjang 93 kilometer.
                </p>
            </div>

            {{-- HUD / Bilah Pantauan Kemajuan Aliran Pipa --}}
            <div class="sticky top-20 z-40 bg-white/95 backdrop-blur-md border border-blue-200 rounded-2xl p-4 sm:p-5 shadow-lg shadow-blue-500/5 transition-all">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-blue-600/20 shrink-0">
                            <i class="fa-solid fa-water"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Posisi Aliran Air:</span>
                                <span id="hud-km" class="text-base sm:text-lg font-black text-blue-600 font-mono">0.0 KM</span>
                                <span class="text-xs text-slate-400 font-semibold">/ 93.0 KM</span>
                            </div>
                            <p id="hud-station" class="text-xs font-semibold text-slate-700">Mata Air Umbulan (Hulu Transmisi)</p>
                        </div>
                    </div>

                    {{-- Tombol Kontrol: Simulasi Aliran Otomatis & Reset --}}
                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        <button id="btn-auto-scroll" onclick="toggleAutoScroll()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/20 flex items-center gap-2 transition">
                            <i id="btn-auto-icon" class="fa-solid fa-play text-[10px]"></i>
                            <span id="btn-auto-text">Alirkan Otomatis</span>
                        </button>
                        <button onclick="resetToHulu()" class="px-3 py-2 bg-white hover:bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold rounded-xl transition" title="Kembali ke Titik Hulu">
                            <i class="fa-solid fa-arrow-up"></i> Hulu
                        </button>
                    </div>
                </div>

                {{-- Bar Kemajuan Mini Aliran Pipa Horizontal --}}
                <div class="mt-3 w-full bg-blue-100/60 rounded-full h-2.5 overflow-hidden p-0.5 border border-blue-200/60">
                    <div id="mini-progress-fill" class="h-full bg-gradient-to-r from-blue-600 via-sky-400 to-blue-500 rounded-full transition-all duration-150" style="width: 2%;"></div>
                </div>
            </div>

            {{-- Tombol Filter Wilayah --}}
            <div class="flex flex-wrap items-center justify-center gap-2 pt-2">
                <button onclick="filterRegion('all')" class="region-filter-btn px-4 py-2 rounded-xl text-xs font-bold border transition bg-blue-600 text-white border-blue-600 shadow-xs" data-region="all">
                    Semua (18 Titik)
                </button>
                <button onclick="filterRegion('pasuruan')" class="region-filter-btn px-4 py-2 rounded-xl text-xs font-bold border transition bg-white text-slate-600 border-blue-100 hover:border-blue-300" data-region="pasuruan">
                    Kab. Pasuruan (RM 01 - 06)
                </button>
                <button onclick="filterRegion('sidoarjo')" class="region-filter-btn px-4 py-2 rounded-xl text-xs font-bold border transition bg-white text-slate-600 border-blue-100 hover:border-blue-300" data-region="sidoarjo">
                    Kab. Sidoarjo (RM 07 - 14)
                </button>
                <button onclick="filterRegion('surabaya')" class="region-filter-btn px-4 py-2 rounded-xl text-xs font-bold border transition bg-white text-slate-600 border-blue-100 hover:border-blue-300" data-region="surabaya">
                    Kota Surabaya (RM 15 - 17)
                </button>
                <button onclick="filterRegion('gresik')" class="region-filter-btn px-4 py-2 rounded-xl text-xs font-bold border transition bg-white text-slate-600 border-blue-100 hover:border-blue-300" data-region="gresik">
                    Kab. Gresik (RM 18)
                </button>
            </div>

            {{-- ======================================================== --}}
            {{-- REL PIPA VERTIKAL DENGAN 18 TITIK RUMAH METER --}}
            {{-- ======================================================== --}}
            <div class="relative py-6 max-w-4xl mx-auto" id="pipeline-container">

                {{-- Batang Pipa Pusat (Transmisi Utama 93 KM) --}}
                <div class="absolute left-6 md:left-1/2 top-12 bottom-12 -translate-x-1/2 w-6 sm:w-8 bg-blue-100/60 rounded-full border-2 border-blue-200 shadow-inner overflow-hidden z-10">
                    {{-- Air yang Mengalir Mengisi Pipa Sesuai Scroll --}}
                    <div id="pipe-water-fill" class="w-full bg-gradient-to-b from-blue-600 via-sky-400 to-blue-500 rounded-full transition-all duration-75 relative overflow-hidden" style="height: 5%;">
                        <div class="absolute inset-0 water-stream-anim opacity-40"></div>
                        {{-- Ujung Kepala Aliran Air yang Berpendar --}}
                        <div class="absolute bottom-0 left-0 right-0 h-4 bg-sky-200 rounded-full blur-xs"></div>
                    </div>
                </div>

                {{-- TITIK 0: HULU MATA AIR UMBULAN --}}
                <div class="relative z-20 flex items-center justify-start md:justify-center mb-16 pl-14 md:pl-0">
                    <div class="bg-gradient-to-r from-blue-600 to-sky-600 text-white px-6 py-4 rounded-2xl shadow-lg shadow-blue-500/20 border border-blue-300 flex items-center gap-3.5 max-w-md w-full md:w-auto">
                        <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-2xl shrink-0">
                            <i class="fa-solid fa-water"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-extrabold uppercase tracking-widest text-blue-100 bg-white/15 px-2 py-0.5 rounded">HULU TRANSMISI</span>
                                <span class="text-xs font-mono font-bold text-sky-200">0.0 KM</span>
                            </div>
                            <h3 class="text-lg font-black font-heading text-white">Mata Air Umbulan</h3>
                            <p class="text-xs text-blue-100">Kapasitas Sumber Alami • Kab. Pasuruan (Elevasi 33 mdpl)</p>
                        </div>
                    </div>
                </div>

                {{-- DAFTAR 18 RUMAH METER (HULU SAMPAI HILIR) --}}
                @php
                    $stations = [
                        ['no' => 1, 'kode' => 'RM_01', 'name' => 'Winongan', 'km' => 2.5, 'region' => 'pasuruan', 'region_name' => 'Kab. Pasuruan', 'diam' => '1.800 mm', 'desc' => 'Titik offtake pertama pasca intake sumber Umbulan untuk PDAM Pasuruan.'],
                        ['no' => 2, 'kode' => 'RM_02', 'name' => 'Pohjentrek', 'km' => 7.8, 'region' => 'pasuruan', 'region_name' => 'Kab. Pasuruan', 'diam' => '1.800 mm', 'desc' => 'Penyaluran air curah untuk wilayah Pasuruan barat dan pemukiman sekitar.'],
                        ['no' => 3, 'kode' => 'RM_03', 'name' => 'Pleret', 'km' => 12.3, 'region' => 'pasuruan', 'region_name' => 'Kota/Kab. Pasuruan', 'diam' => '1.800 mm', 'desc' => 'Stasiun meter perkotaan untuk jaringan pipa distribusi air minum lokal.'],
                        ['no' => 4, 'kode' => 'RM_04', 'name' => 'PIER', 'km' => 17.5, 'region' => 'pasuruan', 'region_name' => 'Kawasan Industri Pasuruan', 'diam' => '1.800 mm', 'desc' => 'Pasokan air minum terpadu untuk kawasan industri strategis PIER Pasuruan.'],
                        ['no' => 5, 'kode' => 'RM_05', 'name' => 'Bangil', 'km' => 23.2, 'region' => 'pasuruan', 'region_name' => 'Kab. Pasuruan', 'diam' => '1.600 mm', 'desc' => 'Titik transmisi utama ibukota kabupaten menuju koridor barat Pasuruan.'],
                        ['no' => 6, 'kode' => 'RM_06', 'name' => 'Gempol', 'km' => 31.9, 'region' => 'pasuruan', 'region_name' => 'Kab. Pasuruan', 'diam' => '1.600 mm', 'desc' => 'Titik perbatasan akhir Pasuruan sebelum pipa memasuki wilayah Sidoarjo.'],
                        ['no' => 7, 'kode' => 'RM_07', 'name' => 'Porong PDAB', 'km' => 38.4, 'region' => 'sidoarjo', 'region_name' => 'Kab. Sidoarjo', 'diam' => '1.600 mm', 'desc' => 'Gerbang transmisi masuk Sidoarjo untuk koordinasi PDAB Jawa Timur.'],
                        ['no' => 8, 'kode' => 'RM_08', 'name' => 'Porong PDAM', 'km' => 40.1, 'region' => 'sidoarjo', 'region_name' => 'Kab. Sidoarjo', 'diam' => '1.600 mm', 'desc' => 'Distribusi air curah skala besar ke instalasi pengolahan PDAM Delta Tirta.'],
                        ['no' => 9, 'kode' => 'RM_09', 'name' => 'Tanggulangin', 'km' => 46.5, 'region' => 'sidoarjo', 'region_name' => 'Kab. Sidoarjo', 'diam' => '1.400 mm', 'desc' => 'Penyaluran air bersih untuk kawasan pemukiman & sentra UKM Tanggulangin.'],
                        ['no' => 10, 'kode' => 'RM_10', 'name' => 'Candi', 'km' => 52.0, 'region' => 'sidoarjo', 'region_name' => 'Kab. Sidoarjo', 'diam' => '1.400 mm', 'desc' => 'Stasiun pemantauan debit aliran untuk jaringan distribusi Sidoarjo selatan.'],
                        ['no' => 11, 'kode' => 'RM_11', 'name' => 'Sidoarjo', 'km' => 56.8, 'region' => 'sidoarjo', 'region_name' => 'Pusat Kota Sidoarjo', 'diam' => '1.400 mm', 'desc' => 'Titik offtake utama melayani pusat perkotaan dan fasilitas umum Sidoarjo.'],
                        ['no' => 12, 'kode' => 'RM_12', 'name' => 'Buduran', 'km' => 61.4, 'region' => 'sidoarjo', 'region_name' => 'Kab. Sidoarjo', 'diam' => '1.400 mm', 'desc' => 'Suplai air minum untuk kawasan industri dan perumahan Buduran.'],
                        ['no' => 13, 'kode' => 'RM_13', 'name' => 'Gedangan', 'km' => 66.0, 'region' => 'sidoarjo', 'region_name' => 'Kab. Sidoarjo', 'diam' => '1.400 mm', 'desc' => 'Zona kepadatan penduduk tinggi penyangga mobilitas Sidoarjo - Surabaya.'],
                        ['no' => 14, 'kode' => 'RM_14', 'name' => 'Waru', 'km' => 71.5, 'region' => 'sidoarjo', 'region_name' => 'Batas Sidoarjo-Surabaya', 'diam' => '1.400 mm', 'desc' => 'Checkpoint batas kabupaten sebelum pipa transmisi melintasi Kota Surabaya.'],
                        ['no' => 15, 'kode' => 'RM_15', 'name' => 'Wonocolo', 'km' => 77.2, 'region' => 'surabaya', 'region_name' => 'Kota Surabaya Selatan', 'diam' => '1.200 mm', 'desc' => 'Pintu masuk suplai air curah Umbulan untuk PDAM Surya Sembada Surabaya.'],
                        ['no' => 16, 'kode' => 'RM_16', 'name' => 'Putat Gedhe', 'km' => 82.6, 'region' => 'surabaya', 'region_name' => 'Kota Surabaya Barat', 'diam' => '1.200 mm', 'desc' => 'Distribusi air minum untuk pengembangan wilayah perumahan Surabaya barat.'],
                        ['no' => 17, 'kode' => 'RM_17', 'name' => 'Alas Malang', 'km' => 87.3, 'region' => 'surabaya', 'region_name' => 'Kota Surabaya Barat', 'diam' => '1.200 mm', 'desc' => 'Koridor transmisi akhir Surabaya sebelum menyeberang ke perbatasan Gresik.'],
                        ['no' => 18, 'kode' => 'RM_18', 'name' => 'Giri', 'km' => 93.0, 'region' => 'gresik', 'region_name' => 'Kabupaten Gresik', 'diam' => '1.000 mm', 'desc' => 'Titik hilir terjauh pipa transmisi 93 KM melayani kebutuhan PDAM Gresik.'],
                    ];
                @endphp

                <div class="space-y-10 sm:space-y-12">
                    @foreach($stations as $index => $st)
                        @php
                            $isEven = ($index % 2 === 1);
                        @endphp
                        <div class="rm-node relative flex items-center {{ $isEven ? 'md:flex-row-reverse' : 'md:flex-row' }} pl-14 md:pl-0 transition-all duration-300"
                             data-km="{{ $st['km'] }}"
                             data-name="RM {{ sprintf('%02d', $st['no']) }} - {{ $st['name'] }}"
                             data-region="{{ $st['region'] }}"
                             id="station-node-{{ $st['no'] }}">

                            {{-- Lingkaran Titik Sensor pada Batang Pipa Pusat --}}
                            <div class="rm-indicator absolute left-6 md:left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-white border-2 border-blue-200 text-blue-600 font-bold text-xs flex items-center justify-center z-30 transition-all duration-300 shadow-xs">
                                <span>{{ $st['no'] }}</span>
                                <span class="rm-ping hidden animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                            </div>

                            {{-- Pipa Cabang Horizontal Menuju Kartu Stasiun --}}
                            <div class="rm-connector hidden md:block absolute top-1/2 -translate-y-1/2 h-1 bg-blue-100 transition-all duration-300 z-20 {{ $isEven ? 'right-1/2 w-8 sm:w-12' : 'left-1/2 w-8 sm:w-12' }}"></div>

                            {{-- Kartu Rincian Rumah Meter --}}
                            <div class="w-full md:w-[calc(50%-3.5rem)]">
                                <div class="rm-card bg-white border border-blue-100 p-5 rounded-2xl shadow-xs hover:border-blue-300 hover:shadow-md transition-all duration-300">
                                    <div class="flex items-center justify-between pb-2 border-b border-blue-50">
                                        <div class="flex items-center gap-2">
                                            <span class="rm-badge text-[10px] font-extrabold uppercase tracking-wider bg-blue-50 text-blue-700 px-2.5 py-0.5 rounded-lg border border-blue-100">
                                                {{ $st['kode'] }}
                                            </span>
                                            <span class="text-xs font-semibold text-slate-500">{{ $st['region_name'] }}</span>
                                        </div>
                                        <span class="text-xs font-black font-mono text-blue-600 bg-blue-50/60 px-2 py-0.5 rounded">
                                            KM {{ number_format($st['km'], 1) }}
                                        </span>
                                    </div>

                                    <div class="mt-3">
                                        <h4 class="text-base sm:text-lg font-bold text-slate-800 font-heading">
                                            Rumah Meter {{ $st['name'] }}
                                        </h4>
                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                            {{ $st['desc'] }}
                                        </p>
                                    </div>

                                    <div class="mt-3.5 pt-3 border-t border-blue-50 flex items-center justify-between text-[11px] text-slate-400">
                                        <span class="flex items-center gap-1 font-mono text-slate-500">
                                            <i class="fa-solid fa-circle-nodes text-blue-500"></i> Pipa: {{ $st['diam'] }}
                                        </span>
                                        <span class="text-emerald-600 font-bold flex items-center gap-1">
                                            <i class="fa-solid fa-circle-check text-[10px]"></i> Suplai Siap
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- TITIK AKHIR: HILIR RESERVOIR GIRI GRESIK --}}
                <div class="relative z-20 flex items-center justify-start md:justify-center mt-16 pl-14 md:pl-0" id="hilir-reservoir">
                    <div class="bg-gradient-to-r from-blue-700 via-sky-600 to-blue-600 text-white px-6 py-4 rounded-2xl shadow-lg shadow-blue-600/20 border border-blue-200 flex items-center gap-3.5 max-w-md w-full md:w-auto">
                        <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-2xl shrink-0">
                            <i class="fa-solid fa-flag-checkered"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-extrabold uppercase tracking-widest text-blue-100 bg-white/15 px-2 py-0.5 rounded">HILIR TRANSMISI</span>
                                <span class="text-xs font-mono font-bold text-sky-200">93.0 KM</span>
                            </div>
                            <h3 class="text-lg font-black font-heading text-white">Reservoir Giri Gresik</h3>
                            <p class="text-xs text-blue-100">Titik Akhir Penyaluran Air Bersih • Kabupaten Gresik</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ======================================================== --}}
    {{-- TENTANG PROYEK KPBU (PUTIH BERSIH DENGAN AKSEN BIRU) --}}
    {{-- ======================================================== --}}
    <section id="about-kami" class="py-24 px-6 md:px-12 bg-blue-50/30 border-t border-blue-100 scroll-mt-20">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center gap-12">
            <div class="w-full lg:w-1/2">
                <div class="grid grid-cols-2 gap-4">
                    <img src="{{ asset('images/uji.jpeg') }}" alt="Pengujian Air SPAM Umbulan" class="rounded-2xl shadow-md w-full h-48 object-cover transform hover:scale-105 transition duration-300 border border-blue-100" onerror="this.src='https://placehold.co/400x192/0284c7/ffffff?text=Pengujian+SPAM+Umbulan'">
                    <img src="{{ asset('images/tbm.jpeg') }}" alt="Infrastruktur SPAM Umbulan" class="rounded-2xl shadow-md w-full h-48 object-cover transform hover:scale-105 transition duration-300 mt-6 border border-blue-100" onerror="this.src='https://placehold.co/400x192/0369a1/ffffff?text=Infrastruktur+Pipa+Transmisi'">
                </div>
            </div>

            <div class="w-full lg:w-1/2 space-y-6">
                <div class="inline-flex items-center space-x-2 text-blue-600 font-bold text-xs uppercase tracking-wider">
                    <span class="w-8 h-0.5 bg-blue-600"></span>
                    <span>Proyek Kerjasama KPBU</span>
                </div>

                <h2 class="text-3xl md:text-4xl font-extrabold font-heading text-slate-900 leading-tight">
                    Infrastruktur Strategis Penyedia Air Minum Massal Jawa Timur
                </h2>

                <p class="text-slate-600 leading-relaxed text-sm md:text-base">
                    SPAM Umbulan diresmikan pada 22 Maret 2021 sebagai Proyek Strategis Nasional berbasis Kerjasama Pemerintah dengan Badan Usaha (KPBU). Dengan skema Build-Operate-Transfer (BOT) masa konsesi 25 tahun 9 bulan, proyek ini menyalurkan air minum curah dari sumber air Umbulan hingga 310.000 sambungan rumah.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                    <div class="flex items-start space-x-3 bg-white p-4 rounded-2xl border border-blue-100 shadow-xs">
                        <div class="p-3 bg-blue-50 rounded-xl text-blue-600 shrink-0">
                            <i class="fa-solid fa-handshake text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm">Skema BOT Konsesi</h4>
                            <p class="text-xs text-slate-400 mt-1">Dikelola selama 25 tahun 9 bulan sebelum diserahterimakan kembali ke pemerintah.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3 bg-white p-4 rounded-2xl border border-blue-100 shadow-xs">
                        <div class="p-3 bg-blue-50 rounded-xl text-blue-600 shrink-0">
                            <i class="fa-solid fa-house-chimney-window text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm">310.000 Sambungan Rumah</h4>
                            <p class="text-xs text-slate-400 mt-1">Memenuhi pasokan air bersih bagi masyarakat di 5 Kabupaten/Kota Jatim.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ======================================================== --}}
    {{-- CAKUPAN 5 WILAYAH LAYANAN (PUTIH & BIRU) --}}
    {{-- ======================================================== --}}
    <section id="titik-distribusi" class="py-24 px-6 md:px-12 bg-white border-t border-blue-100 scroll-mt-20">
        <div class="max-w-7xl mx-auto space-y-12">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <span class="text-blue-600 font-bold text-xs uppercase tracking-wider">Cakupan Distribusi SPAM</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold font-heading text-slate-900 mt-2">Wilayah Layanan SPAM Umbulan</h2>
                </div>
                <p class="text-slate-500 text-sm max-w-md">Air minum disalurkan melalui jalur transmisi sepanjang 93 km ke PDAM di 5 wilayah Jawa Timur.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="group bg-white rounded-3xl overflow-hidden border border-blue-100 shadow-xs hover:shadow-xl hover:border-blue-300 transition duration-300">
                    <div class="h-48 overflow-hidden relative">
                        <img src="{{ asset('images/umbulan.jpg') }}" alt="Layanan Pasuruan & Sidoarjo" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" onerror="this.src='https://placehold.co/600x192/0284c7/ffffff?text=Kota+%26+Kab.+Pasuruan'">
                    </div>
                    <div class="p-6 space-y-3">
                        <h4 class="font-bold text-slate-800 text-base">Kota & Kab. Pasuruan</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">Penyaluran air minum tahap awal langsung dari titik intake sumber Umbulan untuk kebutuhan PDAM lokal.</p>
                        <div class="pt-4 border-t border-blue-50 flex justify-between items-center text-[11px] text-slate-400">
                            <span>Status: Distribusi Berjalan</span>
                            <span class="text-blue-600 font-bold"><i class="fa-solid fa-circle-check"></i> Suplai Aktif</span>
                        </div>
                    </div>
                </div>

                <div class="group bg-white rounded-3xl overflow-hidden border border-blue-100 shadow-xs hover:shadow-xl hover:border-blue-300 transition duration-300">
                    <div class="h-48 overflow-hidden relative">
                        <img src="{{ asset('images/pdam.webp') }}" alt="Layanan Surabaya & Sidoarjo" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" onerror="this.src='https://placehold.co/600x192/0284c7/ffffff?text=Surabaya+%26+Sidoarjo'">
                    </div>
                    <div class="p-6 space-y-3">
                        <h4 class="font-bold text-slate-800 text-base">Kota Surabaya & Sidoarjo</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">Suplai air curah kapasitas besar guna mendukung kepadatan pemukiman perkotaan dan kawasan industri.</p>
                        <div class="pt-4 border-t border-blue-50 flex justify-between items-center text-[11px] text-slate-400">
                            <span>Status: Distribusi Berjalan</span>
                            <span class="text-blue-600 font-bold"><i class="fa-solid fa-circle-check"></i> Suplai Aktif</span>
                        </div>
                    </div>
                </div>

                <div class="group bg-white rounded-3xl overflow-hidden border border-blue-100 shadow-xs hover:shadow-xl hover:border-blue-300 transition duration-300">
                    <div class="h-48 overflow-hidden relative">
                        <img src="{{ asset('images/booster.jpeg') }}" alt="Layanan Kabupaten Gresik" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" onerror="this.src='https://placehold.co/600x192/0284c7/ffffff?text=Kabupaten+Gresik'">
                    </div>
                    <div class="p-6 space-y-3">
                        <h4 class="font-bold text-slate-800 text-base">Kabupaten Gresik</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">Titik distribusi hulu terjauh melalui jaringan pipa transmisi 93 km untuk pemenuhan air bersih daerah pesisir.</p>
                        <div class="pt-4 border-t border-blue-50 flex justify-between items-center text-[11px] text-slate-400">
                            <span>Status: Distribusi Berjalan</span>
                            <span class="text-blue-600 font-bold"><i class="fa-solid fa-circle-check"></i> Suplai Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ======================================================== --}}
    {{-- FOOTER (BERSIH, PUTIH & BIRU) --}}
    {{-- ======================================================== --}}
    <footer class="bg-gradient-to-b from-white to-blue-50/50 text-slate-600 pt-16 pb-8 px-6 md:px-12 border-t border-blue-100 mt-auto">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 pb-12 border-b border-blue-100">
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center p-1 shadow-md shadow-blue-500/10 border border-blue-100 shrink-0">
                        <img src="{{ asset('images/iconfav.png') }}" 
                            alt="Logo META" 
                            class="w-full h-full object-contain rounded-lg">
                    </div>
                    <span class="text-base font-extrabold text-slate-900 font-heading">Meta Adhya Tirta Umbulan</span>
                </div>
                <p class="text-xs leading-relaxed text-slate-500">
                    Badan Usaha Pelaksana Proyek Strategis Nasional KPBU SPAM Umbulan penyedia air minum curah berkapasitas 4.000 liter/detik untuk wilayah Jawa Timur.
                </p>
            </div>

            <div class="space-y-3">
                <h4 class="text-slate-900 text-xs font-bold uppercase tracking-wider">Wilayah Layanan</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="#titik-distribusi" class="hover:text-blue-600 transition">Kab. & Kota Pasuruan</a></li>
                    <li><a href="#titik-distribusi" class="hover:text-blue-600 transition">Kab. Sidoarjo</a></li>
                    <li><a href="#titik-distribusi" class="hover:text-blue-600 transition">Kota Surabaya</a></li>
                    <li><a href="#titik-distribusi" class="hover:text-blue-600 transition">Kabupaten Gresik</a></li>
                </ul>
            </div>

            <div class="space-y-3">
                <h4 class="text-slate-900 text-xs font-bold uppercase tracking-wider">Informasi Proyek</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="#about-kami" class="hover:text-blue-600 transition">Skema KPBU BOT 25 Tahun 9 Bulan</a></li>
                    <li><a href="#jalur-pipa" class="hover:text-blue-600 transition">Jalur Pipa Transmisi ±93 KM</a></li>
                    <li><a href="https://kpbu.pu.go.id" target="_blank" rel="noopener noreferrer" class="hover:text-blue-600 transition">SIMPUL KPBU Kementerian PUPR</a></li>
                </ul>
            </div>

            <div class="space-y-3">
                <h4 class="text-slate-900 text-xs font-bold uppercase tracking-wider">Layanan Penyaluran</h4>
                <ul class="space-y-2 text-xs">
                    <li class="flex justify-between"><span>Senin - Minggu:</span> <span class="text-blue-700 font-bold">24 Jam Nonstop</span></li>
                    <li class="flex justify-between"><span>Monitoring Pipa:</span> <span class="text-blue-700 font-bold">18 Stasiun Online</span></li>
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pt-8 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500">
            <p>&copy; <span id="copyright-year">2026</span> <span class="font-bold text-slate-700">PT. Meta Adhya Tirta Umbulan</span>. Seluruh hak cipta dilindungi undang-undang.</p>
            <div class="flex items-center space-x-3 mt-4 sm:mt-0">
                <a href="#" class="hover:text-blue-600 transition">Ketentuan Kerja Sama</a>
                <span>&middot;</span>
                <a href="#" class="hover:text-blue-600 transition">Kebijakan Privasi Kemitraan</a>
            </div>
        </div>
    </footer>

    {{-- ======================================================== --}}
    {{-- SCRIPT: SCROLL ANIMASI PIPA, AUTO-SCROLL (FIXED) & TOAST --}}
    {{-- ======================================================== --}}
    <script>
        document.getElementById('copyright-year').textContent = new Date().getFullYear();

        // 1. Toast Notification
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `flex items-center space-x-3 p-4 rounded-2xl shadow-xl border border-blue-100 bg-white text-slate-800 transition duration-300 transform translate-y-2 opacity-0 pointer-events-auto max-w-sm`;

            let iconColor = 'text-blue-600 bg-blue-50';
            let icon = '<i class="fa-solid fa-circle-info"></i>';
            if (type === 'error') {
                iconColor = 'text-rose-600 bg-rose-50';
                icon = '<i class="fa-solid fa-circle-exclamation"></i>';
            }

            toast.innerHTML = `
                <div class="p-2 rounded-xl ${iconColor} text-lg flex items-center justify-center">
                    ${icon}
                </div>
                <div class="flex-1">
                    <p class="text-xs font-semibold">${message}</p>
                </div>
            `;

            container.appendChild(toast);
            setTimeout(() => toast.classList.remove('translate-y-2', 'opacity-0'), 10);
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        // 2. Mobile Drawer Navigation
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileDrawer = document.getElementById('mobile-drawer');
        const mobileDrawerContent = document.getElementById('mobile-drawer-content');
        const closeDrawerBtn = document.getElementById('close-drawer-btn');
        const mobileLinks = document.querySelectorAll('.mobile-link');

        function openMenu() {
            mobileDrawer.classList.remove('hidden');
            setTimeout(() => {
                mobileDrawer.classList.add('opacity-100');
                mobileDrawerContent.classList.remove('translate-x-full');
            }, 10);
        }

        function closeMenu() {
            mobileDrawerContent.classList.add('translate-x-full');
            mobileDrawer.classList.remove('opacity-100');
            setTimeout(() => {
                mobileDrawer.classList.add('hidden');
            }, 300);
        }

        if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openMenu);
        if (closeDrawerBtn) closeDrawerBtn.addEventListener('click', closeMenu);
        if (mobileDrawer) mobileDrawer.addEventListener('click', (e) => {
            if (e.target === mobileDrawer) closeMenu();
        });
        mobileLinks.forEach(link => link.addEventListener('click', closeMenu));

        // 3. LOGIKA ANIMASI SCROLL PIPA 93 KM & 18 RUMAH METER
        const pipelineContainer = document.getElementById('pipeline-container');
        const pipeWaterFill = document.getElementById('pipe-water-fill');
        const miniProgressFill = document.getElementById('mini-progress-fill');
        const hudKm = document.getElementById('hud-km');
        const hudStation = document.getElementById('hud-station');
        const rmNodes = document.querySelectorAll('.rm-node');

        // Variabel pelacak persentase kemajuan aliran pipa secara global (0.0 - 1.0)
        let currentPipelineProgress = 0;

        function updatePipelineOnScroll() {
            if (!pipelineContainer) return;

            const rect = pipelineContainer.getBoundingClientRect();
            const windowHeight = window.innerHeight;

            // Hitung kemajuan aliran berdasarkan posisi vertikal wadah pipa di viewport
            const startTrigger = windowHeight * 0.75;
            const totalDistance = rect.height;
            const distanceCovered = startTrigger - rect.top;

            let progress = distanceCovered / totalDistance;
            progress = Math.max(0, Math.min(1, progress));
            currentPipelineProgress = progress;

            // Perbarui visual tinggi air dalam pipa utama
            const percentHeight = (progress * 100).toFixed(1);
            if (pipeWaterFill) {
                pipeWaterFill.style.height = `${percentHeight}%`;
            }
            if (miniProgressFill) {
                miniProgressFill.style.width = `${Math.max(2, percentHeight)}%`;
            }

            // Hitung KM saat ini (0.0 KM sampai 93.0 KM)
            const currentKm = (progress * 93.0).toFixed(1);
            if (hudKm) {
                hudKm.textContent = `${currentKm} KM`;
            }

            // Evaluasi stasiun Rumah Meter yang sudah tercapai oleh air
            let lastActiveStation = 'Mata Air Umbulan (Hulu Transmisi)';

            rmNodes.forEach(node => {
                const nodeRect = node.getBoundingClientRect();
                if (nodeRect.top < startTrigger) {
                    node.classList.add('active-flow');
                    lastActiveStation = node.getAttribute('data-name');
                } else {
                    node.classList.remove('active-flow');
                }
            });

            if (progress >= 0.98) {
                lastActiveStation = 'Reservoir Giri Gresik (Hilir Transmisi 93 KM)';
            }

            if (hudStation) {
                hudStation.textContent = lastActiveStation;
            }
        }

        window.addEventListener('scroll', updatePipelineOnScroll, { passive: true });
        window.addEventListener('resize', updatePipelineOnScroll, { passive: true });
        setTimeout(updatePipelineOnScroll, 100);

        // 4. LOGIKA ALIRAN OTOMATIS (AUTO-SCROLL) TANPA STUCK DI WARU
        let autoScrollInterval = null;
        let isAutoScrolling = false;

        function toggleAutoScroll() {
            if (!isAutoScrolling) {
                // Jika sudah berada di titik akhir (93 KM), reset ke hulu dahulu lalu mulai
                if (currentPipelineProgress >= 0.98) {
                    resetToHulu();
                    setTimeout(() => startAutoScroll(), 600);
                    return;
                }
                startAutoScroll();
            } else {
                stopAutoScroll();
            }
        }

        function startAutoScroll() {
            isAutoScrolling = true;
            const btnText = document.getElementById('btn-auto-text');
            const btnIcon = document.getElementById('btn-auto-icon');
            if (btnText) btnText.textContent = 'Jeda Aliran';
            if (btnIcon) btnIcon.className = 'fa-solid fa-pause text-[10px]';

            clearInterval(autoScrollInterval);

            autoScrollInterval = setInterval(() => {
                if (!isAutoScrolling) {
                    clearInterval(autoScrollInterval);
                    return;
                }

                // Berhenti hanya jika air telah mengalir sampai titik hilir akhir (93 KM)
                if (currentPipelineProgress >= 0.99) {
                    stopAutoScroll();
                    return;
                }

                // Pengaman batas bawah dokumen
                if ((window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 15)) {
                    stopAutoScroll();
                    return;
                }

                // Alirkan ke bawah secara halus
                window.scrollBy({ top: 3.5, behavior: 'auto' });
            }, 16);
        }

        function stopAutoScroll() {
            isAutoScrolling = false;
            clearInterval(autoScrollInterval);
            const btnText = document.getElementById('btn-auto-text');
            const btnIcon = document.getElementById('btn-auto-icon');
            if (btnText) btnText.textContent = 'Alirkan Otomatis';
            if (btnIcon) btnIcon.className = 'fa-solid fa-play text-[10px]';
        }

        function resetToHulu() {
            stopAutoScroll();
            const jalurSection = document.getElementById('jalur-pipa');
            if (jalurSection) {
                jalurSection.scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Hentikan auto-scroll jika pengguna melakukan scrolling manual dengan mouse/touch
        window.addEventListener('wheel', (e) => {
            if (isAutoScrolling && Math.abs(e.deltaY) > 6) {
                stopAutoScroll();
            }
        }, { passive: true });

        window.addEventListener('touchmove', () => {
            if (isAutoScrolling) stopAutoScroll();
        }, { passive: true });

        // 5. Filter Wilayah Rumah Meter
        function filterRegion(region) {
            const buttons = document.querySelectorAll('.region-filter-btn');
            buttons.forEach(btn => {
                if (btn.getAttribute('data-region') === region) {
                    btn.className = 'region-filter-btn px-4 py-2 rounded-xl text-xs font-bold border transition bg-blue-600 text-white border-blue-600 shadow-xs';
                } else {
                    btn.className = 'region-filter-btn px-4 py-2 rounded-xl text-xs font-bold border transition bg-white text-slate-600 border-blue-100 hover:border-blue-300';
                }
            });

            rmNodes.forEach(node => {
                const nodeRegion = node.getAttribute('data-region');
                if (region === 'all' || nodeRegion === region) {
                    node.style.display = 'flex';
                } else {
                    node.style.display = 'none';
                }
            });

            if (region !== 'all') {
                const firstNodeInRegion = document.querySelector(`.rm-node[data-region="${region}"]`);
                if (firstNodeInRegion) {
                    firstNodeInRegion.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }
    </script>

    <div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3 pointer-events-auto"></div>

    {{-- Pendaftaran Skrip PWA --}}
    @laravelPwa
    @pwaInstallButton
</body>
</html>
