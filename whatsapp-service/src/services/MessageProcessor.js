class MessageProcessor {
    constructor(dbService) {
        this.dbService = dbService;
    }

    async processIncomingMessage(messageData) {
        try {
            // Extract phone number from WhatsApp ID
            const fromPhone = this.extractPhoneNumber(messageData.from_number);

            // Find or create lead
            let lead = await this.dbService.findLeadByPhone(fromPhone);

            if (!lead) {
                console.log(`New lead from ${fromPhone}`);

                // Create new lead
                const leadId = await this.dbService.createLead({
                    name: fromPhone, // Will be updated manually later
                    phone: fromPhone,
                    whatsapp_number: fromPhone,
                    source: 'whatsapp',
                    notes: `Auto-created from WhatsApp message: ${messageData.content}`
                });

                lead = { id: leadId };

                // Create journey milestone
                await this.createMilestone(leadId, 'First Contact', 'Lead initiated conversation via WhatsApp');
            }

            // Save message to database
            await this.dbService.saveMessage({
                ...messageData,
                lead_id: lead.id,
                status: 'received'
            });

            // Update lead's last contact
            await this.dbService.updateLeadLastContact(lead.id);

            // Create interaction record
            await this.dbService.createInteraction({
                lead_id: lead.id,
                type: 'whatsapp',
                title: 'WhatsApp Message Received',
                description: messageData.content,
                metadata: {
                    message_id: messageData.message_id,
                    message_type: messageData.type
                }
            });

            // Check for objections or keywords
            await this.detectObjections(lead.id, messageData.content);

            // Check for auto-responses
            await this.checkAutoResponses(lead.id, messageData.content);

            console.log(`✓ Processed message from lead #${lead.id}`);

            return { success: true, lead_id: lead.id };

        } catch (error) {
            console.error('Error processing incoming message:', error);
            throw error;
        }
    }

    async createMilestone(leadId, name, description) {
        const sql = `
            INSERT INTO journey_milestones (lead_id, name, description, icon, achieved_at)
            VALUES (?, ?, ?, ?, NOW())
        `;
        await this.dbService.query(sql, [leadId, name, description, 'message']);
    }

    async detectObjections(leadId, messageContent) {
        // Simple keyword detection for common objections
        const objectionKeywords = {
            'mahal': 'pricing',
            'expensive': 'pricing',
            'harga': 'pricing',
            'think about': 'timing',
            'pikir': 'timing',
            'vendor lain': 'competition',
            'competitor': 'competition',
            'atasan': 'authority',
            'boss': 'authority'
        };

        const lowerContent = messageContent.toLowerCase();

        for (const [keyword, category] of Object.entries(objectionKeywords)) {
            if (lowerContent.includes(keyword)) {
                // Find matching template
                const sql = `
                    SELECT * FROM objection_templates
                    WHERE category = ? AND is_active = 1
                    LIMIT 1
                `;
                const templates = await this.dbService.query(sql, [category]);

                if (templates.length > 0) {
                    const template = templates[0];

                    // Log the objection
                    const logSql = `
                        INSERT INTO objection_logs
                        (lead_id, template_id, objection, created_at)
                        VALUES (?, ?, ?, NOW())
                    `;
                    await this.dbService.query(logSql, [
                        leadId,
                        template.id,
                        messageContent
                    ]);

                    console.log(`Detected objection (${category}) from lead #${leadId}`);

                    // You can optionally auto-send response here
                    // or flag for manual review
                }

                break; // Only detect first objection
            }
        }
    }

    async checkAutoResponses(leadId, messageContent) {
        // Check for common greetings
        const greetings = ['halo', 'hello', 'hi', 'hey', 'pagi', 'siang', 'malam'];
        const lowerContent = messageContent.toLowerCase();

        // Check if this is first message from lead
        const messageCount = await this.getLeadMessageCount(leadId);

        if (messageCount === 1) {
            // Check if auto-response is enabled
            const autoResponseEnabled = await this.dbService.getSetting('auto_response_enabled');

            if (autoResponseEnabled) {
                const autoResponseMessage = await this.dbService.getSetting('auto_response_message') ||
                    'Terima kasih telah menghubungi kami! Tim kami akan segera merespon pesan Anda.';

                // Queue auto-response
                const sql = `
                    INSERT INTO message_queue
                    (to_number, message, priority, status)
                    VALUES (?, ?, 10, 'pending')
                `;

                const lead = await this.dbService.query('SELECT whatsapp_number FROM leads WHERE id = ?', [leadId]);
                if (lead.length > 0) {
                    await this.dbService.query(sql, [lead[0].whatsapp_number, autoResponseMessage]);
                    console.log(`Queued auto-response for lead #${leadId}`);
                }
            }
        }
    }

    async getLeadMessageCount(leadId) {
        const sql = `SELECT COUNT(*) as count FROM whatsapp_messages WHERE lead_id = ?`;
        const result = await this.dbService.query(sql, [leadId]);
        return result[0].count;
    }

    extractPhoneNumber(whatsappId) {
        // WhatsApp ID format: 628123456789@c.us
        return whatsappId.replace('@c.us', '').replace('@g.us', '');
    }

    formatPhoneNumber(phone) {
        // Remove all non-numeric characters
        let formatted = phone.replace(/\D/g, '');

        // Add country code if not present (default to Indonesia +62)
        if (!formatted.startsWith('62')) {
            if (formatted.startsWith('0')) {
                formatted = '62' + formatted.substring(1);
            } else {
                formatted = '62' + formatted;
            }
        }

        return formatted;
    }
}

module.exports = MessageProcessor;
