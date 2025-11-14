# API Documentation

Complete API reference for WhatsApp CRM Backend.

Base URL: `http://your-domain/api`

## Authentication

All endpoints (except login/register) require JWT authentication.

### Headers

```
Authorization: Bearer {your-jwt-token}
Content-Type: application/json
Accept: application/json
```

---

## Auth Endpoints

### POST /auth/register

Register new user.

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "sales_rep"
}
```

**Response:**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "sales_rep"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

### POST /auth/login

Login user.

**Request:**
```json
{
  "email": "admin@example.com",
  "password": "admin123"
}
```

**Response:**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 86400
}
```

### GET /auth/me

Get current user info.

**Response:**
```json
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@example.com",
  "role": "admin",
  "avatar": null,
  "phone": null
}
```

### POST /auth/logout

Logout current user.

**Response:**
```json
{
  "message": "Successfully logged out"
}
```

---

## Leads Endpoints

### GET /leads

Get all leads with filters and pagination.

**Query Parameters:**
- `status` - Filter by status (new, contacted, qualified, etc.)
- `source` - Filter by source (whatsapp, manual, website, etc.)
- `assigned_to` - Filter by assigned user ID
- `tag` - Filter by tag name
- `search` - Search in name, email, phone, company
- `sort_by` - Sort field (default: created_at)
- `sort_dir` - Sort direction (asc/desc, default: desc)
- `per_page` - Items per page (default: 15)

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "phone": "628123456789",
      "email": "john@example.com",
      "whatsapp_number": "628123456789",
      "company": "ABC Corp",
      "status": "new",
      "lead_score": 45,
      "expected_revenue": 10000000,
      "assigned_user": {
        "id": 1,
        "name": "Admin User"
      },
      "tags": [
        {"id": 1, "name": "Hot Lead", "color": "#ef4444"}
      ]
    }
  ],
  "total": 100,
  "per_page": 15,
  "last_page": 7
}
```

### POST /leads

Create new lead.

**Request:**
```json
{
  "name": "Jane Smith",
  "phone": "628987654321",
  "email": "jane@example.com",
  "whatsapp_number": "628987654321",
  "company": "XYZ Inc",
  "position": "CEO",
  "source": "whatsapp",
  "status": "new",
  "expected_revenue": 15000000,
  "assigned_to": 1,
  "notes": "Interested in our premium package",
  "tags": [1, 2]
}
```

**Response:**
```json
{
  "message": "Lead created successfully",
  "lead": {
    "id": 2,
    "name": "Jane Smith",
    ...
  }
}
```

### GET /leads/{id}

Get lead details with all related data.

**Response:**
```json
{
  "id": 1,
  "name": "John Doe",
  "phone": "628123456789",
  "deals": [...],
  "interactions": [...],
  "whatsapp_messages": [...],
  "tasks": [...],
  "journey_milestones": [...],
  "follow_up_enrollments": [...]
}
```

### PUT /leads/{id}

Update lead.

**Request:**
```json
{
  "name": "John Updated",
  "status": "qualified",
  "expected_revenue": 20000000,
  "tags": [1, 3]
}
```

### DELETE /leads/{id}

Delete lead.

**Response:**
```json
{
  "message": "Lead deleted successfully"
}
```

### GET /leads/{id}/timeline

Get lead timeline (interactions, messages, milestones).

**Response:**
```json
[
  {
    "type": "whatsapp",
    "data": {
      "id": 1,
      "content": "Hello, interested in your product",
      "direction": "inbound"
    },
    "timestamp": "2024-01-15T10:30:00Z"
  },
  {
    "type": "interaction",
    "data": {
      "id": 1,
      "type": "call",
      "title": "Follow-up call"
    },
    "timestamp": "2024-01-15T14:00:00Z"
  }
]
```

### POST /leads/bulk-assign

Bulk assign leads to user.

**Request:**
```json
{
  "lead_ids": [1, 2, 3],
  "assigned_to": 2
}
```

---

## Pipeline Endpoints

### GET /deals/kanban

Get Kanban board view.

**Response:**
```json
{
  "stages": [
    {
      "id": 1,
      "name": "New Lead",
      "slug": "new-lead",
      "color": "#3b82f6",
      "deals": [
        {
          "id": 1,
          "title": "ABC Corp - Premium Package",
          "value": 10000000,
          "lead": {...},
          "assigned_user": {...}
        }
      ]
    }
  ],
  "stats": {
    "total_value": 50000000,
    "weighted_value": 25000000,
    "total_deals": 10,
    "closing_soon": 3
  }
}
```

### POST /deals

Create new deal.

**Request:**
```json
{
  "lead_id": 1,
  "title": "Premium Package Deal",
  "value": 10000000,
  "stage_id": 1,
  "probability": 50,
  "expected_close_date": "2024-02-01",
  "priority": "high",
  "assigned_to": 1
}
```

### POST /deals/{id}/move-stage

Move deal to different stage.

**Request:**
```json
{
  "stage_id": 2,
  "position": 0
}
```

---

## WhatsApp Endpoints

### GET /whatsapp/status

Get WhatsApp connection status.

**Response:**
```json
{
  "success": true,
  "status": "connected",
  "client_info": {
    "phone": "628123456789",
    "platform": "android",
    "pushname": "My Business"
  }
}
```

### GET /whatsapp/qr

Get QR code for WhatsApp connection.

**Response:**
```json
{
  "success": true,
  "qr_code": "data:image/png;base64,iVBORw0KGg...",
  "status": "qr_ready"
}
```

### POST /whatsapp/send

Send WhatsApp message.

**Request:**
```json
{
  "lead_id": 1,
  "message": "Hello! Thank you for your interest.",
  "media_url": "https://example.com/image.jpg",
  "queue": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "message_id": "3EB0...",
    "timestamp": 1642234567
  }
}
```

### GET /whatsapp/leads/{id}/messages

Get WhatsApp messages for a lead.

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "message_id": "3EB0...",
      "from_number": "628123456789",
      "to_number": "628987654321",
      "direction": "inbound",
      "content": "Hello, interested in your product",
      "status": "read",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

---

## Broadcast Endpoints

### GET /broadcasts

Get all broadcast campaigns.

### POST /broadcasts

Create broadcast campaign.

**Request:**
```json
{
  "name": "New Year Promo",
  "message": "Happy New Year! Special discount 20% for all products.",
  "media_url": null,
  "segment_filter": {
    "status": "qualified"
  },
  "scheduled_at": "2024-01-01T09:00:00Z",
  "lead_ids": [1, 2, 3]
}
```

### POST /broadcasts/{id}/start

Start sending broadcast campaign.

**Response:**
```json
{
  "message": "Campaign started successfully",
  "campaign": {
    "id": 1,
    "status": "sending",
    "total_recipients": 100
  }
}
```

### GET /broadcasts/{id}/stats

Get broadcast campaign statistics.

**Response:**
```json
{
  "total_recipients": 100,
  "sent_count": 100,
  "delivered_count": 95,
  "read_count": 75,
  "reply_count": 30,
  "sent_rate": 100,
  "delivery_rate": 95,
  "read_rate": 78.95,
  "reply_rate": 31.58
}
```

---

## Analytics Endpoints

### GET /analytics/dashboard

Get dashboard statistics.

**Response:**
```json
{
  "total_leads": 500,
  "total_customers": 150,
  "active_deals": 50,
  "new_leads_this_month": 75,
  "won_deals_this_month": 25,
  "revenue_this_month": 250000000,
  "pipeline_value": 500000000,
  "conversion_rate": 30.5,
  "whatsapp_messages_today": 120,
  "leads_need_follow_up": 15,
  "deals_closing_soon": 8
}
```

### GET /analytics/conversion-funnel

Get conversion funnel data.

**Response:**
```json
[
  {"stage": "New Leads", "count": 500},
  {"stage": "Contacted", "count": 400},
  {"stage": "Qualified", "count": 250},
  {"stage": "Proposal", "count": 150},
  {"stage": "Negotiation", "count": 80},
  {"stage": "Won", "count": 50}
]
```

### GET /analytics/revenue-over-time

Get revenue over time.

**Query Parameters:**
- `days` - Number of days (default: 30)

**Response:**
```json
[
  {
    "date": "2024-01-15",
    "revenue": 15000000,
    "count": 3
  }
]
```

### GET /analytics/team-performance

Get team performance metrics.

**Response:**
```json
[
  {
    "id": 1,
    "name": "John Doe",
    "total_leads": 50,
    "won_deals": 15,
    "total_revenue": 150000000,
    "conversion_rate": 30.0,
    "average_deal_value": 10000000
  }
]
```

---

## Error Responses

### 400 Bad Request
```json
{
  "error": "Invalid request"
}
```

### 401 Unauthorized
```json
{
  "error": "Unauthenticated"
}
```

### 422 Validation Error
```json
{
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 6 characters."]
  }
}
```

### 500 Server Error
```json
{
  "error": "Internal server error"
}
```

---

## Rate Limiting

API requests are rate limited:
- Default: 60 requests per minute per user
- WhatsApp messages: 30 per minute (configurable)

Rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1642234567
```

---

## Webhooks

You can configure webhooks for real-time events:

**POST /webhooks/whatsapp**

Receives WhatsApp message events from service.

**Payload:**
```json
{
  "event": "message_received",
  "data": {
    "message_id": "3EB0...",
    "from": "628123456789",
    "content": "Hello"
  }
}
```

---

For more details, see [User Guide](USER_GUIDE.md) or contact support.
