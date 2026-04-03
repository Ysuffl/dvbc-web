import express from 'express';
import cors from 'cors';
import { makeWASocket, useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion } from '@whiskeysockets/baileys';
import QRCode from 'qrcode';
import pino from 'pino';
import dotenv from 'dotenv';
import fs from 'fs';
import path from 'path';

dotenv.config();

const app = express();
app.use(express.json());
app.use(cors({ origin: process.env.ALLOWED_ORIGINS?.split(',') || '*' }));

const PORT = process.env.PORT || 3001;
const SESSION_DIR = process.env.SESSION_DIR || './sessions';

// Setup Logger
const logger = pino({ level: 'info' });

let sock = null;
let qrCodeDataUrl = null;
let connectionStatus = 'DISCONNECTED';

async function connectToWhatsApp() {
    const { state, saveCreds } = await useMultiFileAuthState(SESSION_DIR);
    const { version, isLatest } = await fetchLatestBaileysVersion();

    logger.info(`Using WA v${version.join('.')}, isLatest: ${isLatest}`);

    sock = makeWASocket({
        version,
        auth: state,
        logger: pino({ level: 'silent' }), // Mute default logger
        browser: ['Dreamville', 'Chrome', '1.0.0'], // Membantu mencegah connection failure
        syncFullHistory: false, // Mempercepat koneksi
        generateHighQualityLinkPreview: false,
        markOnlineOnConnect: false
    });

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            qrCodeDataUrl = await QRCode.toDataURL(qr);
            connectionStatus = 'WAITING_FOR_SCAN';
            logger.info('QR Code generated. Waiting for scan...');
        }

        if (connection === 'close') {
            const shouldReconnect = lastDisconnect.error?.output?.statusCode !== DisconnectReason.loggedOut;
            logger.info(`Connection closed due to ${lastDisconnect.error?.message}. Reconnecting: ${shouldReconnect}`);

            qrCodeDataUrl = null;

            if (shouldReconnect) {
                connectionStatus = 'RECONNECTING'; // Let UI know we are trying instead of fully disconnected
                // Wait slightly before reconnecting
                setTimeout(connectToWhatsApp, 2000);
            } else {
                connectionStatus = 'DISCONNECTED';
                // User logged out, clear session
                // User logged out, clear session
                logger.info('Logged out. Clearing session directory.');
                fs.rmSync(SESSION_DIR, { recursive: true, force: true });
                // We don't automatically restart here, wait for intentional start from UI
            }
        } else if (connection === 'open') {
            logger.info('WhatsApp Connected Successfully!');
            connectionStatus = 'CONNECTED';
            qrCodeDataUrl = null; // Clear QR code as we are connected
        }
    });

    sock.ev.on('creds.update', saveCreds);
}

// Security Middleware
const authenticateToken = (req, res, next) => {
    const token = req.headers['authorization']?.split(' ')[1] || req.query.token;
    if (!token || token !== process.env.GATEWAY_API_SECRET) {
        return res.status(403).json({ error: 'Unauthorized: Invalid API Token' });
    }
    next();
};

app.use(authenticateToken); // Protect all routes below

// Endpoints

app.get('/status', (req, res) => {
    res.json({
        status: connectionStatus,
        qrUrl: connectionStatus === 'WAITING_FOR_SCAN' ? '/qr' : null
    });
});

app.get('/qr', (req, res) => {
    if (qrCodeDataUrl) {
        // Send QR code as image if prefer, or json. For UI json image string is easier
        res.json({ qr_base64: qrCodeDataUrl });
    } else {
        res.status(404).json({ error: 'QR Code not available currently', status: connectionStatus });
    }
});

app.post('/start', async (req, res) => {
    if (connectionStatus === 'CONNECTED') {
        return res.json({ message: 'Already connected', status: connectionStatus });
    }
    if (connectionStatus === 'WAITING_FOR_SCAN') {
        return res.json({ message: 'Waiting for scan. Call /qr to get it.', status: connectionStatus });
    }

    await connectToWhatsApp();
    res.json({ message: 'Server initializing connection...' });
});

app.post('/disconnect', async (req, res) => {
    if (sock) {
        await sock.logout();
        res.json({ message: 'Disconnected successfully' });
    } else {
        res.json({ message: 'Not connected' });
    }
});

app.post('/broadcast', async (req, res) => {
    if (connectionStatus !== 'CONNECTED') {
        return res.status(400).json({ error: 'WhatsApp is not connected' });
    }

    const { messages } = req.body;
    // expected format: messages: [{ to: '628xxx', text: 'message' }]

    if (!messages || !Array.isArray(messages)) {
        return res.status(400).json({ error: 'Invalid payload format. Expected messages array.' });
    }

    const results = [];

    logger.info(`Starting broadcast for ${messages.length} contacts`);

    for (const msg of messages) {
        const { to, text, image } = msg;

        // Validation: Must have 'to' and either 'text' or 'image'
        if (!to || (!text && !image)) {
            results.push({ to, status: 'failed', error: 'Missing destination or content (text/image)' });
            continue;
        }

        let cleanNumber = to.replace(/\D/g, '');
        // Otomatis ubah 08xxx menjadi 628xxx untuk format standar WhatsApp
        if (cleanNumber.startsWith('0')) {
            cleanNumber = '62' + cleanNumber.slice(1);
        }

        const formattedNumber = `${cleanNumber}@s.whatsapp.net`;

        try {
            // Check if number exists on WA
            const [result] = await sock.onWhatsApp(formattedNumber);
            if (!result || !result.exists) {
                results.push({ to, status: 'failed', error: 'Number not registered on WhatsApp' });
                continue;
            }

            // Prepare sending options
            let sendOptions = {};
            if (image) {
                // If it's an image message
                sendOptions = {
                    image: { url: image },
                    caption: text || ''
                };
            } else {
                // If it's just a text message
                sendOptions = { text: text };
            }

            // Send message
            await sock.sendMessage(result.jid, sendOptions);
            results.push({ to, status: 'success' });

            // Artificial delay to prevent ban (Random between 1s and 3s)
            const delay = Math.floor(Math.random() * 2000) + 1000;
            await new Promise(resolve => setTimeout(resolve, delay));

        } catch (error) {
            logger.error(`Failed to send to ${to}: ${error.message}`);
            results.push({ to, status: 'failed', error: error.message });
        }
    }

    res.json({
        message: 'Broadcast completed',
        results: results,
        total: results.length,
        success: results.filter(r => r.status === 'success').length,
        failed: results.filter(r => r.status === 'failed').length
    });
});

app.listen(PORT, async () => {
    logger.info(`WhatsApp Gateway Server running on port ${PORT}`);

    // Auto-connect if session files exist so user doesn't have to click "Activate" on restart
    if (fs.existsSync(path.join(SESSION_DIR, 'creds.json'))) {
        logger.info('Found existing session credentials. Auto-connecting to WhatsApp...');
        await connectToWhatsApp();
    }
});
