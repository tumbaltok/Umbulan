@extends('layouts.app')
@section('title', 'Koneksi WhatsApp Gateway')

@section('content')
<div class="space-y-6">

    {{-- HEADER HALAMAN & BREADCRUMB --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-xs">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/20 shrink-0">                <i class="fa-brands fa-whatsapp"></i>
            </div>
            <div>
                <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-400 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 dark:hover:text-slate-200 transition-colors">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-600 dark:text-slate-300">Administrator</span>
                    <span>/</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">WhatsApp Gateway</span>
                </nav>
                <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2.5">
                    <span>Self-Hosted WhatsApp Gateway</span>
                </h1>
            </div>
        </div>

        <div class="flex items-center space-x-2.5 self-end sm:self-center">
            <button onclick="refreshStatusManual()" id="refreshBtn" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition-all flex items-center space-x-2 cursor-pointer active:scale-95">
                <i class="fa-solid fa-rotate text-xs" id="refreshIcon"></i>
                <span>Segarkan Status</span>
            </button>
        </div>
    </div>

    {{-- ALERT PEMBERITAHUAN LIVE --}}
    <div id="liveAlert" class="hidden p-4 rounded-2xl border text-sm font-semibold transition-all"></div>

    {{-- GRID UTAMA: STATUS & PAIRING QR --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- KOLOM KIRI: STATUS & SCAN QR (7 COLS) --}}
        <div class="lg:col-span-7 space-y-6">

            {{-- KARTU STATUS KONEKSI --}}
            <div class="p-6 bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-xs space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700/70 text-slate-600 dark:text-slate-300 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-server"></i>
                        </div>
                        <h2 class="font-bold text-slate-800 dark:text-slate-100 text-base">Status Koneksi Gateway</h2>
                    </div>

                    {{-- LIVE STATUS BADGE --}}
                    <div id="statusBadgeContainer">
                        @if(($statusData['status'] ?? '') === 'connected')
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                TERHUBUNG
                            </span>
                        @elseif(!empty($statusData['qr']) || ($statusData['status'] ?? '') === 'connecting')
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                <span class="h-2 w-2 rounded-full bg-amber-500 animate-ping"></span>
                                MENGHUBUNGKAN (SIAP SCAN)
                            </span>
                        @elseif(!($statusData['online'] ?? false))
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-extrabold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                SERVICE OFFLINE
                            </span>
                        @else
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-extrabold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                TERPUTUS
                            </span>
                        @endif
                    </div>
                </div>

                {{-- RINCIAN KONEKSI --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-800 space-y-1">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Nomor WhatsApp Terhubung</span>
                        <p id="connectedPhoneDisplay" class="font-extrabold text-sm sm:text-base text-slate-800 dark:text-slate-100 font-mono flex items-center gap-2">
                            @if(!empty($statusData['phone']))
                                <i class="fa-solid fa-phone text-emerald-500"></i>
                                <span>+{{ $statusData['phone'] }}</span>
                            @else
                                <span class="text-slate-400 font-normal italic">Belum terhubung</span>
                            @endif
                        </p>
                    </div>

                    <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-800 space-y-1">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Microservice Engine (Port 3001)</span>
                        <p id="microserviceStatusDisplay" class="font-bold text-sm sm:text-base font-mono flex items-center gap-2">
                            @if($statusData['online'] ?? false)
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold">Online (Aktif)</span>
                            @else
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                <span class="text-rose-600 dark:text-rose-400 font-bold">Offline (Mati)</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- AKSI PUTUS KONEKSI --}}
                <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Sesi multi-file disimpan aman di folder internal <code>auth_session/</code>.
                    </p>
                    <button onclick="confirmDisconnect()" id="disconnectBtn" class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/30 dark:hover:bg-rose-900/50 dark:text-rose-400 rounded-xl text-xs font-bold transition-all border border-rose-200 dark:border-rose-800/80 flex items-center justify-center space-x-2 cursor-pointer active:scale-95 whitespace-nowrap">
                        <i class="fa-solid fa-power-off"></i>
                        <span>Putuskan Sesi / Reset QR</span>
                    </button>
                </div>
            </div>

            {{-- PANEL SCAN QR CODE --}}
            <div id="qrSection" class="p-6 bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-xs space-y-5 transition-all">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-800 dark:text-slate-100 text-base">Pindai Kode QR</h2>
                            <p class="text-xs text-slate-400">Tautkan perangkat WhatsApp resmi perusahaan</p>
                        </div>
                    </div>

                    <div id="qrLiveIndicator" class="{{ ($statusData['status'] ?? '') === 'connected' ? 'hidden' : 'flex' }} items-center gap-1.5 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>Live Sync</span>
                    </div>
                </div>

                {{-- AREA TAMPILAN QR ATAU KONEKSI SUKSES --}}
                <div class="flex flex-col items-center justify-center py-6 px-4">
                    {{-- WIDGET QR CODE (SSR READY) --}}
                    @php
                        $isAlreadyConnected = ($statusData['status'] ?? '') === 'connected';
                        $initialQr = $statusData['qr'] ?? null;
                    @endphp
                    <div id="qrBox" class="{{ $isAlreadyConnected ? 'hidden' : '' }} relative p-4 bg-white rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-700 shadow-md flex items-center justify-center min-w-[260px] min-h-[260px]">
                        <img id="qrImage" src="{{ $initialQr ?? '' }}" alt="WhatsApp QR Code" class="{{ !empty($initialQr) ? '' : 'hidden' }} w-60 h-60 rounded-2xl object-contain transition-all duration-300">
                        <div id="qrPlaceholder" class="{{ !empty($initialQr) ? 'hidden' : 'flex' }} flex-col items-center justify-center space-y-3 text-slate-400 p-8 text-center">
                            <div class="w-10 h-10 border-3 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                            <span id="qrPlaceholderText" class="text-xs font-semibold">
                                @if(!($statusData['online'] ?? false))
                                    Microservice WhatsApp Gateway (Port 3001) belum aktif
                                @else
                                    Menyiapkan QR Code dari Gateway...
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- PESAN SUKSES SAAT TERHUBUNG --}}
                    <div id="connectedNotice" class="{{ $isAlreadyConnected ? '' : 'hidden' }} text-center py-6 space-y-3">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl mx-auto shadow-inner">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <h4 class="font-extrabold text-base text-slate-800 dark:text-slate-100">WhatsApp Gateway Telah Terhubung</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                            Sistem telah siap digunakan untuk memproses pengiriman OTP nomor telepon dan notifikasi persetujuan Cuti, CAR, & MPR.
                        </p>
                    </div>

                    {{-- PETUNJUK PAIRING --}}
                    <div id="pairingGuide" class="{{ $isAlreadyConnected ? 'hidden' : '' }} mt-6 w-full max-w-md bg-slate-50 dark:bg-slate-900/40 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 space-y-2 text-xs text-slate-600 dark:text-slate-400">
                        <p class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-sky-500"></i> Cara Menautkan Perangkat:
                        </p>
                        <ol class="list-decimal list-inside space-y-1 pl-1 leading-relaxed">
                            <li>Buka aplikasi <strong>WhatsApp</strong> di ponsel resmi pengirim.</li>
                            <li>Ketuk menu <strong>Tiga Titik</strong> (Android) atau <strong>Pengaturan</strong> (iPhone).</li>
                            <li>Pilih <strong>Perangkat Tertaut</strong>, lalu ketuk <strong>Tautkan Perangkat</strong>.</li>
                            <li>Arahkan kamera ponsel Anda ke kotak kode QR di atas.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: FORM UJI KIRIM PESAN & INFORMASI (5 COLS) --}}
        <div class="lg:col-span-5 space-y-6">

            {{-- FORM UJI COBA PENGIRIMAN PESAN --}}
            <div class="p-6 bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-xs space-y-5">
                <div class="flex items-center space-x-3 border-b border-slate-100 dark:border-slate-700 pb-4">
                    <div class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-800 dark:text-slate-100 text-base">Uji Kirim Pesan</h2>
                        <p class="text-xs text-slate-400">Validasi fungsionalitas pengiriman socket Baileys</p>
                    </div>
                </div>

                <form id="testMessageForm" onsubmit="handleSendTest(event)" class="space-y-4">
                    <div>
                        <label for="testPhone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Nomor WhatsApp Tujuan
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm font-semibold">
                                +62
                            </div>
                            <input type="text" id="testPhone" name="phone_number" required placeholder="81234567890" class="w-full pl-12 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:border-emerald-500 transition-all font-mono">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Dapat ditulis langsung tanpa angka 0 di depan (contoh: 812xxxx).</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="testMessage" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                Isi Pesan Uji Coba
                            </label>
                            <button type="button" onclick="fillTestPreset()" class="text-[11px] font-bold text-sky-600 dark:text-sky-400 hover:underline">
                                Pasang Template
                            </button>
                        </div>
                        <textarea id="testMessage" name="message" rows="4" required placeholder="Tuliskan pesan uji coba di sini..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:border-emerald-500 transition-all"></textarea>
                    </div>

                    <button type="submit" id="submitTestBtn" class="w-full py-3 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-emerald-600/20 flex items-center justify-center space-x-2 cursor-pointer active:scale-98">
                        <i class="fa-solid fa-paper-plane text-xs" id="submitTestIcon"></i>
                        <span id="submitTestText">Kirim Pesan Uji Coba</span>
                    </button>
                </form>
            </div>

            {{-- KARTU TEKNOLOGI & ARSITEKTUR GATEWAY --}}
            <div class="p-6 bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-xs space-y-4">
                <div class="flex items-center space-x-3 border-b border-slate-100 dark:border-slate-700 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-violet-50 dark:bg-violet-950/40 text-violet-600 dark:text-violet-400 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-microchip"></i>
                    </div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Arsitektur & Spesifikasi Gateway</h3>
                </div>

                <div class="space-y-2.5 text-xs text-slate-600 dark:text-slate-400">
                    <div class="flex items-center justify-between py-1 border-b border-slate-50 dark:border-slate-800">
                        <span class="text-slate-400">Engine Core:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200">@whiskeysockets/baileys (Node.js)</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50 dark:border-slate-800">
                        <span class="text-slate-400">Protokol:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200">Multi-Device WebSocket (MD)</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50 dark:border-slate-800">
                        <span class="text-slate-400">Sesi Penyimpanan:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200">MultiFileAuthState (Internal)</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50 dark:border-slate-800">
                        <span class="text-slate-400">Work Hours Guard:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Aktif (Normal & Roster Shift)</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-400">Anti-Spam Throttling:</span>
                        <span class="font-bold text-sky-600 dark:text-sky-400">Cooldown 2 Jam (last_notified_at)</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- JAVASCRIPT LOGIC REALTIME STATUS & QR POLLING --}}
