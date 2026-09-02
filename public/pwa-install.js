(function() {
    let deferredPrompt;
    let isInstallable = false;

    window.addEventListener('beforeinstallprompt', (e) => {
        // Cegah infobar bawaan peramban muncul otomatis
        e.preventDefault();
        deferredPrompt = e;
        isInstallable = true;

        // Tampilkan tombol instalasi PWA di antarmuka
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.style.display = 'block';
        }

        window.dispatchEvent(new CustomEvent('pwa-installable', { detail: { prompt: e } }));
    });

    window.addEventListener('appinstalled', (event) => {
        console.log('PWA berhasil diinstal.');
        isInstallable = false;
        deferredPrompt = null;
        
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.style.display = 'none';
        }

        window.dispatchEvent(new CustomEvent('pwa-installed'));
    });

    window.laravelPwaInstall = {
        // Memeriksa apakah aplikasi dapat diinstal
        canInstall: function() {
            return isInstallable;
        },

        // Menampilkan prompt dialog instalasi aplikasi
        showPrompt: async function() {
            if (!deferredPrompt) {
                console.warn('[Laravel PWA] Prompt instalasi tidak tersedia.');
                return;
            }

            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            console.log(`[Laravel PWA] Respons pengguna terhadap instalasi: ${outcome}`);
            
            if (outcome === 'accepted') {
                isInstallable = false;
                const installBtn = document.getElementById('pwa-install-btn');
                if (installBtn) {
                    installBtn.style.display = 'none';
                }
            }
            
            deferredPrompt = null;
            return outcome;
        },

        // Memeriksa apakah aplikasi sedang berjalan dalam mode mandiri (standalone)
        isStandalone: function() {
            return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        }
    };

    // Inisialisasi event listener tombol instalasi jika tersedia pada DOM
    document.addEventListener('DOMContentLoaded', () => {
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.addEventListener('click', () => {
                window.laravelPwaInstall.showPrompt();
            });
        }
    });
})();
