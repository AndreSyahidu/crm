# User Guide

Panduan lengkap penggunaan WhatsApp CRM System.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard](#dashboard)
3. [Lead Management](#lead-management)
4. [Sales Pipeline](#sales-pipeline)
5. [WhatsApp Integration](#whatsapp-integration)
6. [Broadcast Campaigns](#broadcast-campaigns)
7. [Analytics & Reports](#analytics--reports)
8. [Best Practices](#best-practices)

---

## Getting Started

### First Login

1. Akses URL CRM Anda di browser
2. Login dengan credentials default:
   - Email: `admin@example.com`
   - Password: `admin123`
3. **PENTING**: Segera ubah password di Settings > Profile

### WhatsApp Setup

1. Pastikan WhatsApp service sudah running
2. Buka menu **WhatsApp** di sidebar
3. Klik "Scan QR Code"
4. Buka WhatsApp di HP > Settings > Linked Devices > Link a Device
5. Scan QR code yang muncul
6. Tunggu hingga status berubah menjadi "Connected"

✅ WhatsApp Anda sekarang terhubung dengan CRM!

---

## Dashboard

Dashboard menampilkan overview lengkap bisnis Anda.

### Key Metrics

**Total Leads**
- Jumlah total leads di sistem
- Leads baru bulan ini
- Trend growth

**Active Deals**
- Deals yang sedang berjalan
- Total nilai pipeline
- Estimasi closing

**Revenue**
- Revenue bulan ini
- Jumlah deals won
- Comparison dengan bulan lalu

**Conversion Rate**
- Persentase leads yang menjadi customer
- Total customers

### Quick Actions

- **Needs Follow-up**: Leads yang perlu di-follow-up (7+ hari tidak ada kontak)
- **Closing Soon**: Deals dengan expected close date < 30 hari
- **Unread Messages**: Pesan WhatsApp yang belum dibaca

### Charts

- **Leads Over Time**: Trend leads masuk per hari/minggu/bulan
- **Leads by Source**: Breakdown leads berdasarkan sumber (WhatsApp, Manual, Website, dll)
- **Revenue Trend**: Trend revenue over time

---

## Lead Management

### Creating Leads

**Manual Entry:**
1. Klik **Leads** di sidebar
2. Klik tombol "+ New Lead"
3. Isi form:
   - Name (required)
   - Phone (required)
   - Email
   - Company
   - Position
   - Expected Revenue
   - Assign To
   - Tags
4. Klik "Save"

**Auto-capture dari WhatsApp:**
- Leads otomatis dibuat ketika ada pesan WhatsApp masuk dari nomor baru
- Akan muncul di status "New" dengan source "WhatsApp"

### Lead Details

Klik pada lead untuk melihat detail lengkap:

**Overview Tab:**
- Informasi dasar (nama, kontak, company)
- Lead score (0-100, auto-calculated)
- Expected vs Actual revenue
- Status & source
- Tags

**Timeline Tab:**
- Semua interaksi chronologically
- WhatsApp messages
- Calls, meetings, notes
- Journey milestones
- Deal updates

**Deals Tab:**
- All deals terkait lead ini
- Stage, value, probability

**Tasks Tab:**
- Pending tasks
- Completed tasks
- Overdue tasks

### Lead Statuses

```
New → Contacted → Qualified → Proposal → Negotiation → Won/Lost
```

**New**: Lead baru masuk
**Contacted**: Sudah ada kontak pertama
**Qualified**: Lead qualified (punya budget, authority, need)
**Proposal**: Proposal sudah dikirim
**Negotiation**: Dalam proses negosiasi
**Won**: Deal berhasil!
**Lost**: Deal tidak berhasil

### Lead Scoring

Lead score dihitung otomatis berdasarkan:
- Jumlah interaksi (+5 per interaction)
- Reply rate WhatsApp (+3 per reply)
- Deal value (+1 per 1000 currency)
- Recent activity (+20 jika < 7 hari)

Score range: 0-100

### Bulk Actions

Select multiple leads untuk:
- Bulk assign to user
- Bulk add tags
- Bulk export

---

## Sales Pipeline

Pipeline view menggunakan Kanban board.

### Stages

Default stages:
1. **New Lead** (biru)
2. **Qualified** (ungu)
3. **Proposal** (kuning)
4. **Negotiation** (pink)
5. **Won** (hijau)
6. **Lost** (merah)

### Creating Deals

1. Klik "+ New Deal" di stage yang diinginkan
2. Isi form:
   - Select Lead (required)
   - Title
   - Value (required)
   - Probability (%)
   - Expected Close Date
   - Priority
   - Assign To
3. Klik "Create"

### Moving Deals

**Drag & Drop:**
- Drag deal card ke stage lain
- Otomatis tercatat di interaction log

**Manual:**
- Klik deal card
- Pilih "Move to Stage"
- Select stage baru

### Deal Priority

- **Low** (abu-abu)
- **Medium** (biru) - default
- **High** (kuning)
- **Urgent** (merah)

### Pipeline Metrics

- **Total Value**: Sum semua deal values
- **Weighted Value**: Sum (value × probability/100)
- **Average Deal Size**: Total value / jumlah deals
- **Win Rate**: Won deals / Total deals

---

## WhatsApp Integration

### Messaging

**Send Individual Message:**
1. Buka Lead detail
2. Klik tab "WhatsApp"
3. Type message di chat box
4. (Optional) Attach media
5. Click "Send"

**Quick Replies:**
- Pre-built message templates
- Click template untuk auto-fill
- Edit sebelum send

### Auto-Response

Enable di Settings > WhatsApp:
- Auto-response untuk first message
- Customize message template
- Set business hours

### Message Status

- ✓ Pending
- ✓✓ Sent
- ✓✓ Delivered (biru)
- ✓✓ Read (biru)

### Objection Handling

System auto-detect common objections:
- "Mahal" / "Expensive" → Pricing objection
- "Pikir-pikir" / "Need to think" → Timing objection
- "Vendor lain" → Competition objection

Akan muncul suggested response dari template.

### Chat Organization

**Filters:**
- Unread messages
- By lead status
- By assigned user
- By tags

**Search:**
- Search across all messages
- Filter by date range

---

## Broadcast Campaigns

Send bulk WhatsApp messages.

### Creating Campaign

1. Click **Broadcasts** di sidebar
2. Click "+ New Campaign"
3. Fill form:
   - Campaign Name
   - Message
   - Media (optional)
   - Schedule (optional)
4. Select Recipients:
   - **Option A**: Manual select leads
   - **Option B**: Use segment filters
5. Preview recipients
6. Click "Create"

### Segment Filters

Filter leads berdasarkan:
- Status
- Source
- Tags
- Lead score
- Revenue range
- Last contact date

Example: "All qualified leads with tag 'Hot Lead' yang belum di-follow-up > 7 hari"

### Campaign Scheduling

- **Send Now**: Kirim immediately
- **Schedule**: Pilih tanggal & waktu specific

### Monitoring Campaign

View real-time stats:
- Total recipients
- Sent count
- Delivered count
- Read count
- Reply count

Rates:
- Delivery rate: delivered/sent × 100%
- Read rate: read/delivered × 100%
- Reply rate: replied/delivered × 100%

### Best Practices

✅ **DO:**
- Segment your audience
- Personalize messages (use {name}, {company})
- Send during business hours
- Test with small group first
- Track performance

❌ **DON'T:**
- Send spam
- Send too frequently (max 1x/week per lead)
- Use ALL CAPS
- Send to unqualified leads

---

## Analytics & Reports

### Dashboard Analytics

Real-time metrics updated setiap 5 menit.

### Conversion Funnel

Visual funnel showing:
- New leads count
- Each stage count
- Drop-off rate per stage

Identify bottlenecks!

### Revenue Reports

- Revenue over time (daily/weekly/monthly)
- Revenue by product/service
- Revenue by sales rep
- Forecast vs actual

### Team Performance

Compare team members:
- Total leads handled
- Won deals
- Total revenue
- Conversion rate
- Average deal value

### WhatsApp Analytics

- Messages sent/received over time
- Response time average
- Peak activity hours
- Top contacts

### Broadcast Performance

Compare campaigns:
- Delivery rates
- Read rates
- Reply rates
- ROI per campaign

### Exporting Data

Export reports as:
- CSV
- Excel
- PDF (coming soon)

---

## Best Practices

### Lead Management

1. **Respond Quickly**: Reply WhatsApp dalam < 5 menit
2. **Update Regularly**: Update lead status setelah setiap interaksi
3. **Add Notes**: Catat detail penting di notes
4. **Use Tags**: Categorize leads untuk easy filtering
5. **Set Tasks**: Jangan lupa follow-up, set reminders

### Pipeline Management

1. **Keep Moving**: Jangan biarkan deals stuck di satu stage > 14 hari
2. **Update Probability**: Adjust probability sesuai progress
3. **Set Realistic Dates**: Expected close date harus realistic
4. **Review Weekly**: Review pipeline setiap minggu, identify blockers

### WhatsApp Communication

1. **Be Professional**: Meskipun WhatsApp, tetap professional
2. **Quick Responses**: Average response time < 1 jam
3. **Personalize**: Use nama, reference previous conversations
4. **Clear CTAs**: Setiap message punya clear next step
5. **Follow-up**: Jangan takut follow-up 2-3x

### Automation

1. **Auto Follow-up**: Setup sequence untuk leads baru
2. **Objection Templates**: Prepare response untuk common objections
3. **Broadcast Wisely**: Segment properly, jangan spam
4. **Daily Recap**: Review daily recap email setiap pagi

### Team Collaboration

1. **Assign Clearly**: Setiap lead harus ada owner
2. **Handoff Notes**: Catat context saat handoff lead
3. **Share Wins**: Celebrate di team ketika close deal
4. **Review Performance**: Weekly team performance review

---

## Troubleshooting

### WhatsApp Disconnected

1. Check WhatsApp service status
2. Restart service: `pm2 restart whatsapp-crm`
3. Re-scan QR code if needed
4. Contact support if issue persists

### Lead Not Showing

1. Check filters (clear all filters)
2. Check assigned user filter
3. Check if lead deleted (check trash)

### Broadcast Not Sending

1. Check WhatsApp connection status
2. Check message queue: `/whatsapp/status`
3. Check rate limit not exceeded
4. Verify recipient numbers valid

### Missing Data

1. Clear browser cache
2. Hard refresh (Ctrl+F5)
3. Check API connection
4. Contact support

---

## Keyboard Shortcuts

- `Ctrl/Cmd + K`: Quick search
- `Ctrl/Cmd + N`: New lead
- `Ctrl/Cmd + S`: Save form
- `Esc`: Close modal

---

## Support

Need help?
- Documentation: `/docs`
- Video Tutorials: [YouTube channel]
- Email: support@example.com
- Live Chat: [if available]

---

**Happy CRM-ing! 🚀**
