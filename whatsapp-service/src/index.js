require('dotenv').config();
const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const WhatsAppService = require('./services/WhatsAppService');
const DatabaseService = require('./services/DatabaseService');
const MessageProcessor = require('./services/MessageProcessor');
const MessageQueueWorker = require('./workers/MessageQueueWorker');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Initialize services
const dbService = new DatabaseService();
const messageProcessor = new MessageProcessor(dbService);
const whatsappService = new WhatsAppService(messageProcessor, dbService);
const queueWorker = new MessageQueueWorker(whatsappService, dbService);

// Global error handler
process.on('unhandledRejection', (error) => {
    console.error('Unhandled rejection:', error);
});

// Health check
app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        whatsapp: whatsappService.getStatus(),
        timestamp: new Date().toISOString()
    });
});

// Get QR Code
app.get('/qr', async (req, res) => {
    try {
        const qrCode = whatsappService.getQRCode();
        if (qrCode) {
            res.json({
                success: true,
                qr_code: qrCode,
                status: whatsappService.getStatus()
            });
        } else {
            res.json({
                success: false,
                message: 'QR code not available. WhatsApp might be connected or initializing.',
                status: whatsappService.getStatus()
            });
        }
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Get connection status
app.get('/status', (req, res) => {
    const status = whatsappService.getStatus();
    const clientInfo = whatsappService.getClientInfo();

    res.json({
        success: true,
        status: status,
        client_info: clientInfo
    });
});

// Send message
app.post('/send-message', async (req, res) => {
    try {
        const { to, message, media_url } = req.body;

        if (!to || !message) {
            return res.status(400).json({
                success: false,
                error: 'Missing required fields: to, message'
            });
        }

        const result = await whatsappService.sendMessage(to, message, media_url);

        res.json({
            success: true,
            message_id: result.id._serialized,
            timestamp: result.timestamp
        });
    } catch (error) {
        console.error('Error sending message:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Send broadcast
app.post('/send-broadcast', async (req, res) => {
    try {
        const { recipients, message, media_url } = req.body;

        if (!recipients || !Array.isArray(recipients) || !message) {
            return res.status(400).json({
                success: false,
                error: 'Invalid request. Need recipients array and message.'
            });
        }

        const results = await whatsappService.sendBroadcast(recipients, message, media_url);

        res.json({
            success: true,
            total: recipients.length,
            sent: results.filter(r => r.success).length,
            failed: results.filter(r => !r.success).length,
            results: results
        });
    } catch (error) {
        console.error('Error sending broadcast:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Disconnect WhatsApp
app.post('/disconnect', async (req, res) => {
    try {
        await whatsappService.disconnect();
        res.json({
            success: true,
            message: 'Disconnected successfully'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Reconnect WhatsApp
app.post('/reconnect', async (req, res) => {
    try {
        await whatsappService.initialize();
        res.json({
            success: true,
            message: 'Reconnecting...'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Get chat history
app.get('/chats/:number', async (req, res) => {
    try {
        const { number } = req.params;
        const limit = parseInt(req.query.limit) || 50;

        const messages = await whatsappService.getChatHistory(number, limit);

        res.json({
            success: true,
            count: messages.length,
            messages: messages
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Initialize WhatsApp
async function initialize() {
    try {
        console.log('Starting WhatsApp CRM Service...');

        // Initialize database
        await dbService.connect();
        console.log('✓ Database connected');

        // Initialize WhatsApp
        await whatsappService.initialize();
        console.log('✓ WhatsApp service initialized');

        // Start message queue worker
        queueWorker.start();
        console.log('✓ Message queue worker started');

        // Start Express server
        app.listen(PORT, () => {
            console.log(`✓ Server running on port ${PORT}`);
            console.log(`\nEndpoints:`);
            console.log(`  - Health Check: http://localhost:${PORT}/health`);
            console.log(`  - QR Code: http://localhost:${PORT}/qr`);
            console.log(`  - Status: http://localhost:${PORT}/status`);
            console.log(`  - Send Message: POST http://localhost:${PORT}/send-message`);
            console.log(`\nScan the QR code in your terminal or visit /qr endpoint`);
        });

    } catch (error) {
        console.error('Failed to initialize:', error);
        process.exit(1);
    }
}

// Graceful shutdown
process.on('SIGINT', async () => {
    console.log('\nShutting down gracefully...');
    queueWorker.stop();
    await whatsappService.disconnect();
    await dbService.disconnect();
    process.exit(0);
});

// Start the application
initialize();
