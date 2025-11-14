# WhatsApp CRM System

Sistem CRM lengkap dengan integrasi WhatsApp untuk mengelola leads, sales pipeline, dan customer journey.

## Fitur Utama

### 1. Integrasi WhatsApp
- ✅ Koneksi via WhatsApp Web (scan QR code)
- ✅ Auto-capture leads dari pesan masuk
- ✅ Kirim/terima pesan untuk follow-up
- ✅ Broadcast messaging
- ✅ Support WhatsApp Personal & Business

### 2. Lead Management
- ✅ Auto-capture dari WhatsApp
- ✅ Manual entry via dashboard
- ✅ Lead scoring berdasarkan engagement
- ✅ Timeline interaksi lengkap

### 3. Sales Pipeline
- ✅ 5 Stage: New Lead → Qualified → Proposal → Negotiation → Won/Lost
- ✅ Kanban board (drag & drop)
- ✅ Auto-move berdasarkan trigger

### 4. Customer Journey Tracking
- ✅ Timeline view semua interaksi
- ✅ Visual journey map dengan milestones
- ✅ History lengkap per customer

### 5. Value Calculation
- ✅ Deal value tracking
- ✅ Lifetime Value (LTV)
- ✅ ROI per lead
- ✅ Automated reports

### 6. Daily Recap
- ✅ Email harian otomatis (via cron)
- ✅ Summary leads baru & konversi
- ✅ Dashboard dengan charts

### 7. Objection Handling
- ✅ Template untuk objection umum
- ✅ AI-suggested responses
- ✅ Quick reply via WhatsApp

### 8. Auto Follow-Up
- ✅ Rule-based automation
- ✅ Customizable sequences
- ✅ WhatsApp reminders otomatis

### 9. Broadcast & Segmentation
- ✅ Bulk WhatsApp messaging
- ✅ Tag-based segments
- ✅ Dynamic lists
- ✅ Track open/reply rates

### 10. Analytics Dashboard
- ✅ Conversion rates
- ✅ Lead sources
- ✅ Revenue tracking
- ✅ Interactive charts

## Tech Stack

- **Backend**: PHP 8+ with Laravel 10
- **Database**: MySQL 8+
- **WhatsApp**: Node.js + whatsapp-web.js
- **Frontend**: Bootstrap 5 + Vue.js 3
- **Charts**: Chart.js
- **Auth**: JWT

## Struktur Direktori

```
crm/
├── backend/              # Laravel API
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   └── ...
├── whatsapp-service/     # Node.js WhatsApp bot
│   ├── src/
│   ├── package.json
│   └── ...
├── frontend/             # Vue.js SPA
│   ├── src/
│   ├── public/
│   └── ...
├── database/             # SQL schemas
├── docs/                 # Documentation
└── deployment/           # cPanel configs
```

## Quick Start

### 1. Database Setup
```bash
mysql -u root -p < database/schema.sql
```

### 2. Backend Setup
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### 3. WhatsApp Service Setup
```bash
cd whatsapp-service
npm install
npm start
# Scan QR code yang muncul
```

### 4. Frontend Setup
```bash
cd frontend
npm install
npm run dev
```

### 5. Setup Cron Jobs
```bash
# Add to crontab
* * * * * cd /path/to/crm/backend && php artisan schedule:run >> /dev/null 2>&1
```

## Deployment ke cPanel

Lihat panduan lengkap di [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)

## Keamanan

- ✅ JWT Authentication
- ✅ Input sanitization
- ✅ Rate limiting untuk WhatsApp
- ✅ CORS protection
- ✅ SQL injection prevention

## Lisensi

Open Source - MIT License

## Support

Untuk bantuan dan pertanyaan, silakan buka issue di repository ini.
