const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode');
const qrcodeTerminal = require('qrcode-terminal');

class WhatsAppService {
    constructor(messageProcessor, dbService) {
        this.client = null;
        this.messageProcessor = messageProcessor;
        this.dbService = dbService;
        this.qrCode = null;
        this.status = 'disconnected';
        this.clientInfo = null;
        this.rateLimiter = {
            messages: [],
            limit: parseInt(process.env.WHATSAPP_RATE_LIMIT) || 30
        };
    }

    async initialize() {
        this.client = new Client({
            authStrategy: new LocalAuth({
                clientId: process.env.SESSION_NAME || 'default'
            }),
            puppeteer: {
                headless: true,
                args: [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-accelerated-2d-canvas',
                    '--no-first-run',
                    '--no-zygote',
                    '--disable-gpu'
                ]
            }
        });

        this.setupEventHandlers();

        try {
            await this.client.initialize();
        } catch (error) {
            console.error('Error initializing WhatsApp client:', error);
            throw error;
        }
    }

    setupEventHandlers() {
        // QR Code generated
        this.client.on('qr', async (qr) => {
            console.log('\n=== QR CODE GENERATED ===');
            qrcodeTerminal.generate(qr, { small: true });

            this.qrCode = await qrcode.toDataURL(qr);
            this.status = 'qr_ready';

            // Save to database
            await this.dbService.updateWhatsAppSession({
                session_name: process.env.SESSION_NAME || 'default',
                status: 'qr_ready',
                qr_code: this.qrCode,
                qr_generated_at: new Date()
            });

            console.log('QR Code available at /qr endpoint');
        });

        // Authenticated
        this.client.on('authenticated', () => {
            console.log('✓ WhatsApp authenticated');
            this.status = 'authenticated';
        });

        // Authentication failure
        this.client.on('auth_failure', (msg) => {
            console.error('Authentication failed:', msg);
            this.status = 'auth_failed';
        });

        // Ready
        this.client.on('ready', async () => {
            console.log('✓ WhatsApp is ready!');
            this.status = 'connected';
            this.qrCode = null;

            const info = this.client.info;
            this.clientInfo = {
                phone: info.wid.user,
                platform: info.platform,
                pushname: info.pushname
            };

            // Update database
            await this.dbService.updateWhatsAppSession({
                session_name: process.env.SESSION_NAME || 'default',
                status: 'connected',
                phone_number: info.wid.user,
                connected_at: new Date(),
                qr_code: null
            });

            console.log(`Connected as: ${info.pushname} (${info.wid.user})`);
        });

        // Message received
        this.client.on('message', async (message) => {
            try {
                await this.handleIncomingMessage(message);
            } catch (error) {
                console.error('Error handling incoming message:', error);
            }
        });

        // Message ACK (acknowledgment)
        this.client.on('message_ack', async (message, ack) => {
            try {
                await this.handleMessageAck(message, ack);
            } catch (error) {
                console.error('Error handling message ack:', error);
            }
        });

        // Disconnected
        this.client.on('disconnected', async (reason) => {
            console.log('WhatsApp disconnected:', reason);
            this.status = 'disconnected';
            this.clientInfo = null;

            await this.dbService.updateWhatsAppSession({
                session_name: process.env.SESSION_NAME || 'default',
                status: 'disconnected'
            });
        });
    }

    async handleIncomingMessage(message) {
        console.log(`Message from ${message.from}: ${message.body}`);

        const messageData = {
            message_id: message.id._serialized,
            from_number: message.from,
            to_number: message.to,
            direction: 'inbound',
            type: message.type,
            content: message.body,
            timestamp: message.timestamp
        };

        // Handle media
        if (message.hasMedia) {
            try {
                const media = await message.downloadMedia();
                messageData.media_mime_type = media.mimetype;
                messageData.media_url = `data:${media.mimetype};base64,${media.data}`;
            } catch (error) {
                console.error('Error downloading media:', error);
            }
        }

        // Process message (save to DB, create lead, etc.)
        await this.messageProcessor.processIncomingMessage(messageData);
    }

