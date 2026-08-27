import makeWASocket, {
    DisconnectReason,
    useMultiFileAuthState,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore,
    Browsers
} from '@whiskeysockets/baileys';
import { Boom } from '@hapi/boom';
import express from 'express';
import cors from 'cors';
import pino from 'pino';
import QRCode from 'qrcode';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3001;

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const AUTH_PATH = path.join(__dirname, 'auth_session');

let sock = null;
let currentQr = null;
let currentQrDataUrl = null;
let isConnecting = false;
let manualLogoutRequested = false;

// Pastikan direktori sesi autentikasi tersedia
function ensureAuthDir() {
    if (!fs.existsSync(AUTH_PATH)) {
        fs.mkdirSync(AUTH_PATH, { recursive: true });
    }
}
ensureAuthDir();

/**
 * Bersihkan sesi kredensial di disk
 */
function clearAuthSession() {
    try {
        if (fs.existsSync(AUTH_PATH)) {
            fs.rmSync(AUTH_PATH, { recursive: true, force: true });
        }
        ensureAuthDir();
        console.log('[WhatsApp Gateway] 🗑️ Sesi kredensial lokal berhasil dibersihkan.');
    } catch (err) {
        console.error('[WhatsApp Gateway] Gagal membersihkan folder auth_session:', err);
    }
}

/**
 * Mutex / Message Queueing Mechanism
 * Mencegah race condition, flooding, dan socket reset saat pengiriman pesan beruntun
 */
let sendQueue = Promise.resolve();
const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

async function enqueueMessage(targetJid, textMessage) {
    return new Promise((resolve, reject) => {
        sendQueue = sendQueue
            .then(async () => {
                if (!sock || !sock.user || !sock.user.id) {
                    throw new Error('WhatsApp Gateway belum terhubung.');
                }
                const sent = await sock.sendMessage(targetJid, { text: textMessage });
                // Delay 500ms antar pesan untuk mencegah rate limiting / socket drop dari WhatsApp
                await delay(500);
                resolve(sent);
            })
            .catch((err) => {
                reject(err);
            });
    });
}

/**
 * Inisialisasi Socket Baileys dengan Arsitektur Persistent Connection
 */
async function startWhatsAppSocket() {
    if (isConnecting) return;
    isConnecting = true;

    try {
        ensureAuthDir();
        const { state, saveCreds } = await useMultiFileAuthState(AUTH_PATH);
        const { version } = await fetchLatestBaileysVersion().catch(() => ({ version: [2, 3000, 1015901307] }));

        console.log(`[WhatsApp Gateway] 🔄 Menginisialisasi socket Baileys v${version.join('.')}...`);

        sock = makeWASocket({
            version,
            logger: pino({ level: 'silent' }),
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, pino({ level: 'silent' })),
            },
            browser: Browsers.ubuntu('Chrome'),
            syncFullHistory: false,
            generateHighQualityLinkPreview: false,
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000,
            keepAliveIntervalMs: 15000,
            emitOwnEvents: false,
            retryRequestDelayMs: 250,
        });

        // Wajib simpan update kredensial setiap ada perubahan state
        sock.ev.on('creds.update', saveCreds);

        // Pantau siklus koneksi
        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                currentQr = qr;
                try {
                    currentQrDataUrl = await QRCode.toDataURL(qr, {
                        margin: 2,
                        scale: 8,
                        color: {
                            dark: '#0f172a',
                            light: '#ffffff'
                        }
                    });
                } catch (qrErr) {
                    console.error('[WhatsApp Gateway] Gagal generate Data URL QR:', qrErr);
                }
                console.log('[WhatsApp Gateway] 📱 QR Code baru siap untuk di-scan.');
            }

            if (connection === 'open') {
                isConnecting = false;
                manualLogoutRequested = false;
                currentQr = null;
                currentQrDataUrl = null;

                const rawJid = sock?.user?.id || '';
                const phone = rawJid.split(':')[0].replace(/[^0-9]/g, '');
                console.log(`[WhatsApp Gateway] ✅ Socket TERHUBUNG & STABIL. Nomor: +${phone}`);
            }

            if (connection === 'close') {
                isConnecting = false;

                const statusCode = (lastDisconnect?.error instanceof Boom)
                    ? lastDisconnect.error.output?.statusCode
                    : lastDisconnect?.error?.statusCode;

                const isLoggedOut = statusCode === DisconnectReason.loggedOut;

                console.warn(`[WhatsApp Gateway] ⚠️ Koneksi terputus. Status Code: ${statusCode}. Manual Logout: ${manualLogoutRequested}`);

                // STRICT DISCONNECT PROTECTION:
                // Sesi di disk HANYA BOLEH DIHAPUS jika:
                // 1. Admin secara eksplisit memicu POST /disconnect (manualLogoutRequested === true)
                // 2. ATAU perangkat secara resmi di-unpair dari WhatsApp smartphone (isLoggedOut === true / 401)
                if (manualLogoutRequested || isLoggedOut) {
                    console.log('[WhatsApp Gateway] 🗑️ Menghapus sesi kredensial karena logout permanen...');
                    currentQr = null;
                    currentQrDataUrl = null;
                    clearAuthSession();
                    manualLogoutRequested = false;
                    setTimeout(() => startWhatsAppSocket(), 2000);
                } else {
                    // SILENT AUTO-RECONNECT:
                    // JANGAN hapus folder sesi! Cukup sambungkan ulang socket secara transparan di background
                    console.log(`[WhatsApp Gateway] 🔄 Silent auto-reconnect (Status ${statusCode}). Menghubungkan ulang dalam 3 detik...`);
                    setTimeout(() => startWhatsAppSocket(), 3000);
                }
            }
        });

    } catch (err) {
        console.error('[WhatsApp Gateway Init Error]:', err);
        isConnecting = false;
        setTimeout(() => startWhatsAppSocket(), 5000);
    }
}

