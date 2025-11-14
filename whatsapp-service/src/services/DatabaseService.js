const mysql = require('mysql2/promise');

class DatabaseService {
    constructor() {
        this.pool = null;
    }

    async connect() {
        try {
            this.pool = mysql.createPool({
                host: process.env.DB_HOST || 'localhost',
                port: process.env.DB_PORT || 3306,
                user: process.env.DB_USERNAME || 'root',
                password: process.env.DB_PASSWORD || '',
                database: process.env.DB_DATABASE || 'whatsapp_crm',
                waitForConnections: true,
                connectionLimit: 10,
                queueLimit: 0,
                enableKeepAlive: true,
                keepAliveInitialDelay: 0
            });

            // Test connection
            const connection = await this.pool.getConnection();
            console.log('Database connected successfully');
            connection.release();

            return true;
        } catch (error) {
            console.error('Database connection error:', error);
            throw error;
        }
    }

    async disconnect() {
        if (this.pool) {
            await this.pool.end();
            console.log('Database disconnected');
        }
    }

    async query(sql, params = []) {
        try {
            const [rows] = await this.pool.execute(sql, params);
            return rows;
        } catch (error) {
            console.error('Query error:', error);
            throw error;
        }
    }

    // WhatsApp Sessions
    async updateWhatsAppSession(data) {
        const {
            session_name,
            phone_number,
            status,
            qr_code,
            qr_generated_at,
            connected_at
        } = data;

        const sql = `
            INSERT INTO whatsapp_sessions
            (session_name, phone_number, status, qr_code, qr_generated_at, connected_at, last_seen_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                phone_number = VALUES(phone_number),
                status = VALUES(status),
                qr_code = VALUES(qr_code),
                qr_generated_at = VALUES(qr_generated_at),
                connected_at = VALUES(connected_at),
                last_seen_at = NOW()
        `;

        return await this.query(sql, [
            session_name,
            phone_number || null,
            status,
            qr_code || null,
            qr_generated_at || null,
            connected_at || null
        ]);
    }

    // Messages
    async saveMessage(data) {
        const {
            message_id,
            from_number,
            to_number,
            direction,
            type,
            content,
            media_url,
            media_mime_type,
            status,
            sent_at,
            delivered_at,
            read_at,
            lead_id
        } = data;

        const sql = `
            INSERT INTO whatsapp_messages
            (message_id, from_number, to_number, direction, type, content,
             media_url, media_mime_type, status, sent_at, delivered_at, read_at, lead_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                delivered_at = VALUES(delivered_at),
                read_at = VALUES(read_at)
        `;

        return await this.query(sql, [
            message_id,
            from_number,
            to_number,
            direction,
            type || 'text',
            content,
            media_url || null,
            media_mime_type || null,
            status || 'pending',
            sent_at || null,
            delivered_at || null,
            read_at || null,
            lead_id || null
        ]);
    }

    async updateMessageStatus(messageId, status) {
        const statusField = status === 'read' ? 'read_at' :
                           status === 'delivered' ? 'delivered_at' :
                           status === 'sent' ? 'sent_at' : null;

        let sql = `UPDATE whatsapp_messages SET status = ? WHERE message_id = ?`;
        let params = [status, messageId];

        if (statusField) {
            sql = `UPDATE whatsapp_messages SET status = ?, ${statusField} = NOW() WHERE message_id = ?`;
        }

        return await this.query(sql, params);
    }

    // Leads
    async findLeadByPhone(phone) {
        const sql = `
            SELECT * FROM leads
            WHERE phone = ? OR whatsapp_number = ?
            LIMIT 1
        `;
        const results = await this.query(sql, [phone, phone]);
        return results[0] || null;
    }

    async createLead(data) {
        const {
            name,
            phone,
            whatsapp_number,
            source,
            notes
        } = data;

        const sql = `
            INSERT INTO leads
            (name, phone, whatsapp_number, source, status, notes, created_at)
            VALUES (?, ?, ?, ?, 'new', ?, NOW())
        `;

        const result = await this.query(sql, [
            name,
            phone,
            whatsapp_number || phone,
            source || 'whatsapp',
            notes || ''
        ]);

        return result.insertId;
    }