    async handleMessageAck(message, ack) {
        const ackStatus = {
            0: 'error',
            1: 'pending',
            2: 'sent',
            3: 'delivered',
            4: 'read'
        };

        const status = ackStatus[ack] || 'unknown';

        await this.dbService.updateMessageStatus(
            message.id._serialized,
            status
        );
    }

    async sendMessage(to, message, mediaUrl = null) {
        if (!this.isConnected()) {
            throw new Error('WhatsApp is not connected');
        }

        // Check rate limit
        if (!this.checkRateLimit()) {
            throw new Error('Rate limit exceeded. Please wait before sending more messages.');
        }

        try {
            // Format phone number
            const chatId = this.formatPhoneNumber(to);

            let sentMessage;

            if (mediaUrl) {
                // Send with media
                const media = await MessageMedia.fromUrl(mediaUrl);
                sentMessage = await this.client.sendMessage(chatId, media, {
                    caption: message
                });
            } else {
                // Send text only
                sentMessage = await this.client.sendMessage(chatId, message);
            }

            // Track for rate limiting
            this.trackMessage();

            // Save to database
            await this.dbService.saveMessage({
                message_id: sentMessage.id._serialized,
                from_number: sentMessage.from,
                to_number: sentMessage.to,
                direction: 'outbound',
                type: sentMessage.type,
                content: message,
                media_url: mediaUrl,
                status: 'sent',
                sent_at: new Date()
            });

            return sentMessage;
        } catch (error) {
            console.error('Error sending message:', error);
            throw error;
        }
    }

    async sendBroadcast(recipients, message, mediaUrl = null) {
        const results = [];
        const delay = 60000 / this.rateLimiter.limit; // Calculate delay between messages

        for (const recipient of recipients) {
            try {
                await this.sendMessage(recipient, message, mediaUrl);
                results.push({
                    to: recipient,
                    success: true
                });
            } catch (error) {
                results.push({
                    to: recipient,
                    success: false,
                    error: error.message
                });
            }

            // Delay between messages to respect rate limit
            if (recipient !== recipients[recipients.length - 1]) {
                await this.sleep(delay);
            }
        }

        return results;
    }

    async getChatHistory(number, limit = 50) {
        if (!this.isConnected()) {
            throw new Error('WhatsApp is not connected');
        }

        try {
            const chatId = this.formatPhoneNumber(number);
            const chat = await this.client.getChatById(chatId);
            const messages = await chat.fetchMessages({ limit });

            return messages.map(msg => ({
                id: msg.id._serialized,
                from: msg.from,
                to: msg.to,
                body: msg.body,
                timestamp: msg.timestamp,
                type: msg.type,
                hasMedia: msg.hasMedia
            }));
        } catch (error) {
            console.error('Error fetching chat history:', error);
            throw error;
        }
    }

    async disconnect() {
        if (this.client) {
            await this.client.destroy();
            this.status = 'disconnected';
            this.clientInfo = null;
        }
    }

    formatPhoneNumber(number) {
        // Remove all non-numeric characters
        let formatted = number.replace(/\D/g, '');

        // Add country code if not present (default to Indonesia +62)
        if (!formatted.startsWith('62')) {
            if (formatted.startsWith('0')) {
                formatted = '62' + formatted.substring(1);
            } else {
                formatted = '62' + formatted;
            }
        }

        return formatted + '@c.us';
    }

    checkRateLimit() {
        const now = Date.now();
        const oneMinuteAgo = now - 60000;

        // Remove messages older than 1 minute
        this.rateLimiter.messages = this.rateLimiter.messages.filter(
            timestamp => timestamp > oneMinuteAgo
        );

        return this.rateLimiter.messages.length < this.rateLimiter.limit;
    }

    trackMessage() {
        this.rateLimiter.messages.push(Date.now());
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    isConnected() {
        return this.status === 'connected' && this.client !== null;
    }

    getStatus() {
        return this.status;
    }

    getQRCode() {
        return this.qrCode;
    }

    getClientInfo() {
        return this.clientInfo;
    }
}

module.exports = WhatsAppService;