<script>
    let isConnected = {{ ($statusData['status'] ?? '') === 'connected' ? 'true' : 'false' }};
    let pollingInterval = null;

    function showAlert(message, type = 'success') {
        const alertEl = document.getElementById('liveAlert');
        if (!alertEl) return;

        alertEl.className = `p-4 rounded-2xl border text-sm font-semibold transition-all ${
            type === 'success' 
                ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-200' 
                : 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-200'
        }`;
        alertEl.innerHTML = `<div class="flex items-center gap-2"><i class="fa-solid ${type === 'success' ? 'fa-circle-check text-emerald-500' : 'fa-circle-exclamation text-rose-500'}"></i><span>${message}</span></div>`;
        alertEl.classList.remove('hidden');

        setTimeout(() => {
            alertEl.classList.add('hidden');
        }, 6000);
    }

    function updateUiState(status, phone, qrUrl, isOnline = true) {
        const badgeContainer = document.getElementById('statusBadgeContainer');
        const phoneDisplay = document.getElementById('connectedPhoneDisplay');
        const microserviceDisplay = document.getElementById('microserviceStatusDisplay');
        const qrBox = document.getElementById('qrBox');
        const qrImage = document.getElementById('qrImage');
        const qrPlaceholder = document.getElementById('qrPlaceholder');
        const qrPlaceholderText = document.getElementById('qrPlaceholderText');
        const connectedNotice = document.getElementById('connectedNotice');
        const pairingGuide = document.getElementById('pairingGuide');
        const qrLiveIndicator = document.getElementById('qrLiveIndicator');

        // Update indikator engine microservice
        if (microserviceDisplay) {
            microserviceDisplay.innerHTML = isOnline
                ? `<span class="h-2 w-2 rounded-full bg-emerald-500"></span><span class="text-emerald-600 dark:text-emerald-400 font-bold">Online (Aktif)</span>`
                : `<span class="h-2 w-2 rounded-full bg-rose-500"></span><span class="text-rose-600 dark:text-rose-400 font-bold">Offline (Mati)</span>`;
        }

        if (status === 'connected') {
            isConnected = true;
            badgeContainer.innerHTML = `
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    TERHUBUNG
                </span>
            `;
            phoneDisplay.innerHTML = phone 
                ? `<i class="fa-solid fa-phone text-emerald-500"></i><span>+${phone}</span>` 
                : `<span class="text-emerald-600 font-bold">Terhubung</span>`;

            qrBox.classList.add('hidden');
            pairingGuide.classList.add('hidden');
            connectedNotice.classList.remove('hidden');
            qrLiveIndicator.classList.add('hidden');
        } else if (status === 'connecting' || qrUrl) {
            isConnected = false;
            badgeContainer.innerHTML = `
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                    <span class="h-2 w-2 rounded-full bg-amber-500 animate-ping"></span>
                    MENGHUBUNGKAN (SIAP SCAN)
                </span>
            `;
            phoneDisplay.innerHTML = `<span class="text-slate-400 font-normal italic">Menyiapkan koneksi...</span>`;

            qrBox.classList.remove('hidden');
            pairingGuide.classList.remove('hidden');
            connectedNotice.classList.add('hidden');
            qrLiveIndicator.classList.remove('hidden');

            if (qrUrl) {
                qrImage.src = qrUrl;
                qrImage.classList.remove('hidden');
                qrPlaceholder.classList.add('hidden');
            } else {
                qrImage.classList.add('hidden');
                qrPlaceholder.classList.remove('hidden');
                if (qrPlaceholderText) qrPlaceholderText.innerText = 'Menyiapkan QR Code dari Gateway...';
            }
        } else {
            isConnected = false;
            if (!isOnline) {
                badgeContainer.innerHTML = `
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-extrabold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                        SERVICE OFFLINE
                    </span>
                `;
                phoneDisplay.innerHTML = `<span class="text-rose-500 font-medium italic">Service Node.js Offline</span>`;
                if (qrPlaceholderText) qrPlaceholderText.innerText = 'Microservice WhatsApp Gateway (Port 3001) belum aktif';
            } else {
                badgeContainer.innerHTML = `
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-extrabold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                        TERPUTUS
                    </span>
                `;
                phoneDisplay.innerHTML = `<span class="text-slate-400 font-normal italic">Belum terhubung</span>`;
                if (qrPlaceholderText) qrPlaceholderText.innerText = 'Menyiapkan QR Code dari Gateway...';
            }

            qrBox.classList.remove('hidden');
            pairingGuide.classList.remove('hidden');
            connectedNotice.classList.add('hidden');
            qrLiveIndicator.classList.add('hidden');

            qrImage.classList.add('hidden');
            qrPlaceholder.classList.remove('hidden');
        }
    }

    async function pollStatusAndQr() {
        try {
            const statusRes = await fetch('{{ route("admin.whatsapp.status") }}');
            if (!statusRes.ok) throw new Error('Gateway Offline');
            const statusData = await statusRes.json();

            const isOnline = statusData.online ?? false;

            if (statusData.status === 'connected') {
                updateUiState('connected', statusData.phone, null, isOnline);
                return;
            }

            // Jika status response sudah menyertakan QR data URL
            if (statusData.qr) {
                updateUiState('connecting', statusData.phone, statusData.qr, isOnline);
                return;
            }

            // Fallback: Jika belum ada QR di status, lakukan fetch ke endpoint /qr
            try {
                const qrRes = await fetch('{{ route("admin.whatsapp.qr") }}');
                if (qrRes.ok) {
                    const qrData = await qrRes.json();
                    const finalStatus = qrData.qr ? 'connecting' : (statusData.status || 'disconnected');
                    updateUiState(finalStatus, statusData.phone, qrData.qr, isOnline);
                    return;
                }
            } catch (qrErr) {
                // Abaikan jika endpoint qr sementara belum merespons
            }

            updateUiState(statusData.status || 'disconnected', statusData.phone, null, isOnline);
        } catch (e) {
            updateUiState('disconnected', null, null, false);
        }
    }

    function startLivePolling() {
        if (pollingInterval) clearInterval(pollingInterval);
        // Polling setiap 3 detik
        pollingInterval = setInterval(() => {
            pollStatusAndQr();
        }, 3000);
    }

    async function refreshStatusManual() {
        const icon = document.getElementById('refreshIcon');
        icon.classList.add('animate-spin');
        await pollStatusAndQr();
        setTimeout(() => icon.classList.remove('animate-spin'), 600);
        showAlert('Status WhatsApp Gateway berhasil diperbarui.');
    }

    function fillTestPreset() {
        document.getElementById('testMessage').value = 
            "Halo!\n\nIni adalah pesan uji coba dari sistem *Self-Hosted WhatsApp Gateway Baileys* ERP META Adhya Tirta Umbulan.\n\nGateway berfungsi normal & siap beroperasi! 🚀";
    }

    async function handleSendTest(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('submitTestBtn');
        const submitText = document.getElementById('submitTestText');
        const submitIcon = document.getElementById('submitTestIcon');
        const phone = document.getElementById('testPhone').value;
        const message = document.getElementById('testMessage').value;

        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        submitText.innerText = 'Mengirim pesan...';
        submitIcon.className = 'fa-solid fa-spinner animate-spin text-xs';

        try {
            const response = await fetch('{{ route("admin.whatsapp.send_test") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    phone_number: phone,
                    message: message
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showAlert(data.message, 'success');
            } else {
                showAlert(data.message || 'Gagal mengirim pesan uji coba.', 'error');
            }
        } catch (err) {
            showAlert('Gagal menghubungi server: ' + err.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
            submitText.innerText = 'Kirim Pesan Uji Coba';
            submitIcon.className = 'fa-solid fa-paper-plane text-xs';
        }
    }

    async function confirmDisconnect() {
        if (!confirm('Apakah Anda yakin ingin memutuskan koneksi WhatsApp Gateway? Sesi akan dihapus dan perangkat harus di-scan ulang.')) {
            return;
        }

        const disconnectBtn = document.getElementById('disconnectBtn');
        disconnectBtn.disabled = true;
        disconnectBtn.innerHTML = `<i class="fa-solid fa-spinner animate-spin"></i> <span>Memutuskan...</span>`;

        try {
            const response = await fetch('{{ route("admin.whatsapp.disconnect") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();
            if (response.ok && data.success) {
                showAlert(data.message, 'success');
                await pollStatusAndQr();
            } else {
                showAlert(data.message || 'Gagal memutuskan koneksi.', 'error');
            }
        } catch (e) {
            showAlert('Terjadi kesalahan sistem: ' + e.message, 'error');
        } finally {
            disconnectBtn.disabled = false;
            disconnectBtn.innerHTML = `<i class="fa-solid fa-power-off"></i> <span>Putuskan Koneksi / Logout</span>`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        pollStatusAndQr();
        startLivePolling();
    });
</script>
@endsection
