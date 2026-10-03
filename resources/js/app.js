import * as Turbo from '@hotwired/turbo';

// Nonaktifkan snapshot cache Turbo secara menyeluruh agar hemat RAM browser dan selalu meminta data segar
Turbo.config.cache.size = 0;

// Ekspor Turbo ke window agar dapat dikontrol jika perlu
window.Turbo = Turbo;

// Pengendali Overlay Loading Halaman
let loadingStartTime = 0;
const MIN_DISPLAY_DURATION_MS = 200; // Durasi visual minimal agar animasi logo tampil elegan dan tidak berkedip kasar

export function showPageLoading() {
    const overlay = document.getElementById('pageLoadingOverlay');
    if (!overlay) return;

    loadingStartTime = Date.now();
    overlay.classList.remove('hidden');
    // Force reflow agar transisi opacity CSS berjalan mulus
    void overlay.offsetWidth;
    overlay.classList.remove('opacity-0', 'pointer-events-none');
    overlay.classList.add('opacity-100', 'pointer-events-auto');
}

export function hidePageLoading() {
    const overlay = document.getElementById('pageLoadingOverlay');
    if (!overlay) return;

    const elapsed = Date.now() - loadingStartTime;
    const remainingDelay = Math.max(0, MIN_DISPLAY_DURATION_MS - elapsed);

    setTimeout(() => {
        overlay.classList.remove('opacity-100', 'pointer-events-auto');
        overlay.classList.add('opacity-0', 'pointer-events-none');

        setTimeout(() => {
            if (overlay.classList.contains('opacity-0')) {
                overlay.classList.add('hidden');
            }
        }, 280);
    }, remainingDelay);
}

export function triggerPageTransition() {
    const content = document.getElementById('mainContent');
    if (content) {
        content.classList.remove('page-transition-enter');
        void content.offsetWidth; // Force reflow
        content.classList.add('page-transition-enter');
    }
}

// Global helper agar bisa dipanggil dari modul atau skrip view lain
window.showPageLoading = showPageLoading;
window.hidePageLoading = hidePageLoading;
window.triggerPageTransition = triggerPageTransition;

// Event Turbo: Navigasi dimulai -> Tampilkan loading overlay seketika
document.addEventListener('turbo:visit', () => {
    showPageLoading();
});

// Event Turbo: Request HTTP penarikan halaman dimulai
document.addEventListener('turbo:request-start', () => {
    showPageLoading();
});

// Event Turbo: Konten baru telah dirender ke dalam DOM
document.addEventListener('turbo:render', () => {
    triggerPageTransition();
});

// Event Turbo: Halaman selesai dimuat
document.addEventListener('turbo:load', () => {
    hidePageLoading();
    triggerPageTransition();

    // Inisialisasi tema Dark / Light (Default: Light Mode untuk user awal / login)
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
        if (!localStorage.getItem('theme')) {
            localStorage.setItem('theme', 'light');
        }
    }

    // Selalu sinkronkan tampilan ikon tombol switch tema
    if (typeof window.syncThemeIcon === 'function') {
        window.syncThemeIcon();
    } else {
        const icon = document.getElementById('themeToggleIcon');
        const btn = document.getElementById('themeToggleBtn');
        if (icon) {
            const isDark = document.documentElement.classList.contains('dark');
            icon.className = isDark ? 'fa-solid fa-moon text-slate-300 text-sm' : 'fa-solid fa-sun text-amber-500 text-sm';
            if (btn) {
                btn.setAttribute('title', isDark ? 'Beralih ke Mode Terang (Light Mode)' : 'Beralih ke Mode Gelap (Dark Mode)');
            }
        }
    }

    // Trigger event khusus untuk re-inisialisasi komponen
    window.dispatchEvent(new CustomEvent('app:navigated'));
});

// Event Turbo: Penarikan request gagal atau error
document.addEventListener('turbo:fetch-request-error', () => {
    hidePageLoading();
});

// Form submit via Turbo Drive
document.addEventListener('turbo:submit-start', () => {
    showPageLoading();
});

document.addEventListener('turbo:submit-end', () => {
    hidePageLoading();
});


