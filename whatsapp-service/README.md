# WhatsApp CRM Service

Node.js service untuk integrasi WhatsApp Web dengan CRM system menggunakan whatsapp-web.js.

## Features

- ✅ Koneksi via WhatsApp Web (QR Code)
- ✅ Auto-capture leads dari pesan masuk
- ✅ Kirim/terima pesan
- ✅ Message queue dengan retry mechanism
- ✅ Rate limiting (30 pesan/menit)
- ✅ Broadcast messaging
- ✅ Auto-response untuk leads baru
- ✅ Objection detection
- ✅ Media support (images, videos, documents)
- ✅ Chat history retrieval

## Prerequisites

- Node.js 16+
- MySQL 8+
- Chrome/Chromium (untuk Puppeteer)

## Installation

```bash
# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Edit .env dengan konfigurasi Anda
nano .env
```

## Configuration

Edit file `.env`:

```env
PORT=3000
SESSION_NAME=default

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=whatsapp_crm
DB_USERNAME=root
DB_PASSWORD=your_password

BACKEND_API_URL=http://localhost:8000/api
WHATSAPP_RATE_LIMIT=30
```

## Usage

### Development Mode
```bash
npm run dev
```

### Production Mode
```bash
npm start
```

### Scan QR Code

1. Start the service
2. Check terminal untuk QR code, atau
3. Visit `http://localhost:3000/qr` untuk mendapatkan QR code dalam format data URL

## API Endpoints

### GET /health
Health check endpoint
```bash
curl http://localhost:3000/health
```

### GET /qr
Dapatkan QR code untuk scan
```bash
curl http://localhost:3000/qr
```

Response:
```json
{
  "success": true,
  "qr_code": "data:image/png;base64,...",
  "status": "qr_ready"
}
```

### GET /status
Dapatkan status koneksi WhatsApp
```bash
curl http://localhost:3000/status
```

### POST /send-message
Kirim pesan WhatsApp

```bash
curl -X POST http://localhost:3000/send-message \
  -H "Content-Type: application/json" \
  -d '{
    "to": "628123456789",
    "message": "Hello from CRM!",
    "media_url": "https://example.com/image.jpg"
  }'
```

### POST /send-broadcast
Kirim broadcast ke multiple recipients

```bash
curl -X POST http://localhost:3000/send-broadcast \
  -H "Content-Type: application/json" \
  -d '{
    "recipients": ["628123456789", "628987654321"],
    "message": "Broadcast message",
    "media_url": null
  }'
```

### GET /chats/:number
Dapatkan chat history

```bash
curl http://localhost:3000/chats/628123456789?limit=50
```

### POST /disconnect
Disconnect WhatsApp session

```bash
curl -X POST http://localhost:3000/disconnect
```

### POST /reconnect
Reconnect WhatsApp

```bash
curl -X POST http://localhost:3000/reconnect
```

## How It Works

### 1. Message Flow

**Incoming Messages:**
```
WhatsApp → whatsapp-web.js → MessageProcessor → Database
                                    ↓
                              Create/Update Lead
                                    ↓
                              Create Interaction
                                    ↓
                              Detect Objections
```

**Outgoing Messages:**
```
CRM/API → Message Queue → MessageQueueWorker → WhatsApp
            (Database)          (Every 5s)
```

### 2. Auto Lead Creation

Ketika pesan masuk dari nomor baru:
1. Check database untuk lead dengan nomor tersebut
2. Jika tidak ada, create lead baru dengan source "whatsapp"
3. Save pesan ke `whatsapp_messages` table
4. Create interaction record
5. Create journey milestone "First Contact"

### 3. Message Queue

Messages yang belum terkirim disimpan di `message_queue` table:
- Worker process queue setiap 5 detik
- Retry up to 3x jika gagal
- Respect rate limit (30 msg/min)
- Priority-based processing

### 4. Rate Limiting

Service otomatis limit 30 pesan per menit untuk menghindari WhatsApp ban:
- Track timestamps dari semua pesan terkirim
- Queue messages jika limit tercapai
- Automatic delay between broadcast messages

## Database Integration

Service ini terhubung langsung ke MySQL database dan mengelola:

- `whatsapp_sessions` - Session status dan QR codes
- `whatsapp_messages` - Semua pesan masuk/keluar
- `message_queue` - Antrian pesan yang akan dikirim
- `leads` - Auto-create leads dari pesan masuk
- `interactions` - Timeline interaksi customer

## Deployment

### Standalone Server
```bash
# Install PM2
npm install -g pm2

# Start with PM2
pm2 start src/index.js --name whatsapp-crm

# Save PM2 config
pm2 save
pm2 startup
```

### With Backend
Service ini berjalan terpisah dari Laravel backend, biasanya di server yang sama atau terpisah.

## Troubleshooting

### QR Code tidak muncul
- Check Chrome/Chromium terinstall
- Check file permissions di `.wwebjs_auth` folder
- Restart service

### Connection failed
- Check database credentials di `.env`
- Ensure MySQL running
- Check firewall settings

### Messages tidak terkirim
- Check WhatsApp connection status: `/status`
- Check message queue: `SELECT * FROM message_queue WHERE status='failed'`
- Check rate limit

### "Session Error"
- Delete `.wwebjs_auth` folder
- Restart dan scan QR code lagi

## Production Considerations

1. **Resource Usage**: Service menggunakan Puppeteer (Chrome headless), butuh minimal 512MB RAM
2. **Network**: Pastikan koneksi internet stabil
3. **Backup Session**: Backup folder `.wwebjs_auth` untuk menghindari re-scan QR
4. **Monitoring**: Gunakan PM2 atau supervisord untuk auto-restart
5. **Rate Limits**: Respect WhatsApp rate limits untuk menghindari ban

## Security

- Jangan expose port 3000 ke public
- Gunakan reverse proxy (nginx) dengan authentication
- Simpan `.env` dengan secure permissions (600)
- Regular backup session files
- Monitor untuk suspicious activity

## License

MIT
