import * as Turbo from '@hotwired/turbo';

// Ekspor Turbo ke window agar dapat dikontrol jika perlu
window.Turbo = Turbo;

// Handler Global Event Turbo Load
document.addEventListener('turbo:load', () => {
    // Inisialisasi ulang tema Dark / Light
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    // Trigger event khusus untuk re-inisialisasi komponen
    window.dispatchEvent(new CustomEvent('app:navigated'));
});
