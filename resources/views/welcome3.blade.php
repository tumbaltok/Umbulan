<!DOCTYPE html>
<html lang="id" class="scroll-smooth h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SKRIP DETEKSI PWA: AUTO REDIRECT KE LOGIN JIKA DIBUKA DARI APP INSTALAN HP -->
    <script>
        (function() {
            const isPWA = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
            if (isPWA) {
                window.location.replace("{{ route('login') }}");
            }
        })();
    </script>

    <!-- Meta Warna Background untuk Mobile Status Bar -->
    <meta name="theme-color" content="#ffffff">
    <meta name="background-color" content="#ffffff">

    <title>META Adhya Tirta Umbulan</title>
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}?v=2">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
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
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            950: '#082f49',
                            accent: '#272EF5'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    <!-- PWA Head -->
    @pwaHead
</head>
<body class="font-sans text-slate-800 antialiased bg-slate-50/50 flex flex-col min-h-screen">

    <header class="sticky top-0 bg-white/80 backdrop-blur-md border-b border-slate-100 h-20 flex items-center justify-between px-6 md:px-12 z-50 shadow-sm">
        <a href="#" class="flex items-center space-x-3 group">
            <!-- BINGKAI LOGO HEADER: Diubah menjadi object-contain + p-1 agar tajam dan tidak di-crop -->
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center p-1 shadow-md border border-slate-100 transition duration-300 group-hover:scale-105 shrink-0">
                <img src="{{ asset('images/iconfav.png') }}" 
                    alt="Logo META" 
                    class="w-full h-full object-contain rounded-full">
            </div>
            <div class="flex flex-col">
                <span class="text-lg font-extrabold tracking-tight text-water-950 font-heading leading-tight">META <span class="text-water-600">Adhya Tirta Umbulan</span></span>
                <span class="text-[10px] font-bold text-slate-400 tracking-wider uppercase">
                    Badan Usaha Pelaksana KPBU SPAM Umbulan
                </span>
            </div>
        </a>

        <div class="hidden md:flex items-center space-x-8">
            <nav class="flex items-center space-x-8 text-sm font-semibold text-slate-600 mr-2">
                <a href="#about-kami" class="hover:text-water-600 transition">Tentang Proyek</a>
                <a href="#titik-distribusi" class="hover:text-water-600 transition">Wilayah Layanan</a>
            </nav>
            <a href="/login" onclick="showToast('Mengarahkan ke Portal Karyawan...', 'info')" class="px-5 py-2.5 bg-gradient-to-r from-water-600 to-water-700 hover:from-water-700 hover:to-water-800 active:scale-95 text-white text-sm font-bold rounded-xl shadow-md shadow-water-600/20 transition-all duration-150 flex items-center space-x-2">
                <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                <span>Portal Karyawan</span>
            </a>
        </div>

        <button id="mobile-menu-btn" class="md:hidden p-2 text-slate-600 hover:text-water-600 focus:outline-none" aria-label="Toggle Menu">
            <i class="fa-solid fa-bars text-2xl"></i>
        </button>
    </header>

    <div id="mobile-drawer" class="fixed inset-0 bg-slate-900/60 z-50 backdrop-blur-xs transition-opacity duration-300 hidden opacity-0">
        <div class="fixed top-0 right-0 bottom-0 w-4/5 max-w-sm bg-white p-6 shadow-2xl flex flex-col justify-between transform translate-x-full transition-transform duration-300" id="mobile-drawer-content">
            <div>
                <div class="flex items-center justify-between pb-6 border-b border-slate-100">
                    <span class="font-extrabold text-lg text-water-950 font-heading">Menu Navigasi</span>
                    <button id="close-drawer-btn" class="p-2 text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-2xl"></i>
                    </button>
                </div>
                <nav class="flex flex-col space-y-4 mt-8">
                    <a href="#about-kami" class="mobile-link text-base font-semibold text-slate-600 hover:text-water-600 transition py-2 border-b border-slate-50">Tentang Proyek</a>
                    <a href="#titik-distribusi" class="mobile-link text-base font-semibold text-slate-600 hover:text-water-600 transition py-2 border-b border-slate-50">Wilayah Layanan</a>
                </nav>
            </div>
            <div class="pt-6 border-t border-slate-100">
                <a href="/login" onclick="showToast('Mengarahkan ke Portal Karyawan...', 'info')" class="w-full py-3.5 bg-gradient-to-r from-water-600 to-water-700 text-white text-center font-bold rounded-2xl shadow-lg shadow-water-600/20 block">
                    <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Portal Karyawan
                </a>
            </div>
        </div>
    </div>

    <section class="relative bg-gradient-to-b from-water-50 to-white pt-16 pb-24 md:pt-24 md:pb-32 px-6 md:px-12 overflow-hidden">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-water-100 rounded-full blur-3xl opacity-60"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-slate-100 rounded-full blur-3xl opacity-60"></div>

        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center gap-12 relative z-10">
            <div class="w-full lg:w-1/2 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center space-x-2.5 px-4 py-2 bg-water-100 text-water-700 text-xs font-bold rounded-full border border-water-500/20 shadow-xs">
                    <i class="fa-solid fa-building-shield text-sm"></i>
                    <span>PROYEK STRATEGIS NASIONAL (KPBU SPAM UMBULAN)</span>
                </div>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black font-heading text-slate-900 leading-tight">
                    Pengelolaan SPAM <br class="hidden md:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-water-600 to-water-500">Kapasitas 4.000 Liter/Detik</span>
                </h1>

                <p class="text-slate-500 text-base md:text-lg leading-relaxed max-w-xl mx-auto lg:mx-0">
                    PT. Meta Adhya Tirta Umbulan mengelola Sistem Penyediaan Air Minum (SPAM) Umbulan dengan skema Build-Operate-Transfer (BOT) konsesi 25 tahun 9 bulan untuk memenuhi kebutuhan air bersih masyarakat Jawa Timur.
                </p>

                <div class="flex flex-wrap justify-center lg:justify-start gap-4 pt-4">
                    <div class="flex items-center space-x-2 px-3 py-1.5 bg-white border border-slate-100 rounded-xl shadow-xs">
                        <i class="fa-solid fa-route text-water-500"></i>
                        <span class="text-xs font-semibold text-slate-600">Pipa Transmisi ±93 KM</span>
                    </div>
                    <div class="flex items-center space-x-2 px-3 py-1.5 bg-white border border-slate-100 rounded-xl shadow-xs">
                        <i class="fa-solid fa-users text-blue-500"></i>
                        <span class="text-xs font-semibold text-slate-600">1,3 Juta Jiwa Penerima Manfaat</span>
                    </div>
                </div>

                <div class="pt-6 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="#about-kami" class="w-full sm:w-auto px-8 py-4 bg-water-600 hover:bg-water-700 active:scale-95 text-white font-bold rounded-2xl text-center shadow-lg shadow-water-600/25 transition-all duration-150">
                        Detail Proyek SPAM
                    </a>
                    <a href="#titik-distribusi" class="w-full sm:w-auto px-8 py-4 bg-white hover:bg-slate-50 border border-slate-200 active:scale-95 text-slate-700 font-bold rounded-2xl text-center shadow-sm transition-all duration-150">
                        Cakupan Wilayah
                    </a>
                </div>
            </div>

            <div class="w-full lg:w-1/2 flex justify-center">
                <div class="relative w-full max-w-md md:max-w-lg">
                    <div class="absolute inset-0 bg-gradient-to-tr from-water-500/20 to-slate-500/20 rounded-3xl blur-2xl transform rotate-6 scale-95"></div>

                    <div class="relative bg-white border border-slate-100 p-6 md:p-8 rounded-3xl shadow-2xl space-y-6 transition-all duration-300 hover:shadow-water-500/10 hover:shadow-3xl">

                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Status Operasional Proyek</span>
                            <span class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                                <span class="relative flex h-2 w-2 mr-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                RESMI BEROPERASI
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-slate-50 border border-slate-100/80 rounded-2xl transition-all duration-200 hover:bg-slate-100/50">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kapasitas Desain</span>
                                <h4 class="text-xl font-extrabold text-slate-800 mt-1">4.000 <span class="text-xs font-semibold text-slate-500">lps</span></h4>
                            </div>
                            <div class="p-4 bg-slate-50 border border-slate-100/80 rounded-2xl transition-all duration-200 hover:bg-slate-100/50">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Target Sambungan</span>
                                <h4 class="text-xl font-extrabold text-slate-800 mt-1">310.000 <span class="text-xs font-semibold text-slate-500">SR</span></h4>
                            </div>
                        </div>

                        <!-- Bagian Debit Real-Time -->
                        <div class="p-4 bg-blue-50/50 border border-blue-100/80 rounded-2xl">
                            <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wider block">Debit Aliran Transmisi (Real-time)</span>
                            <h4 class="text-2xl font-black text-water-600 mt-1"><span id="current-flow-display">2.700</span> <span class="text-xs font-semibold text-slate-500">lps</span></h4>
                        </div>

                        <!-- Bagian Volume Serapan Bulanan Bawah -->
                        <div class="relative p-5 bg-gradient-to-r from-cyan-600 to-slate-800 text-white rounded-2xl overflow-hidden shadow-md group">
                            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl transition-all duration-500 group-hover:scale-150"></div>

                            <div class="relative z-10">
                                <p class="text-xs text-cyan-100/90 font-semibold tracking-wide">Volume Distibusi Akumulatif Bulan Ini</p>
                                <h3 id="volume-display" class="text-2xl md:text-3xl font-black mt-1 tracking-tight">
                                    0 <span class="text-sm font-medium text-cyan-300">m³</span>
                                </h3>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
                            <span class="flex items-center gap-1.5 font-medium">
                                <span class="w-1.5 h-1.5 bg-water-500 rounded-full animate-pulse"></span>
                                Pemantauan SIMPUL KPBU PUPR
                            </span>
                            <div class="p-2 bg-water-50 text-water-600 rounded-xl">
                                <i class="fa-solid fa-gauge-high text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12 bg-slate-900 text-white px-6 md:px-12 relative overflow-hidden">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10 text-center">
            <div class="space-y-1 border-b md:border-b-0 md:border-r border-slate-800 pb-6 md:pb-0 last:border-none">
                <span class="text-4xl md:text-5xl font-black font-heading text-water-400">1,3 Juta</span>
                <p class="text-xs md:text-sm font-semibold text-slate-400">Penerima Manfaat Air Bersih</p>
            </div>
            <div class="space-y-1 border-b md:border-b-0 md:border-r border-slate-800 pb-6 md:pb-0 last:border-none">
                <span class="text-4xl md:text-5xl font-black font-heading text-white">93 KM</span>
                <p class="text-xs md:text-sm font-semibold text-slate-400">Panjang Pipa Transmisi Utama</p>
            </div>
            <div class="space-y-1 last:border-none">
                <span class="text-4xl md:text-5xl font-black font-heading text-water-400">5 Wilayah</span>
                <p class="text-xs md:text-sm font-semibold text-slate-400">Kabupaten/Kota Terlayani di Jatim</p>
            </div>
        </div>
    </section>

    <section id="about-kami" class="py-24 px-6 md:px-12 bg-white scroll-mt-20">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center gap-12">
            <div class="w-full lg:w-1/2">
                <div class="grid grid-cols-2 gap-4">
                    <img src="{{ asset('images/uji.jpeg') }}" alt="Pengujian Air SPAM Umbulan" class="rounded-2xl shadow-md w-full h-48 object-cover transform hover:scale-105 transition duration-300" onerror="this.src='https://placehold.co/400x192/0284c7/ffffff?text=Pengujian+SPAM+Umbulan'">
                    <img src="{{ asset('images/tbm.jpeg') }}" alt="Infrastruktur SPAM Umbulan" class="rounded-2xl shadow-md w-full h-48 object-cover transform hover:scale-105 transition duration-300 mt-6" onerror="this.src='https://placehold.co/400x192/475569/ffffff?text=Infrastruktur+Pipa+Transmisi'">
                </div>
            </div>

            <div class="w-full lg:w-1/2 space-y-6">
                <div class="inline-flex items-center space-x-2 text-water-600 font-bold text-xs uppercase tracking-wider">
                    <span class="w-8 h-0.5 bg-water-600"></span>
                    <span>Detail Proyek KPBU</span>
                </div>

                <h2 class="text-3xl md:text-4xl font-extrabold font-heading text-slate-900 leading-tight">
                    Infrastruktur Strategis Penyedia Air Minum Massal Jawa Timur
                </h2>

                <p class="text-slate-500 leading-relaxed text-sm md:text-base">
                    SPAM Umbulan diresmikan pada 22 Maret 2021 sebagai Proyek Strategis Nasional berbasis Kerjasama Pemerintah dengan Badan Usaha (KPBU). Dengan skema Build-Operate-Transfer (BOT) masa konsesi 25 tahun 9 bulan, proyek ini menyalurkan air minum curah dari sumber air Umbulan hingga 310.000 sambungan rumah.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                    <div class="flex items-start space-x-3">
                        <div class="p-3 bg-water-50 rounded-xl text-water-600 shrink-0">
                            <i class="fa-solid fa-handshake text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm">Skema BOT Konsesi</h4>
                            <p class="text-xs text-slate-400 mt-1">Dikelola selama 25 tahun 9 bulan sebelum diserahterimakan kembali ke pemerintah.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="p-3 bg-slate-100 rounded-xl text-slate-600 shrink-0">
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

    <section id="titik-distribusi" class="py-24 px-6 md:px-12 bg-slate-50 border-t border-slate-100 scroll-mt-20">
        <div class="max-w-7xl mx-auto space-y-12">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <span class="text-water-600 font-bold text-xs uppercase tracking-wider">Cakupan Distribusi SPAM</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold font-heading text-slate-900 mt-2">Wilayah Layanan SPAM Umbulan</h2>
                </div>
                <p class="text-slate-500 text-sm max-w-md">Air minum disalurkan melalui jalur transmisi sepanjang 93 km ke PDAM di 5 wilayah Jawa Timur.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="group bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-xl transition duration-300">
                    <div class="h-48 overflow-hidden relative">
                        <img src="{{ asset('images/pdam.webp') }}" alt="Layanan Pasuruan & Sidoarjo" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" onerror="this.src='https://placehold.co/600x192/0284c7/ffffff?text=Kota+%26+Kab.+Pasuruan'">
                    </div>
                    <div class="p-6 space-y-3">
                        <h4 class="font-bold text-slate-800 text-base">Kota & Kab. Pasuruan</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Penyaluran air minum tahap awal langsung dari titik intake sumber Umbulan untuk kebutuhan PDAM lokal.</p>
                        <div class="pt-4 border-t border-slate-100 flex justify-between items-center text-[11px] text-slate-400">
                            <span>Status: Distribusi Berjalan</span>
                            <span class="text-emerald-600 font-bold"><i class="fa-solid fa-circle-check"></i> Suplai Aktif</span>
                        </div>
                    </div>
                </div>

                <div class="group bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-xl transition duration-300">
                    <div class="h-48 overflow-hidden relative">
                        <img src="{{ asset('images/umbulan.jpg') }}" alt="Layanan Surabaya & Sidoarjo" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" onerror="this.src='https://placehold.co/600x192/0284c7/ffffff?text=Surabaya+%26+Sidoarjo'">
                    </div>
                    <div class="p-6 space-y-3">
                        <h4 class="font-bold text-slate-800 text-base">Kota Surabaya & Sidoarjo</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Suplai air curah kapasitas besar guna mendukung kepadatan pemukiman perkotaan dan kawasan industri.</p>
                        <div class="pt-4 border-t border-slate-100 flex justify-between items-center text-[11px] text-slate-400">
                            <span>Status: Distribusi Berjalan</span>
                            <span class="text-emerald-600 font-bold"><i class="fa-solid fa-circle-check"></i> Suplai Aktif</span>
                        </div>
                    </div>
                </div>

                <div class="group bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-xl transition duration-300">
                    <div class="h-48 overflow-hidden relative">
                        <img src="{{ asset('images/booster.jpeg') }}" alt="Layanan Kabupaten Gresik" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" onerror="this.src='https://placehold.co/600x192/0284c7/ffffff?text=Kabupaten+Gresik'">
                    </div>
                    <div class="p-6 space-y-3">
                        <h4 class="font-bold text-slate-800 text-base">Kabupaten Gresik</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Titik distribusi hulu terjauh melalui jaringan pipa transmisi 93 km untuk pemenuhan air bersih daerah pesisir.</p>
                        <div class="pt-4 border-t border-slate-100 flex justify-between items-center text-[11px] text-slate-400">
                            <span>Status: Distribusi Berjalan</span>
                            <span class="text-emerald-600 font-bold"><i class="fa-solid fa-circle-check"></i> Suplai Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-slate-900 text-slate-400 pt-16 pb-8 px-6 md:px-12 border-t border-slate-800 mt-auto">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 pb-12 border-b border-slate-800">
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <!-- BINGKAI LOGO FOOTER: Diubah menjadi object-contain + p-1 agar tajam dan tidak di-crop -->
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center p-1 shadow-lg shrink-0">
                        <img src="{{ asset('images/iconfav.png') }}" 
                            alt="Logo META" 
                            class="w-full h-full object-contain rounded-full">
                    </div>
                    <span class="text-base font-extrabold text-white">Meta Adhya Tirta Umbulan</span>
                </div>
                <p class="text-xs leading-relaxed">
                    Badan Usaha Pelaksana Proyek Strategis Nasional KPBU SPAM Umbulan penyedia air minum berkapasitas 4.000 liter/detik untuk wilayah Jawa Timur.
                </p>
            </div>

            <div class="space-y-3">
                <h4 class="text-white text-xs font-bold uppercase tracking-wider">Wilayah Layanan</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="#titik-distribusi" class="hover:text-white transition">Pasuruan & Sidoarjo</a></li>
                    <li><a href="#titik-distribusi" class="hover:text-white transition">Surabaya & Gresik</a></li>
                </ul>
            </div>

            <div class="space-y-3">
                <h4 class="text-white text-xs font-bold uppercase tracking-wider">Informasi Proyek</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="#about-kami" class="hover:text-white transition">Skema KPBU BOT 25 Tahun 9 Bulan</a></li>
                    <li><a href="https://kpbu.pu.go.id" target="_blank" rel="noopener noreferrer" class="hover:text-white transition">SIMPUL KPBU Kementerian PUPR</a></li>
                </ul>
            </div>

            <div class="space-y-3">
                <h4 class="text-white text-xs font-bold uppercase tracking-wider">Jam Operasional Layanan</h4>
                <ul class="space-y-2 text-xs">
                    <li class="flex justify-between"><span>Senin - Minggu:</span> <span class="text-white">24 Jam Nonstop</span></li>
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pt-8 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500">
            <p>&copy; <span id="copyright-year">2026</span> <span class="font-bold">PT. Meta Adhya Tirta Umbulan</span>. Seluruh hak cipta dilindungi undang-undang.</p>
            <div class="flex items-center space-x-3 mt-4 sm:mt-0">
                <a href="#" class="hover:text-white transition">Ketentuan Kerja Sama</a>
                <span>&middot;</span>
                <a href="#" class="hover:text-white transition">Kebijakan Privasi Kemitraan</a>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('copyright-year').textContent = new Date().getFullYear();

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `flex items-center space-x-3 p-4 rounded-2xl shadow-xl border bg-white text-slate-800 transition duration-300 transform translate-y-2 opacity-0 pointer-events-auto max-w-sm`;

            let iconColor = 'text-emerald-500 bg-emerald-50';
            let icon = '<i class="fa-solid fa-circle-check"></i>';
            if (type === 'error') {
                iconColor = 'text-rose-500 bg-rose-50';
                icon = '<i class="fa-solid fa-circle-exclamation"></i>';
            } else if (type === 'info') {
                iconColor = 'text-blue-500 bg-blue-50';
                icon = '<i class="fa-solid fa-circle-info"></i>';
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
            }, 4000);
        }

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

        mobileMenuBtn.addEventListener('click', openMenu);
        closeDrawerBtn.addEventListener('click', closeMenu);
        mobileDrawer.addEventListener('click', (e) => {
            if (e.target === mobileDrawer) closeMenu();
        });
        mobileLinks.forEach(link => {
            link.addEventListener('click', closeMenu);
        });

        // ========================================================
        // KODE PERBAIKAN: REAL-TIME FLOW GENERATOR & ACCUMULATOR
        // ========================================================
        let totalAccumulatedVolumeM3 = 0;

        function initAndUpdateFlow() {
            const sekarang = new Date();

            // Menggunakan waktu lokal awal bulan tanggal 1 jam 00:00:00 (WIB/Wita/Wit sesuai lokal)
            const awalBulan = new Date(sekarang.getFullYear(), sekarang.getMonth(), 1, 0, 0, 0);

            // Menghitung berapa detik yang telah terlewati sejak awal bulan
            const selisihMilidetik = sekarang - awalBulan;
            const totalDetikLalu = Math.floor(selisihMilidetik / 1000);

            // Menggunakan nilai rata-rata tengah (2.725 m3 per detik) sebagai acuan historis awal bulan
            const rataRataM3 = 2.725;
            totalAccumulatedVolumeM3 = totalDetikLalu * rataRataM3;

            // Jalankan fungsi update agar nilai langsung berubah seketika halaman dibuka
            updateVolumeRealtime();
        }

        function updateVolumeRealtime() {
            // 1. BUAT DEBIT PER DETIK DINAMIS (Range 2700 - 2750 lps)
            const randomLps = Math.floor(Math.random() * (2750 - 2700 + 1)) + 2700;

            // Format angka dengan pemisah ribuan lokal Indonesia (id-ID)
            const formatLps = new Intl.NumberFormat('id-ID').format(randomLps);

            // Memperbarui tampilan Debit Aliran secara live
            document.getElementById('current-flow-display').textContent = formatLps;

            // 2. HITUNG VOLUME AKUMULATIF BULANAN (Sum real-time per detik)
            // Ubah lps ke meter kubik per detik (dibagi 1000)
            const flowM3PerDetik = randomLps / 1000;

            // Jumlahkan penambahan air detik ini ke total akumulasi
            totalAccumulatedVolumeM3 += flowM3PerDetik;

            // Format angka sum tanpa desimal
            const formatTotalVolume = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(totalAccumulatedVolumeM3);

            // Perbarui tampilan Volume Serapan Akumulatif
            document.getElementById('volume-display').innerHTML = `
                ${formatTotalVolume} <span class="text-sm font-medium text-cyan-300">m³</span>
            `;
        }

        // Inisialisasi total volume awal bulan saat halaman dimuat
        initAndUpdateFlow();

        // Set interval untuk terus memperbarui data acak & sum setiap 1 detik berkelanjutan
        setInterval(updateVolumeRealtime, 1000);
    </script>

    <div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3 pointer-events-none"></div>

    <!-- PWA Script Registrations & Tools -->
    @laravelPwa
    @pwaInstallButton
</body>
</html>