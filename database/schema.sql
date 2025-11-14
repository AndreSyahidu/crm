-- ============================================
-- WhatsApp CRM Database Schema
-- MySQL 8.0+
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS whatsapp_crm
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE whatsapp_crm;

-- ============================================
-- USERS & AUTHENTICATION
-- ============================================

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'sales_rep', 'manager') DEFAULT 'sales_rep',
    avatar VARCHAR(255),
    phone VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- LEADS & CUSTOMERS
-- ============================================

CREATE TABLE leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    whatsapp_number VARCHAR(50),
    company VARCHAR(255),
    position VARCHAR(255),
    source ENUM('whatsapp', 'manual', 'website', 'referral', 'other') DEFAULT 'whatsapp',
    status ENUM('new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost') DEFAULT 'new',
    lead_score INT DEFAULT 0,
    expected_revenue DECIMAL(15, 2) DEFAULT 0,
    actual_revenue DECIMAL(15, 2) DEFAULT 0,
    lifetime_value DECIMAL(15, 2) DEFAULT 0,
    acquisition_cost DECIMAL(15, 2) DEFAULT 0,
    assigned_to BIGINT UNSIGNED,
    last_contact_at TIMESTAMP NULL,
    converted_at TIMESTAMP NULL,
    lost_reason TEXT,
    notes TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_phone (phone),
    INDEX idx_whatsapp (whatsapp_number),
    INDEX idx_status (status),
    INDEX idx_source (source),
    INDEX idx_assigned (assigned_to),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SALES PIPELINE
-- ============================================

