class MessageQueueWorker {
    constructor(whatsappService, dbService) {
        this.whatsappService = whatsappService;
        this.dbService = dbService;
        this.isRunning = false;
        this.interval = null;
        this.processInterval = 5000; // Process every 5 seconds
    }

    start() {
        if (this.isRunning) {
            console.log('Message queue worker is already running');
            return;
        }

        console.log('Starting message queue worker...');
        this.isRunning = true;

        this.interval = setInterval(async () => {
            await this.processQueue();
        }, this.processInterval);
    }

    stop() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
        this.isRunning = false;
        console.log('Message queue worker stopped');
    }

    async processQueue() {
        if (!this.whatsappService.isConnected()) {
            // console.log('WhatsApp not connected, skipping queue processing');
            return;
        }

        try {
            // Get pending messages from queue
            const messages = await this.dbService.getPendingMessages(10);

            if (messages.length === 0) {
                return;
            }

            console.log(`Processing ${messages.length} queued messages...`);

            for (const message of messages) {
                await this.processMessage(message);

                // Small delay between messages
                await this.sleep(1000);
            }

        } catch (error) {
            console.error('Error processing queue:', error);
        }
    }

    async processMessage(message) {
        try {
            // Update status to processing
            await this.dbService.updateMessageQueue(message.id, {
                status: 'processing',
                attempts: message.attempts + 1,
                error_message: null,
                sent_at: null
            });

            // Send the message
            const result = await this.whatsappService.sendMessage(
                message.to_number,
                message.message,
                message.media_url
            );

            // Mark as sent
            await this.dbService.updateMessageQueue(message.id, {
                status: 'sent',
                attempts: message.attempts + 1,
                error_message: null,
                sent_at: new Date()
            });

            // Delete from queue after successful send
            await this.dbService.deleteFromQueue(message.id);

            console.log(`✓ Sent queued message to ${message.to_number}`);

        } catch (error) {
            console.error(`Error sending message to ${message.to_number}:`, error);

            // Update with error
            const newAttempts = message.attempts + 1;
            const status = newAttempts >= message.max_attempts ? 'failed' : 'pending';

            await this.dbService.updateMessageQueue(message.id, {
                status: status,
                attempts: newAttempts,
                error_message: error.message,
                sent_at: null
            });

            if (status === 'failed') {
                console.log(`✗ Message to ${message.to_number} failed after ${newAttempts} attempts`);
            }
        }
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

module.exports = MessageQueueWorker;