// ==========================================
// REST API ENDPOINTS
// ==========================================

/**
 * GET /status
 * Status koneksi real-time untuk dashboard & scheduler
 */
app.get('/status', (req, res) => {
    const isConnected = !!(sock && sock.user && sock.user.id);
    const phone = isConnected ? sock.user.id.split(':')[0].replace(/[^0-9]/g, '') : null;

    res.json({
        success: true,
        online: true,
        status: isConnected ? 'connected' : (currentQr ? 'connecting' : 'disconnected'),
        phone: phone,
        qr: currentQrDataUrl,
        raw: currentQr,
        uptime: process.uptime()
    });
});

/**
 * GET /qr
 * Mengembalikan string Data URL QR Code
 */
app.get('/qr', (req, res) => {
    const isConnected = !!(sock && sock.user && sock.user.id);
    const phone = isConnected ? sock.user.id.split(':')[0].replace(/[^0-9]/g, '') : null;

    if (isConnected) {
        return res.json({
            success: true,
            status: 'connected',
            phone: phone,
            qr: null,
            message: 'Perangkat sudah terhubung.'
        });
    }

    res.json({
        success: true,
        status: currentQr ? 'connecting' : 'disconnected',
        qr: currentQrDataUrl,
        raw: currentQr,
        message: currentQrDataUrl ? 'QR code siap di-scan.' : 'Menyiapkan QR code...'
    });
});

/**
 * POST /send-message
 * Mengirim pesan dengan proteksi sequential queue / mutex
 */
app.post('/send-message', async (req, res) => {
    const { number, message } = req.body;

    if (!number || !message) {
        return res.status(400).json({
            success: false,
            message: 'Parameter "number" dan "message" wajib disertakan.'
        });
    }

    const isConnected = !!(sock && sock.user && sock.user.id);
    if (!isConnected) {
        return res.status(503).json({
            success: false,
            message: 'WhatsApp Gateway belum terhubung. Silakan sambungkan perangkat terlebih dahulu.'
        });
    }

    // Format nomor WhatsApp: bersihkan non-angka, ganti 08 -> 628
    let cleanNumber = String(number).replace(/[^0-9]/g, '');
    if (cleanNumber.startsWith('0')) {
        cleanNumber = '62' + cleanNumber.slice(1);
    } else if (cleanNumber.startsWith('8')) {
        cleanNumber = '62' + cleanNumber;
    }
    if (!cleanNumber.endsWith('@s.whatsapp.net')) {
        cleanNumber = cleanNumber + '@s.whatsapp.net';
    }

    try {
        const sent = await enqueueMessage(cleanNumber, message);

        return res.json({
            success: true,
            message: 'Pesan berhasil dikirim.',
            messageId: sent?.key?.id,
            timestamp: sent?.messageTimestamp,
            target: cleanNumber,
            deliveryStatus: 'sent'
        });
    } catch (err) {
        console.error('[WhatsApp Gateway Send Error]:', err);
        return res.status(500).json({
            success: false,
            message: 'Gagal mengirim pesan: ' + (err.message || 'Unknown error'),
            error: err.message
        });
    }
});

/**
 * POST /disconnect
 * Manual Disconnect dari Admin (Satu-satunya pemicu penghapusan sesi lokal)
 */
app.post('/disconnect', async (req, res) => {
    try {
        console.log('[WhatsApp Gateway] 🛑 Permintaan manual disconnect diterima dari Admin...');
        manualLogoutRequested = true;
        currentQr = null;
        currentQrDataUrl = null;

        if (sock) {
            await sock.logout().catch(() => {});
        }

        clearAuthSession();
        manualLogoutRequested = false;

        // Restart socket untuk langsung menyiapkan QR pairing baru
        setTimeout(() => {
            startWhatsAppSocket();
        }, 1500);

        return res.json({
            success: true,
            message: 'Sesi WhatsApp berhasil diputus oleh admin. Sesi baru siap di-scan.'
        });
    } catch (err) {
        console.error('[WhatsApp Gateway Disconnect Error]:', err);
        return res.status(500).json({
            success: false,
            message: 'Gagal memutuskan sesi: ' + (err.message || 'Unknown error'),
            error: err.message
        });
    }
});

// Penanganan global error agar Node.js tidak pernah crash
process.on('uncaughtException', (err) => {
    console.error('[WhatsApp Gateway Uncaught Exception]:', err);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('[WhatsApp Gateway Unhandled Rejection]:', reason);
});

// Mulai Server & Inisialisasi Socket Baileys
app.listen(PORT, () => {
    console.log(`[WhatsApp Gateway Microservice] 🚀 Aktif di http://127.0.0.1:${PORT}`);
    startWhatsAppSocket();
});