    async updateLeadLastContact(leadId) {
        const sql = `UPDATE leads SET last_contact_at = NOW() WHERE id = ?`;
        return await this.query(sql, [leadId]);
    }

    // Interactions
    async createInteraction(data) {
        const {
            lead_id,
            user_id,
            type,
            title,
            description,
            metadata
        } = data;

        const sql = `
            INSERT INTO interactions
            (lead_id, user_id, type, title, description, metadata, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        `;

        return await this.query(sql, [
            lead_id,
            user_id || null,
            type,
            title,
            description || null,
            metadata ? JSON.stringify(metadata) : null
        ]);
    }

    // Message Queue
    async getPendingMessages(limit = 10) {
        const sql = `
            SELECT * FROM message_queue
            WHERE status = 'pending'
            AND attempts < max_attempts
            AND (scheduled_at IS NULL OR scheduled_at <= NOW())
            ORDER BY priority DESC, created_at ASC
            LIMIT ?
        `;
        return await this.query(sql, [limit]);
    }

    async updateMessageQueue(id, data) {
        const { status, attempts, error_message, sent_at } = data;

        const sql = `
            UPDATE message_queue
            SET status = ?, attempts = ?, error_message = ?, sent_at = ?
            WHERE id = ?
        `;

        return await this.query(sql, [
            status,
            attempts,
            error_message || null,
            sent_at || null,
            id
        ]);
    }

    async deleteFromQueue(id) {
        const sql = `DELETE FROM message_queue WHERE id = ?`;
        return await this.query(sql, [id]);
    }

    // Analytics
    async getDailyStats(date) {
        const sql = `SELECT * FROM daily_stats WHERE date = ?`;
        const results = await this.query(sql, [date]);
        return results[0] || null;
    }

    async updateDailyStats(date, stats) {
        const sql = `
            INSERT INTO daily_stats
            (date, new_leads, qualified_leads, won_deals, lost_deals,
             total_revenue, whatsapp_messages_sent, whatsapp_messages_received,
             conversion_rate, average_deal_value, metrics)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                new_leads = VALUES(new_leads),
                qualified_leads = VALUES(qualified_leads),
                won_deals = VALUES(won_deals),
                lost_deals = VALUES(lost_deals),
                total_revenue = VALUES(total_revenue),
                whatsapp_messages_sent = VALUES(whatsapp_messages_sent),
                whatsapp_messages_received = VALUES(whatsapp_messages_received),
                conversion_rate = VALUES(conversion_rate),
                average_deal_value = VALUES(average_deal_value),
                metrics = VALUES(metrics)
        `;

        return await this.query(sql, [
            date,
            stats.new_leads || 0,
            stats.qualified_leads || 0,
            stats.won_deals || 0,
            stats.lost_deals || 0,
            stats.total_revenue || 0,
            stats.whatsapp_messages_sent || 0,
            stats.whatsapp_messages_received || 0,
            stats.conversion_rate || 0,
            stats.average_deal_value || 0,
            stats.metrics ? JSON.stringify(stats.metrics) : null
        ]);
    }

    // Settings
    async getSetting(key) {
        const sql = `SELECT value, type FROM settings WHERE \`key\` = ?`;
        const results = await this.query(sql, [key]);

        if (results.length === 0) return null;

        const { value, type } = results[0];

        switch (type) {
            case 'number':
                return parseFloat(value);
            case 'boolean':
                return value === '1' || value === 'true';
            case 'json':
                return JSON.parse(value);
            default:
                return value;
        }
    }

    async updateSetting(key, value, type = 'string') {
        let stringValue = value;
        if (type === 'json') {
            stringValue = JSON.stringify(value);
        } else if (type === 'boolean') {
            stringValue = value ? '1' : '0';
        }

        const sql = `
            INSERT INTO settings (\`key\`, value, type)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE value = VALUES(value)
        `;

        return await this.query(sql, [key, stringValue, type]);
    }
}

module.exports = DatabaseService;