CREATE TABLE pipeline_stages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    position INT NOT NULL,
    color VARCHAR(7) DEFAULT '#3b82f6',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE deals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    value DECIMAL(15, 2) NOT NULL,
    stage_id BIGINT UNSIGNED NOT NULL,
    probability INT DEFAULT 50,
    expected_close_date DATE,
    actual_close_date DATE,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    position_in_stage INT DEFAULT 0,
    assigned_to BIGINT UNSIGNED,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (stage_id) REFERENCES pipeline_stages(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_stage (stage_id),
    INDEX idx_lead (lead_id),
    INDEX idx_assigned (assigned_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WHATSAPP MESSAGES
-- ============================================

CREATE TABLE whatsapp_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED,
    message_id VARCHAR(255) UNIQUE,
    from_number VARCHAR(50) NOT NULL,
    to_number VARCHAR(50) NOT NULL,
    direction ENUM('inbound', 'outbound') NOT NULL,
    type ENUM('text', 'image', 'video', 'audio', 'document', 'sticker', 'location', 'contact') DEFAULT 'text',
    content TEXT,
    media_url VARCHAR(500),
    media_mime_type VARCHAR(100),
    status ENUM('pending', 'sent', 'delivered', 'read', 'failed') DEFAULT 'pending',
    is_from_broadcast BOOLEAN DEFAULT FALSE,
    broadcast_id BIGINT UNSIGNED,
    read_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_lead (lead_id),
    INDEX idx_message_id (message_id),
    INDEX idx_from (from_number),
    INDEX idx_to (to_number),
    INDEX idx_direction (direction),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CUSTOMER JOURNEY & INTERACTIONS
-- ============================================

CREATE TABLE interactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED,
    type ENUM('call', 'email', 'meeting', 'whatsapp', 'note', 'task', 'deal_update', 'status_change') NOT NULL,
    title VARCHAR(255),
    description TEXT,
    duration INT, -- in minutes
    outcome TEXT,
    scheduled_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_lead (lead_id),
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE journey_milestones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    achieved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metadata JSON,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    INDEX idx_lead (lead_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- OBJECTION HANDLING
-- ============================================

CREATE TABLE objection_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    objection TEXT NOT NULL,
    response TEXT NOT NULL,
    category VARCHAR(100),
    usage_count INT DEFAULT 0,
    success_rate DECIMAL(5, 2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE objection_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NOT NULL,
    template_id BIGINT UNSIGNED,
    objection TEXT NOT NULL,
    response_sent TEXT,
    was_successful BOOLEAN,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES objection_templates(id) ON DELETE SET NULL,
    INDEX idx_lead (lead_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- FOLLOW-UP AUTOMATION
-- ============================================

CREATE TABLE follow_up_sequences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    trigger_type ENUM('manual', 'no_activity', 'status_change', 'deal_stage') NOT NULL,
    trigger_config JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE follow_up_steps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sequence_id BIGINT UNSIGNED NOT NULL,
    step_number INT NOT NULL,
    delay_days INT NOT NULL,
    action_type ENUM('whatsapp', 'email', 'task', 'note') NOT NULL,
    message_template TEXT,
    subject VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sequence_id) REFERENCES follow_up_sequences(id) ON DELETE CASCADE,
    INDEX idx_sequence (sequence_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE follow_up_enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NOT NULL,
    sequence_id BIGINT UNSIGNED NOT NULL,
    current_step INT DEFAULT 0,
    status ENUM('active', 'paused', 'completed', 'cancelled') DEFAULT 'active',
    next_action_at TIMESTAMP NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (sequence_id) REFERENCES follow_up_sequences(id) ON DELETE CASCADE,
    INDEX idx_lead (lead_id),
    INDEX idx_next_action (next_action_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BROADCAST CAMPAIGNS
-- ============================================

CREATE TABLE broadcast_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    media_url VARCHAR(500),
    media_type VARCHAR(50),
    segment_filter JSON,
    scheduled_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    status ENUM('draft', 'scheduled', 'sending', 'completed', 'cancelled') DEFAULT 'draft',
    total_recipients INT DEFAULT 0,
    sent_count INT DEFAULT 0,
    delivered_count INT DEFAULT 0,
    read_count INT DEFAULT 0,
    reply_count INT DEFAULT 0,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_scheduled (scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE broadcast_recipients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    lead_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'sent', 'delivered', 'read', 'replied', 'failed') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    replied_at TIMESTAMP NULL,
    error_message TEXT,
    FOREIGN KEY (campaign_id) REFERENCES broadcast_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    INDEX idx_campaign (campaign_id),
    INDEX idx_lead (lead_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEGMENTATION & TAGS
-- ============================================

CREATE TABLE tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    color VARCHAR(7) DEFAULT '#3b82f6',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lead_tags (
    lead_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (lead_id, tag_id),
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE segments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    filter_rules JSON NOT NULL,
    is_dynamic BOOLEAN DEFAULT TRUE,
    cached_count INT DEFAULT 0,
    last_calculated_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TASKS & REMINDERS
-- ============================================

CREATE TABLE tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED,
    assigned_to BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    due_date TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_lead (lead_id),
    INDEX idx_assigned (assigned_to),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ANALYTICS & REPORTS
-- ============================================

CREATE TABLE daily_stats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    new_leads INT DEFAULT 0,
    qualified_leads INT DEFAULT 0,
    won_deals INT DEFAULT 0,
    lost_deals INT DEFAULT 0,
    total_revenue DECIMAL(15, 2) DEFAULT 0,
    whatsapp_messages_sent INT DEFAULT 0,
    whatsapp_messages_received INT DEFAULT 0,
    conversion_rate DECIMAL(5, 2) DEFAULT 0,
    average_deal_value DECIMAL(15, 2) DEFAULT 0,
    metrics JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id BIGINT UNSIGNED,
    changes JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WHATSAPP SERVICE CONFIG
-- ============================================

CREATE TABLE whatsapp_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_name VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(50),
    status ENUM('disconnected', 'connecting', 'connected', 'qr_ready') DEFAULT 'disconnected',
    qr_code TEXT,
    qr_generated_at TIMESTAMP NULL,
    connected_at TIMESTAMP NULL,
    last_seen_at TIMESTAMP NULL,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE message_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    to_number VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    media_url VARCHAR(500),
    priority INT DEFAULT 5,
    status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    error_message TEXT,
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SETTINGS & CONFIGURATIONS
-- ============================================

CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA
-- ============================================

-- Default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Pipeline stages
INSERT INTO pipeline_stages (name, slug, position, color) VALUES
('New Lead', 'new-lead', 1, '#3b82f6'),
('Qualified', 'qualified', 2, '#8b5cf6'),
('Proposal', 'proposal', 3, '#f59e0b'),
('Negotiation', 'negotiation', 4, '#ec4899'),
('Won', 'won', 5, '#10b981'),
('Lost', 'lost', 6, '#ef4444');

-- Default objection templates
INSERT INTO objection_templates (title, objection, category, response) VALUES
('Harga Terlalu Mahal', 'Harganya terlalu mahal', 'pricing', 'Saya memahami kekhawatiran Anda tentang harga. Mari saya jelaskan nilai yang Anda dapatkan...'),
('Perlu Diskusi dengan Atasan', 'Saya perlu diskusi dengan atasan dulu', 'authority', 'Tentu, saya mengerti. Apakah saya bisa membantu menyiapkan presentasi untuk atasan Anda?'),
('Sudah Punya Vendor Lain', 'Kami sudah bekerja dengan vendor lain', 'competition', 'Saya menghargai loyalitas Anda. Boleh saya tahu apa yang Anda sukai dari vendor saat ini?'),
('Perlu Waktu Berpikir', 'Saya perlu waktu untuk berpikir', 'timing', 'Saya mengerti. Boleh saya tahu kekhawatiran spesifik apa yang ingin Anda pertimbangkan?');

-- Default follow-up sequence
INSERT INTO follow_up_sequences (name, description, trigger_type, trigger_config) VALUES
('Default Follow-Up', 'Sequence follow-up standar untuk leads baru', 'manual', '{}');

INSERT INTO follow_up_steps (sequence_id, step_number, delay_days, action_type, message_template) VALUES
(1, 1, 0, 'whatsapp', 'Halo {name}, terima kasih sudah menghubungi kami! Ada yang bisa saya bantu?'),
(1, 2, 3, 'whatsapp', 'Halo {name}, saya ingin follow-up pesan sebelumnya. Apakah Anda masih tertarik?'),
(1, 3, 7, 'whatsapp', 'Halo {name}, kami punya penawaran spesial untuk Anda. Tertarik untuk diskusi?');

-- Default tags
INSERT INTO tags (name, color) VALUES
('Hot Lead', '#ef4444'),
('High Value', '#f59e0b'),
('Need Follow-up', '#3b82f6'),
('VIP', '#8b5cf6'),
('Referral', '#10b981');

-- Default settings
INSERT INTO settings (`key`, value, type, description, is_public) VALUES
('company_name', 'My Company', 'string', 'Nama perusahaan', true),
('whatsapp_rate_limit', '30', 'number', 'Maksimal pesan per menit', false),
('daily_recap_time', '08:00', 'string', 'Waktu kirim daily recap (HH:MM)', false),
('default_currency', 'IDR', 'string', 'Mata uang default', true),
('timezone', 'Asia/Jakarta', 'string', 'Timezone', true);

-- ============================================
-- VIEWS FOR ANALYTICS
-- ============================================

CREATE VIEW lead_conversion_funnel AS
SELECT
    status,
    COUNT(*) as count,
    SUM(expected_revenue) as total_value
FROM leads
GROUP BY status;

CREATE VIEW top_performing_reps AS
SELECT
    u.id,
    u.name,
    COUNT(DISTINCT l.id) as total_leads,
    COUNT(DISTINCT CASE WHEN l.status = 'won' THEN l.id END) as won_deals,
    SUM(CASE WHEN l.status = 'won' THEN l.actual_revenue ELSE 0 END) as total_revenue
FROM users u
LEFT JOIN leads l ON l.assigned_to = u.id
WHERE u.role = 'sales_rep'
GROUP BY u.id, u.name
ORDER BY total_revenue DESC;

CREATE VIEW recent_activities AS
SELECT
    i.id,
    i.type,
    i.title,
    i.created_at,
    l.name as lead_name,
    u.name as user_name
FROM interactions i
JOIN leads l ON i.lead_id = l.id
LEFT JOIN users u ON i.user_id = u.id
ORDER BY i.created_at DESC
LIMIT 100;

-- ============================================
-- END OF SCHEMA
-- ============================================
