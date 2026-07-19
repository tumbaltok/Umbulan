<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}">
    <title>PT.META ADHYA TIRTA UMBULAN - Transmisi Air Bersih Umbulan</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        water: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        pure: {
                            50: '#f0fdfa',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .wave-bg {
            background-image: radial-gradient(circle at 15% 20%, rgba(14, 165, 233, 0.08) 0%, transparent 40%),
                              radial-gradient(circle at 85% 80%, rgba(20, 184, 166, 0.08) 0%, transparent 40%);
        }
        .pipeline-pulse {
            stroke-dasharray: 8 4;
            animation: flow 3s linear infinite;
        }
        @keyframes flow {
            to {
                stroke-dashoffset: -20;
            }
        }
    </style>
    <!-- PWA Head -->
    @pwaHead
</head>
<body class="bg-slate-950 text-slate-100 antialiased wave-bg min-h-screen flex flex-col">

    <!-- NAVIGATION BAR -->
    <header class="sticky top-0 z-50 bg-slate-950/90 backdrop-blur-md border-b border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 shrink-0 bg-gradient-to-tr from-water-500 to-pure-500 rounded-full flex items-center justify-center text-white shadow-lg shadow-water-500/20">
                        <img src="{{ asset('images/iconfav.png') }}" alt="Logo" class="w-full h-full object-cover rounded-full">
                        {{-- <i class="fa-solid fa-circle-nodes text-xl"></i> --}}
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-water-400 to-pure-400 bg-clip-text ">META ADHYA TIRTA UMBULAN</span>
                        {{-- <span class="block text-[9px] text-slate-500 font-bold tracking-widest uppercase">Umbulan Transmission</span> --}}
                    </div>
                </div>

                <!-- Desktop Nav Links -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="#tentang" class="text-sm font-medium text-slate-400 hover:text-water-400 transition-colors">Sistem Kami</a>
                    <a href="#transmisi" class="text-sm font-medium text-slate-400 hover:text-water-400 transition-colors">Jalur Transmisi</a>
                    <a href="#distribusi" class="text-sm font-medium text-slate-400 hover:text-water-400 transition-colors">18 Rumah Meter</a>
                    <a href="#infrastruktur" class="text-sm font-medium text-slate-400 hover:text-water-400 transition-colors">Infrastruktur</a>
                    {{-- <a href="#kolaborasi" class="text-sm font-medium text-slate-400 hover:text-water-400 transition-colors">Portal PDAM</a> --}}
                </nav>

                <!-- CTA Button Desktop -->
                <div class="hidden md:flex items-center gap-4">
                    <a href="login" class="px-5 py-2.5 bg-gradient-to-r from-water-600 to-pure-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-water-600/30 transition-all transform hover:-translate-y-0.5">
                        LOGIN <i class="fa fa-sign-in" aria-hidden="true"></i>
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 text-slate-400 hover:text-water-400 focus:outline-none" aria-label="Toggle Menu">
                    <i class="fa-solid fa-bars text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div id="mobile-menu" class="hidden md:hidden bg-slate-950 border-b border-slate-900 px-4 pt-2 pb-6 space-y-3 shadow-lg">
            <a href="#tentang" class="block px-4 py-2 rounded-lg text-slate-400 hover:bg-slate-900 font-medium">Sistem Kami</a>
            <a href="#transmisi" class="block px-4 py-2 rounded-lg text-slate-400 hover:bg-slate-900 font-medium">Jalur Transmisi</a>
            <a href="#distribusi" class="block px-4 py-2 rounded-lg text-slate-400 hover:bg-slate-900 font-medium">18 Rumah Meter</a>
            <a href="#infrastruktur" class="block px-4 py-2 rounded-lg text-slate-400 hover:bg-slate-900 font-medium">Infrastruktur</a>
            {{-- <a href="#kolaborasi" class="block px-4 py-2 rounded-lg text-slate-400 hover:bg-slate-900 font-medium">Portal PDAM</a> --}}
            <div class="pt-2 border-t border-slate-900">
                <a href="login" class="block w-full text-center px-4 py-3 bg-gradient-to-r from-water-600 to-pure-600 text-white font-semibold rounded-xl">
                    LOGIN <i class="fa fa-sign-in" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="flex-grow">

        <!-- HERO SECTION -->
        <section class="relative pt-12 pb-24 md:py-32 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                    <!-- Left Hero Content -->
                    <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-water-950/80 border border-water-900/60 text-water-400 rounded-full text-xs font-semibold uppercase tracking-wider">
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-pure-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-pure-500"></span>
                            </span>
                            Jalur Transmisi Air Bersih Regional Jawa Timur
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-none">
                            Mengalirkan <br>
                            <span class="bg-gradient-to-r from-water-400 to-pure-400 bg-clip-text text-transparent">Mata Air Umbulan</span> <br>
                            Lintas 4 Kota Utama.
                        </h1>
                        <p class="text-base sm:text-lg text-slate-400 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                            PT META ADHYA TIRTA UMBULAN   mengoperasikan jalur pipa transmisi utama yang menyalurkan aliran air bersih berkualitas tinggi langsung dari sumber mata air alami **Umbulan** menuju 18 titik rumah meter strategis PDAM di Kota Pasuruan, Sidoarjo, Surabaya, hingga Gresik.
                        </p>

                        <!-- CTA & Stats Grid -->
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                            <a href="#transmisi" class="w-full sm:w-auto text-center px-8 py-4 bg-gradient-to-r from-water-600 to-pure-600 text-white font-bold rounded-2xl hover:shadow-xl hover:shadow-water-600/30 transition-all transform hover:-translate-y-1">
                                Pantau Aliran Live
                            </a>
                            <a href="#distribusi" class="w-full sm:w-auto text-center px-8 py-4 bg-slate-900 border border-slate-800 text-slate-300 font-bold rounded-2xl hover:bg-slate-800/80 transition-all">
                                Distribusi Rumah Meter
                            </a>
                        </div>

                        <!-- Mini Stats Corporate -->
                        <div class="grid grid-cols-3 gap-4 pt-8 border-t border-slate-900 max-w-md mx-auto lg:mx-0">
                            <div>
                                <p class="text-2xl sm:text-3xl font-extrabold text-white">4 Kota</p>
                                <p class="text-xs text-slate-500">Cakupan Distribusi</p>
                            </div>
                            <div>
                                <p class="text-2xl sm:text-3xl font-extrabold text-white">18 Titik</p>
                                <p class="text-xs text-slate-500">Rumah Meter PDAM</p>
                            </div>
                            <div>
                                <p class="text-2xl sm:text-3xl font-extrabold text-white">2 Stasiun</p>
                                <p class="text-xs text-slate-500">Rumah Pompa Aktif</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Hero Graphic -->
                    <div class="lg:col-span-5 flex justify-center relative">
                        <div class="absolute -inset-1 bg-gradient-to-r from-water-500 to-pure-500 rounded-3xl blur-2xl opacity-15 animate-pulse"></div>
                        <div class="relative bg-slate-950 p-8 rounded-3xl shadow-2xl border border-slate-800 max-w-md w-full">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-xs font-bold text-slate-500 tracking-widest uppercase">Skema Teknis Utama</span>
                                <span class="px-2 py-0.5 bg-emerald-950 border border-emerald-800 text-emerald-400 text-[10px] rounded font-bold">ALIRAN NORMAL</span>
                            </div>

                            <!-- SVG Pipeline transmission line schematic -->
                            <svg viewBox="0 0 350 250" class="w-full h-auto" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <!-- Background connections -->
                                <path d="M 50,150 Q 120,50 200,150 T 320,150" stroke="#1e293b" stroke-width="8" stroke-linecap="round" fill="none"/>
                                <path d="M 50,150 Q 120,50 200,150 T 320,150" stroke="#0ea5e9" stroke-width="4" stroke-linecap="round" fill="none" class="pipeline-pulse"/>

                                <!-- Umbulan Source (Left Node) -->
                                <circle cx="50" cy="150" r="14" fill="#0c4a6e" stroke="#0ea5e9" stroke-width="3" />
                                <circle cx="50" cy="150" r="6" fill="#14b8a6" />

                                <!-- Booster Station Surabaya (Middle-right Node) -->
                                <circle cx="230" cy="120" r="12" fill="#0f766e" stroke="#14b8a6" stroke-width="2" />
                                <path d="M 226,116 L 234,124 M 234,116 L 226,124" stroke="#ffffff" stroke-width="2"/>

                                <!-- Gresik End (Right Node) -->
                                <circle cx="320" cy="150" r="10" fill="#1e293b" stroke="#64748b" stroke-width="2" />
                                <circle cx="320" cy="150" r="4" fill="#0ea5e9" />

                                <!-- Annotations -->
                                <text x="25" y="182" fill="#94a3b8" font-size="9" font-family="sans-serif" font-weight="bold">UMBULAN (PUSAT)</text>
                                <text x="180" y="95" fill="#94a3b8" font-size="9" font-family="sans-serif" font-weight="bold">BOOSTER SBY</text>
                                <text x="290" y="182" fill="#94a3b8" font-size="9" font-family="sans-serif" font-weight="bold">GRESIK (TAIL)</text>

                                <path d="M 50,130 V 100 H 90" stroke="#475569" stroke-width="1"/>
                                <text x="96" y="103" fill="#38bdf8" font-size="8" font-family="sans-serif" font-weight="bold">4.000 L/DTK</text>
                            </svg>

                            <div class="mt-6 space-y-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-400">Total Panjang Jalur:</span>
                                    <span class="font-bold text-white">~93.2 Kilometer</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-400">Jenis Pipa:</span>
                                    <span class="font-bold text-white">Steel Pipe DN1200 - DN1000</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- CORE SYSTEM OVERVIEW -->
        <section id="tentang" class="py-24 bg-slate-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-base font-semibold text-pure-400 uppercase tracking-widest">Spesialisasi Transmisi</h2>
                    <p class="text-3xl sm:text-4xl font-extrabold text-white mt-2 tracking-tight">Kualifikasi Sistem Transmisi Umbulan</p>
                    <p class="text-lg text-slate-400 mt-4">Berbeda dengan sistem pengolahan kimiawi (WTP), air dari Mata Air Umbulan telah memiliki kualitas air minum alami yang sangat murni dari hulu. Fokus utama kami adalah transmisi dan distribusi tanpa kontaminasi.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-slate-900 p-8 rounded-2xl border border-slate-800 hover:border-water-500/50 hover:shadow-xl transition-all duration-300">
                        <div class="w-12 h-12 bg-water-950 text-water-400 rounded-xl flex items-center justify-center mb-6 border border-water-800">
                            <i class="fa-solid fa-droplet-slash text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Tanpa Proses Kimia Berat</h3>
                        <p class="text-slate-400 leading-relaxed text-sm">Sumber Umbulan langsung disalurkan tanpa membutuhkan instalasi penjernihan kimiawi yang kompleks karena tingkat kemurnian alami mata air yang sangat tinggi dari pegunungan.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-slate-900 p-8 rounded-2xl border border-slate-800 hover:border-pure-500/50 hover:shadow-xl transition-all duration-300">
                        <div class="w-12 h-12 bg-pure-950 text-pure-400 rounded-xl flex items-center justify-center mb-6 border border-pure-800">
                            <i class="fa-solid fa-chart-area text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Sistem 18 Rumah Meter (DRM)</h3>
                        <p class="text-slate-400 leading-relaxed text-sm">Integrasi pembagian debit yang akurat untuk menyuplai PDAM di Pasuruan (4 titik), Sidoarjo (9 titik), Surabaya (3 titik), dan Gresik (1 titik) secara simultan.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-slate-900 p-8 rounded-2xl border border-slate-800 hover:border-sky-500/50 hover:shadow-xl transition-all duration-300">
                        <div class="w-12 h-12 bg-sky-950 text-sky-400 rounded-xl flex items-center justify-center mb-6 border border-sky-800">
                            <i class="fa-solid fa-bolt text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Manajemen Tekanan Hidrolis</h3>
                        <p class="text-slate-400 leading-relaxed text-sm">Menggunakan sistem pompa pusat Umbulan didukung stasiun booster Surabaya untuk menjamin tekanan stabil hingga ke titik akhir terjauh di Gresik.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- INTERACTIVE JALUR TRANSMISI VISUALIZER -->
        <section id="transmisi" class="py-24 bg-slate-900/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-xs font-bold text-pure-400 uppercase tracking-wider">Pantauan Sistem SCADA</span>
                    <h3 class="text-3xl sm:text-4xl font-extrabold text-white mt-2">Diagram Alir Jalur Transmisi Regional</h3>
                    <p class="text-slate-400 mt-4 text-sm">
                        Klik pada setiap wilayah di sepanjang jalur pipa transmisi Umbulan di bawah ini untuk melihat rincian kapasitas, status tekanan, jumlah rumah meter PDAM, serta peran stasiun pompa terkait.
                    </p>
                </div>

                <!-- Interactive Pipeline System Dashboard -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">

                    <!-- Left: Clickable Nodes of Transmission (7 cols on large screens) -->
                    <div class="lg:col-span-7 bg-slate-950 p-6 sm:p-8 rounded-3xl border border-slate-800/80 shadow-2xl space-y-6">
                        <div class="flex justify-between items-center border-b border-slate-800 pb-4">
                            <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider"><i class="fa-solid fa-server mr-1"></i> Jalur Utama SCADA</span>
                            <span class="flex items-center gap-1.5 text-xs text-emerald-400 font-bold"><span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span> TRANSMISI AKTIF</span>
                        </div>

                        <!-- Pipeline Flow Line representation (Interactive Buttons) -->
                        <div class="space-y-4">
                            <!-- Node 1: Umbulan (Pusat) -->
                            <button onclick="selectTransmissionNode('umbulan')" id="node-umbulan" class="w-full p-5 text-left rounded-2xl border bg-slate-900 border-water-500 text-white shadow-lg shadow-water-950/25 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-water-500 text-white flex items-center justify-center font-bold text-lg"><i class="fa-solid fa-faucet-drip"></i></div>
                                    <div>
                                        <h4 class="font-extrabold text-sm sm:text-base">Mata Air & Rumah Pompa Pusat Umbulan</h4>
                                        <p class="text-[11px] text-slate-400">Titik Awal Hulu (Pasuruan)</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 bg-water-950 text-water-400 text-xs font-bold rounded-lg group-hover:bg-water-900">Hulu / Pusat</span>
                            </button>

                            <!-- Node 2: Pasuruan -->
                            <button onclick="selectTransmissionNode('pasuruan')" id="node-pasuruan" class="w-full p-5 text-left rounded-2xl border bg-slate-900/40 border-slate-800 text-slate-400 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center font-bold text-lg">P</div>
                                    <div>
                                        <h4 class="font-extrabold text-sm sm:text-base text-slate-300">Segmen Pasuruan</h4>
                                        <p class="text-[11px] text-slate-500">Penyaluran 4 Rumah Meter PDAM</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 bg-slate-900 text-slate-500 text-xs font-bold rounded-lg">4 Titik Meter</span>
                            </button>

                            <!-- Node 3: Sidoarjo -->
                            <button onclick="selectTransmissionNode('sidoarjo')" id="node-sidoarjo" class="w-full p-5 text-left rounded-2xl border bg-slate-900/40 border-slate-800 text-slate-400 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center font-bold text-lg">S</div>
                                    <div>
                                        <h4 class="font-extrabold text-sm sm:text-base text-slate-300">Segmen Sidoarjo</h4>
                                        <p class="text-[11px] text-slate-500">Penyaluran 9 Rumah Meter PDAM</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 bg-slate-900 text-slate-500 text-xs font-bold rounded-lg">9 Titik Meter</span>
                            </button>

                            <!-- Node 4: Surabaya Booster -->
                            <button onclick="selectTransmissionNode('booster')" id="node-booster" class="w-full p-5 text-left rounded-2xl border bg-slate-900/40 border-slate-800 text-slate-400 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center font-bold text-lg"><i class="fa-solid fa-bolt"></i></div>
                                    <div>
                                        <h4 class="font-extrabold text-sm sm:text-base text-slate-300">Rumah Pompa Booster Surabaya</h4>
                                        <p class="text-[11px] text-slate-500">Stasiun Peningkat Tekanan Akhir</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 bg-slate-900 text-slate-500 text-xs font-bold rounded-lg">Booster</span>
                            </button>

                            <!-- Node 5: Surabaya DRM -->
                            <button onclick="selectTransmissionNode('surabaya')" id="node-surabaya" class="w-full p-5 text-left rounded-2xl border bg-slate-900/40 border-slate-800 text-slate-400 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center font-bold text-lg">SB</div>
                                    <div>
                                        <h4 class="font-extrabold text-sm sm:text-base text-slate-300">Segmen Surabaya</h4>
                                        <p class="text-[11px] text-slate-500">Penyaluran 3 Rumah Meter PDAM</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 bg-slate-900 text-slate-500 text-xs font-bold rounded-lg">3 Titik Meter</span>
                            </button>

                            <!-- Node 6: Gresik -->
                            <button onclick="selectTransmissionNode('gresik')" id="node-gresik" class="w-full p-5 text-left rounded-2xl border bg-slate-900/40 border-slate-800 text-slate-400 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center font-bold text-lg">G</div>
                                    <div>
                                        <h4 class="font-extrabold text-sm sm:text-base text-slate-300">Segmen Gresik</h4>
                                        <p class="text-[11px] text-slate-500">Penyaluran 1 Rumah Meter (Titik Ujung)</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 bg-slate-900 text-slate-500 text-xs font-bold rounded-lg">1 Titik Meter</span>
                            </button>
                        </div>
                    </div>

                    <!-- Right: Dynamic Details Panel (5 cols on large screens) -->
                    <div class="lg:col-span-5 bg-slate-900 p-8 rounded-3xl border border-slate-800 shadow-2xl relative min-h-[460px] flex flex-col justify-between">
                        <!-- Huge decorative background indicator -->
                        <div class="absolute right-4 top-4 text-slate-800 text-8xl font-black -z-0 select-none opacity-20" id="panel-bg-label">HULU</div>

                        <div class="space-y-6 relative z-10">
                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-water-950 border border-water-800 text-water-400 rounded-full text-[10px] font-bold uppercase tracking-wider" id="panel-tag">
                                STASIUN UTAMA (Pusat)
                            </div>
                            <h3 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight" id="panel-title">Mata Air & Rumah Pompa Umbulan</h3>
                            <p class="text-slate-400 leading-relaxed text-sm sm:text-base" id="panel-description">
                                Berlokasi di Kabupaten Pasuruan, stasiun pompa utama ini mengekstraksi air bersih dari mata air alami Umbulan yang legendaris karena kemurniannya. Air dialirkan langsung tanpa filter kimia tambahan, menggunakan gaya gravitasi bumi dibantu pompa pendorong hulu berkapasitas total hingga 4.000 liter/detik untuk menempuh perjalanan hulu-ke-hilir sepanjang jalur pipa.
                            </p>
                        </div>

                        <!-- Technical Spec Card inside Panel -->
                        <div class="grid grid-cols-2 gap-4 mt-8 pt-6 border-t border-slate-850 relative z-10">
                            <div>
                                <span class="block text-[10px] text-slate-500 font-bold uppercase tracking-wider">Tekanan Jalur</span>
                                <span class="font-extrabold text-white text-base sm:text-lg" id="panel-pressure">12.5 Bar</span>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-500 font-bold uppercase tracking-wider">Kapasitas Aliran</span>
                                <span class="font-extrabold text-pure-400 text-base sm:text-lg" id="panel-flow">4.000 L/detik</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- DETIL DISTRIBUSI 18 RUMAH METER -->
        <section id="distribusi" class="py-24 bg-slate-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-base font-semibold text-pure-400 uppercase tracking-widest">Alokasi Distribusi</h2>
                    <p class="text-3xl sm:text-4xl font-extrabold text-white mt-2 tracking-tight">Pembagian Aliran di 18 Titik PDAM</p>
                    <p class="text-lg text-slate-400 mt-4">Pipa transmisi utama membagi aliran air bersih secara berkesinambungan melalui DRM (*District Metered Area*) di masing-masing wilayah kabupaten/kota.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Pasuruan Card -->
                    <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <span class="px-2.5 py-1 bg-emerald-950/80 border border-emerald-900 text-emerald-400 text-[10px] font-extrabold rounded-lg uppercase tracking-wider">Wilayah 1</span>
                                <span class="text-3xl font-black text-slate-800">01</span>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Pasuruan</h3>
                            <p class="text-slate-400 text-xs leading-relaxed">Berada paling dekat dengan hulu mata air Umbulan. Menerima aliran dengan tekanan alami yang tinggi.</p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-850 flex justify-between items-center text-xs">
                            <span class="text-slate-500">Rumah Meter PDAM:</span>
                            <span class="font-extrabold text-water-400 text-sm">4 Titik DRM</span>
                        </div>
                    </div>

                    <!-- Sidoarjo Card -->
                    <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <span class="px-2.5 py-1 bg-emerald-950/80 border border-emerald-900 text-emerald-400 text-[10px] font-extrabold rounded-lg uppercase tracking-wider">Wilayah 2</span>
                                <span class="text-3xl font-black text-slate-800">02</span>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Sidoarjo</h3>
                            <p class="text-slate-400 text-xs leading-relaxed">Penerima alokasi terbesar dengan jaringan pipa distribusi sekunder yang mencakup wilayah padat industri.</p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-850 flex justify-between items-center text-xs">
                            <span class="text-slate-500">Rumah Meter PDAM:</span>
                            <span class="font-extrabold text-water-400 text-sm">9 Titik DRM</span>
                        </div>
                    </div>

                    <!-- Surabaya Card -->
                    <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <span class="px-2.5 py-1 bg-emerald-950/80 border border-emerald-900 text-emerald-400 text-[10px] font-extrabold rounded-lg uppercase tracking-wider">Wilayah 3</span>
                                <span class="text-3xl font-black text-slate-800">03</span>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Surabaya</h3>
                            <p class="text-slate-400 text-xs leading-relaxed">Menerima suplai sebelum batas kota, didukung stasiun pompa booster penyeimbang di wilayah Surabaya Selatan.</p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-850 flex justify-between items-center text-xs">
                            <span class="text-slate-500">Rumah Meter PDAM:</span>
                            <span class="font-extrabold text-water-400 text-sm">3 Titik DRM</span>
                        </div>
                    </div>

                    <!-- Gresik Card -->
                    <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <span class="px-2.5 py-1 bg-amber-950/80 border border-amber-900 text-amber-400 text-[10px] font-extrabold rounded-lg uppercase tracking-wider">Wilayah 4</span>
                                <span class="text-3xl font-black text-slate-800">04</span>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Gresik</h3>
                            <p class="text-slate-400 text-xs leading-relaxed">Titik ujung transmisi. Membutuhkan optimalisasi tekanan maksimal dari pompa booster di Surabaya.</p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-850 flex justify-between items-center text-xs">
                            <span class="text-slate-500">Rumah Meter PDAM:</span>
                            <span class="font-extrabold text-water-400 text-sm">1 Titik DRM</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CORE INFRASTRUCTURE PORTFOLIO -->
        <section id="infrastruktur" class="py-24 bg-slate-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-base font-semibold text-pure-400 uppercase tracking-widest">Infrastruktur & Aset</h2>
                    <p class="text-3xl sm:text-4xl font-extrabold text-white mt-2 tracking-tight">Aset Utama Transmisi Air Bersih</p>
                    <p class="text-lg text-slate-400 mt-4">PT META ADHYA TIRTA UMBULAN   merancang dan mengoperasikan aset transmisi modern dengan standar ketahanan tinggi.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Project 1 -->
                    <div class="bg-slate-950 rounded-2xl border border-slate-800 overflow-hidden flex flex-col justify-between group hover:border-water-500/50 transition-all duration-300">
                        <div class="p-8 space-y-4">
                            <span class="inline-block px-3 py-1 bg-water-950/80 border border-water-800 text-water-400 rounded-full text-[10px] font-bold uppercase tracking-wider">Aset Transmisi</span>
                            <h3 class="text-xl font-bold text-white tracking-tight group-hover:text-water-400 transition-colors">Pipa Transmisi Steel DN1200</h3>
                            <p class="text-slate-400 text-sm leading-relaxed">
                                Pemasangan pipa baja karbon dengan lapisan anti-korosi tingkat tinggi sepanjang puluhan kilometer melintasi jalur bypass kabupaten untuk menyalurkan aliran bertekanan tinggi.
                            </p>
                        </div>
                        <div class="p-6 bg-slate-900/60 border-t border-slate-850 grid grid-cols-2 gap-4 text-xs">
                            <div>
                                <span class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">Diameter Pipa</span>
                                <span class="font-extrabold text-white text-sm">1.200 milimeter</span>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">Tekanan Maks</span>
                                <span class="font-extrabold text-white text-sm">20 Bar</span>
                            </div>
                        </div>
                    </div>

                    <!-- Project 2 -->
                    <div class="bg-slate-950 rounded-2xl border border-slate-800 overflow-hidden flex flex-col justify-between group hover:border-pure-500/50 transition-all duration-300">
                        <div class="p-8 space-y-4">
                            <span class="inline-block px-3 py-1 bg-pure-950/80 border border-pure-800 text-pure-400 rounded-full text-[10px] font-bold uppercase tracking-wider">Stasiun Hulu</span>
                            <h3 class="text-xl font-bold text-white tracking-tight group-hover:text-pure-400 transition-colors">Rumah Pompa Pusat Umbulan</h3>
                            <p class="text-slate-400 text-sm leading-relaxed">
                                Dilengkapi dengan jajaran pompa sentrifugal split-case besar bersertifikasi efisiensi tinggi, sistem kontrol inverter kecepatan, serta gardu induk listrik mandiri.
                            </p>
                        </div>
                        <div class="p-6 bg-slate-900/60 border-t border-slate-850 grid grid-cols-2 gap-4 text-xs">
                            <div>
                                <span class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">Kapasitas Pompa</span>
                                <span class="font-extrabold text-white text-sm">4.000 Liters/sec</span>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">Sistem Listrik</span>
                                <span class="font-extrabold text-white text-sm">Gardu Ganda 20 kV</span>
                            </div>
                        </div>
                    </div>

                    <!-- Project 3 -->
                    <div class="bg-slate-950 rounded-2xl border border-slate-800 overflow-hidden flex flex-col justify-between group hover:border-sky-500/50 transition-all duration-300">
                        <div class="p-8 space-y-4">
                            <span class="inline-block px-3 py-1 bg-sky-950/80 border border-sky-800 text-sky-400 rounded-full text-[10px] font-bold uppercase tracking-wider">Stasiun Pendorong</span>
                            <h3 class="text-xl font-bold text-white tracking-tight group-hover:text-sky-400 transition-colors">Stasiun Booster Surabaya</h3>
                            <p class="text-slate-400 text-sm leading-relaxed">
                                Terletak di pinggiran Surabaya sebelum memasuki wilayah Gresik, berfungsi mengembalikan momentum tekanan hidrolik air yang mengalami degradasi gesekan sepanjang pipa.
                            </p>
                        </div>
                        <div class="p-6 bg-slate-900/60 border-t border-slate-850 grid grid-cols-2 gap-4 text-xs">
                            <div>
                                <span class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">Tekanan Booster</span>
                                <span class="font-extrabold text-white text-sm">Hingga 8 Bar</span>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-500 uppercase font-bold tracking-wider">Lokasi Stasiun</span>
                                <span class="font-extrabold text-white text-sm">Batas Surabaya - Gresik</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- B2B PDAM PORTAL FORM -->
        {{-- <section id="kolaborasi" class="py-24 bg-slate-950 border-t border-slate-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">

                    <!-- Left Info -->
                    <div class="lg:col-span-5 space-y-6">
                        <span class="text-xs font-bold text-water-400 uppercase tracking-wider">Portal Koordinasi PDAM</span>
                        <h3 class="text-3xl font-extrabold text-white tracking-tight">Koordinasi Alokasi Distribusi</h3>
                        <p class="text-slate-400 leading-relaxed">
                            Formulir koordinasi teknis khusus untuk operator PDAM wilayah Pasuruan, Sidoarjo, Surabaya, dan Gresik. Gunakan portal ini untuk melaporkan pemeliharaan rumah meter, permintaan perubahan kuota debit, atau pelaporan tekanan aliran.
                        </p>

                        <!-- Contact Points -->
                        <div class="space-y-4 pt-4">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-slate-900 rounded-xl shadow-sm flex items-center justify-center text-water-400 border border-slate-800 flex-shrink-0">
                                    <i class="fa-solid fa-headset"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-white text-sm">Meja Bantuan SCADA 24/7</h4>
                                    <p class="text-xs text-slate-400 mt-0.5">Hotline Khusus Operator PDAM: (031) 555-8899 / Radio Trunking Ch 12B</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-slate-900 rounded-xl shadow-sm flex items-center justify-center text-water-400 border border-slate-800 flex-shrink-0">
                                    <i class="fa-solid fa-network-wired"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-white text-sm">Integrasi API Telemetri</h4>
                                    <p class="text-xs text-slate-400 mt-0.5">telemetri.META ADHYA TIRTA UMBULANumbulan.co.id</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right B2B Form -->
                    <div class="lg:col-span-7 bg-slate-900 p-8 sm:p-10 rounded-3xl border border-slate-800/85 shadow-xl shadow-slate-950">
                        <form id="contact-form" onsubmit="handleFormSubmit(event)" class="space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Institusi PDAM</label>
                                    <select id="form-pdam" required class="w-full p-3.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-300 focus:outline-none focus:ring-2 focus:ring-water-500 focus:border-transparent transition-all">
                                        <option value="pdam-pasuruan">PDAM Kabupaten/Kota Pasuruan</option>
                                        <option value="pdam-sidoarjo">PDAM Delta Tirta Sidoarjo</option>
                                        <option value="pdam-surabaya">PDAM Surya Sembada Surabaya</option>
                                        <option value="pdam-gresik">PDAM Giri Tirta Gresik</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Nama Operator / Pengaju</label>
                                    <input type="text" required placeholder="Contoh: Ir. Eko Prasetyo" class="w-full p-3.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-water-500 focus:border-transparent transition-all">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Nomor Telepon Dinas (WhatsApp)</label>
                                    <input type="tel" required placeholder="Contoh: 08123456xxx" class="w-full p-3.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-water-500 focus:border-transparent transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Jenis Laporan / Kebutuhan</label>
                                    <select required class="w-full p-3.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-300 focus:outline-none focus:ring-2 focus:ring-water-500 focus:border-transparent transition-all">
                                        <option value="kuota-debit">Permintaan Penyesuaian Debit Aliran</option>
                                        <option value="maintenance-meter">Jadwal Pemeliharaan Rumah Meter</option>
                                        <option value="tekanan-drop">Laporan Fluktuasi Tekanan (Pressure Drop)</option>
                                        <option value="koordinasi-rutin">Rapat Koordinasi Rutin & Kehumasan</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Detail Titik Rumah Meter (DRM) & Deskripsi Masalah</label>
                                <textarea rows="4" required placeholder="Tuliskan nama titik DRM spesifik (contoh: DRM Karangpilang, DRM Kebomas, dll) beserta rincian informasi teknis..." class="w-full p-3.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-water-500 focus:border-transparent transition-all"></textarea>
                            </div>

                            <button type="submit" class="w-full py-4 bg-gradient-to-r from-water-600 to-pure-600 text-white font-bold rounded-2xl hover:shadow-xl hover:shadow-water-600/30 transition-all">
                                Kirim Laporan ke Pusat SCADA Umbulan
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </section> --}}

    </main>

    <!-- CUSTOM NOTIFICATION MODAL -->
    <div id="notif-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm transition-opacity">
        <div class="bg-slate-900 rounded-3xl p-8 max-w-sm w-full border border-slate-800 shadow-2xl text-center space-y-4">
            <div class="w-16 h-16 bg-pure-950 text-pure-400 rounded-full flex items-center justify-center text-3xl mx-auto shadow-md border border-pure-800/50">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3 class="text-xl font-bold text-white">Laporan Terkirim</h3>
            <p id="notif-message" class="text-sm text-slate-400 leading-relaxed">
                Formulir koordinasi teknis Anda telah masuk ke Sistem SCADA Pusat PT META ADHYA TIRTA UMBULAN  . Operator piket kami akan segera memverifikasi data dan menghubungi Anda kembali.
            </p>
            <button onclick="closeNotif()" class="w-full py-3 bg-gradient-to-r from-water-600 to-pure-600 text-white font-bold rounded-xl hover:shadow-lg transition-all">
                Tutup Jendela
            </button>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="bg-slate-950 text-slate-400 border-t border-slate-900 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8 pb-12 border-b border-slate-900">
            <!-- Brand Column -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 shrink-0 bg-gradient-to-tr from-water-500 to-pure-500 rounded-full flex items-center justify-center text-white">
                        <img src="{{ asset('images/iconfav.png') }}" alt="Logo" class="w-full h-full object-cover rounded-full">
                    </div>
                    <div>
                        <span class="text-lg font-bold text-white tracking-tight">META ADHYA TIRTA UMBULAN</span>
                        {{-- <span class="block text-[8px] text-slate-500 font-semibold uppercase tracking-wider">Umbulan Transmission</span> --}}
                    </div>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Menjaga kontinuitas transmisi air bersih regional hulu ke hilir untuk mengamankan kebutuhan pasokan air minum masyarakat perkotaan.
                </p>
            </div>

            <!-- Navigation Links -->
            <div>
                <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Navigasi Jalur</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="#tentang" class="hover:text-white transition-colors">Tentang Sistem</a></li>
                    <li><a href="#transmisi" class="hover:text-white transition-colors">Pantau Jalur SCADA</a></li>
                    <li><a href="#distribusi" class="hover:text-white transition-colors">Alokasi Wilayah</a></li>
                    <li><a href="#infrastruktur" class="hover:text-white transition-colors">Detail Konstruksi Pipa</a></li>
                </ul>
            </div>

            <!-- Operations / Working Hours -->
            <div>
                <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Pusat SCADA</h4>
                <p class="text-xs text-slate-500 leading-relaxed font-medium">
                    <strong>Pusat Kontrol Umbulan</strong><br>
                    Kawasan Mata Air Umbulan, Winongan,<br>
                    Pasuruan, Jawa Timur<br><br>
                    <strong>Status Sistem:</strong><br>
                    <span class="text-pure-400 font-semibold"><i class="fa-solid fa-signal mr-1"></i> Telemetri Terhubung 24/7</span>
                </p>
            </div>
        </div>

        <!-- Copyright -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-600">
            <p>&copy; <?=date('Y')?> PT META ADHYA TIRTA UMBULAN  . Seluruh Hak Cipta Dilindungi Undang-Undang.</p>
            <p>Sistem Integrasi Transmisi Umbulan Regional Jawa Timur.</p>
        </div>
    </footer>

    <!-- INTERACTIVE JAVASCRIPT LOGIC -->
    <script>
        // 1. Mobile Menu Toggle
        const menuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Close menu on link click
        const mobileLinks = mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
            });
        });

        // 2. Interactive Transmission Nodes Data Map
        const TRANSMISSION_NODES = {
            umbulan: {
                tag: "STASIUN UTAMA (Pusat)",
                bgLabel: "HULU",
                title: "Mata Air & Rumah Pompa Umbulan",
                description: "Berlokasi di Kabupaten Pasuruan, stasiun pompa utama ini mengekstraksi air bersih langsung dari mata air alami Umbulan yang legendaris karena kemurniannya. Air dialirkan tanpa filter kimia tambahan, memanfaatkan gaya gravitasi bumi dibantu pompa pendorong hulu berkapasitas total hingga 4.000 liter/detik untuk meluncurkan air menyusuri pipa transmisi.",
                pressure: "12.5 Bar",
                flow: "4.000 L/detik"
            },
            pasuruan: {
                tag: "SEGMEN WILAYAH I",
                bgLabel: "PASURUAN",
                title: "Sistem Distribusi Pasuruan",
                description: "Menyuplai air bersih untuk wilayah Kabupaten dan Kota Pasuruan melalui 4 titik Rumah Meter (DRM) PDAM setempat. Karena posisinya yang paling dekat dengan stasiun pompa pusat, wilayah ini mendapatkan pasokan dengan tekanan stabil yang optimal tanpa membutuhkan pompa tambahan.",
                pressure: "10.2 Bar",
                flow: "350 L/detik"
            },
            sidoarjo: {
                tag: "SEGMEN WILAYAH II",
                bgLabel: "SIDOARJO",
                title: "Sistem Distribusi Sidoarjo",
                description: "Merupakan wilayah dengan alokasi penyerapan terbesar di sepanjang jalur pipa transmisi Umbulan. Menyuplai 9 titik Rumah Meter (DRM) PDAM Delta Tirta Sidoarjo untuk mencakup kawasan perumahan padat penduduk serta pusat industri manufaktur utama.",
                pressure: "7.8 Bar",
                flow: "1.350 L/detik"
            },
            booster: {
                tag: "STASIUN BOOSTING (Antara)",
                bgLabel: "BOOSTER",
                title: "Rumah Pompa Booster Surabaya",
                description: "Terletak strategis di perbatasan Surabaya Selatan sebelum jalur pipa mengarah ke utara menuju Gresik. Stasiun ini mutlak diperlukan untuk memulihkan kehilangan tekanan (*pressure drop*) akibat gesekan pipa sepanjang puluhan kilometer dari hulu, guna memastikan air memiliki daya dorong yang kuat untuk mencapai destinasi akhir.",
                pressure: "9.5 Bar (Boosted)",
                flow: "1.200 L/detik"
            },
            surabaya: {
                tag: "SEGMEN WILAYAH III",
                bgLabel: "SURABAYA",
                title: "Sistem Distribusi Surabaya",
                description: "Menyalurkan air bersih ke wilayah administrasi Kota Surabaya melalui 3 titik Rumah Meter (DRM) strategis PDAM Surya Sembada. Suplai diatur secara presisi untuk menyeimbangkan kebutuhan domestik perkotaan besar.",
                pressure: "6.5 Bar",
                flow: "1.000 L/detik"
            },
            gresik: {
                tag: "SEGMEN WILAYAH IV (Ujung)",
                bgLabel: "GRESIK",
                title: "Sistem Distribusi Gresik",
                description: "Merupakan titik akhir dari pipa transmisi utama Umbulan sepanjang ~93.2 km. Menyuplai 1 titik Rumah Meter (DRM) utama PDAM Giri Tirta Gresik. Karena posisinya berada di ujung jaringan transmisi (*tail-end*), pemantauan tekanan dilakukan secara konstan dari stasiun booster Surabaya.",
                pressure: "4.5 Bar",
                flow: "300 L/detik"
            }
        };

        function selectTransmissionNode(nodeKey) {
            const keys = ['umbulan', 'pasuruan', 'sidoarjo', 'booster', 'surabaya', 'gresik'];

            // Loop buttons styling
            keys.forEach(key => {
                const btn = document.getElementById(`node-${key}`);
                const iconBox = btn.querySelector('div > div');
                const titleText = btn.querySelector('div h4');
                const badge = btn.querySelector('span');

                if (key === nodeKey) {
                    btn.className = "w-full p-5 text-left rounded-2xl border bg-slate-900 border-water-500 text-white shadow-lg shadow-water-950/25 transition-all flex items-center justify-between group";
                    iconBox.className = "w-10 h-10 rounded-xl bg-water-500 text-white flex items-center justify-center font-bold text-lg";
                    titleText.className = "font-extrabold text-sm sm:text-base text-white";
                    badge.className = "px-2.5 py-1 bg-water-950 text-water-400 text-xs font-bold rounded-lg";
                } else {
                    btn.className = "w-full p-5 text-left rounded-2xl border bg-slate-900/40 border-slate-800 text-slate-400 transition-all flex items-center justify-between group";
                    iconBox.className = "w-10 h-10 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center font-bold text-lg";
                    titleText.className = "font-extrabold text-sm sm:text-base text-slate-300";
                    badge.className = "px-2.5 py-1 bg-slate-900 text-slate-500 text-xs font-bold rounded-lg";
                }
            });

            // Update content display on the detail side
            const nodeData = TRANSMISSION_NODES[nodeKey];
            document.getElementById('panel-bg-label').innerText = nodeData.bgLabel;
            document.getElementById('panel-tag').innerText = nodeData.tag;
            document.getElementById('panel-title').innerText = nodeData.title;
            document.getElementById('panel-description').innerText = nodeData.description;
            document.getElementById('panel-pressure').innerText = nodeData.pressure;
            document.getElementById('panel-flow').innerText = nodeData.flow;
        }

        // 3. Form Submission handler
        function handleFormSubmit(event) {
            event.preventDefault();

            const modal = document.getElementById('notif-modal');
            const form = document.getElementById('contact-form');
            const selectPdam = document.getElementById('form-pdam');
            const selectedText = selectPdam.options[selectPdam.selectedIndex].text;

            document.getElementById('notif-message').innerText = `Laporan koordinasi untuk ${selectedText} telah dikirimkan ke Pusat Kontrol SCADA Umbulan. Operator piket kami akan segera memproses informasi ini dalam waktu maksimal 15 menit melalui saluran darurat radio atau telepon dinas.`;

            // Show custom notification
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Reset fields
            form.reset();
        }

        function closeNotif() {
            const modal = document.getElementById('notif-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
    <!-- PWA Script Registrations & Tools -->
    @laravelPwa
    @pwaUpdateNotifier
    @pwaInstallButton
</body>
</html>
