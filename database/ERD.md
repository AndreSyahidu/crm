# Entity Relationship Diagram (ERD)

## WhatsApp CRM Database Structure

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          USERS & AUTHENTICATION                              │
└─────────────────────────────────────────────────────────────────────────────┘

    ┌──────────────┐
    │    USERS     │
    ├──────────────┤
    │ PK id        │
    │    name      │
    │    email     │
    │    password  │
    │    role      │◄───────┐
    │    avatar    │        │
    │    phone     │        │
    └──────┬───────┘        │
           │                │
           │                │
┌─────────────────────────────────────────────────────────────────────────────┐
│                         LEADS & CUSTOMERS                                    │
└─────────────────────────────────────────────────────────────────────────────┘
           │                │
           │                │
    ┌──────▼───────────────┴─┐         ┌─────────────────┐
    │       LEADS            │         │      TAGS       │
    ├────────────────────────┤         ├─────────────────┤
    │ PK id                  │         │ PK id           │
    │    name                │         │    name         │
    │    phone               │         │    color        │
    │    email               │         └────────┬────────┘
    │    whatsapp_number     │                  │
    │    company             │                  │
    │    status              │         ┌────────▼────────┐
    │    lead_score          │         │   LEAD_TAGS     │
    │    expected_revenue    │◄────────┤ PK lead_id      │
    │    actual_revenue      │         │ PK tag_id       │
    │    lifetime_value      │         └─────────────────┘
    │ FK assigned_to         │
    └──────┬─────────────────┘
           │
           ├─────────────────────┐
           │                     │
┌──────────▼──────────┐   ┌─────▼──────────────┐
│    DEALS            │   │  INTERACTIONS      │
├─────────────────────┤   ├────────────────────┤
│ PK id               │   │ PK id              │
│ FK lead_id          │   │ FK lead_id         │
│    title            │   │ FK user_id         │
│    value            │   │    type            │
│ FK stage_id         │   │    title           │
│    probability      │   │    description     │
│    expected_close   │   │    scheduled_at    │
│ FK assigned_to      │   │    completed_at    │
└─────────────────────┘   └────────────────────┘
           │
           │              ┌─────────────────────┐
           │              │ JOURNEY_MILESTONES  │
           │              ├─────────────────────┤
           │              │ PK id               │
           │              │ FK lead_id          │
           │              │    name             │
           │              │    achieved_at      │
           │              └─────────────────────┘
           │
    ┌──────▼──────────────┐
    │  PIPELINE_STAGES    │
    ├─────────────────────┤
    │ PK id               │
    │    name             │
    │    slug             │
    │    position         │
    │    color            │
    └─────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                        WHATSAPP INTEGRATION                                  │
└─────────────────────────────────────────────────────────────────────────────┘

    ┌─────────────────────┐         ┌──────────────────┐
    │ WHATSAPP_MESSAGES   │         │ WHATSAPP_SESSION │
    ├─────────────────────┤         ├──────────────────┤
    │ PK id               │         │ PK id            │
    │ FK lead_id          │         │    session_name  │
    │    message_id       │         │    phone_number  │
    │    from_number      │         │    status        │
    │    to_number        │         │    qr_code       │
    │    direction        │         │    connected_at  │
    │    content          │         └──────────────────┘
    │    type             │
    │    status           │         ┌──────────────────┐
    │    read_at          │         │  MESSAGE_QUEUE   │
    └─────────────────────┘         ├──────────────────┤
                                    │ PK id            │
                                    │    to_number     │
                                    │    message       │
                                    │    status        │
                                    │    scheduled_at  │
                                    └──────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                        OBJECTION HANDLING                                    │
└─────────────────────────────────────────────────────────────────────────────┘

    ┌─────────────────────┐         ┌──────────────────┐
    │ OBJECTION_TEMPLATES │         │  OBJECTION_LOGS  │
    ├─────────────────────┤         ├──────────────────┤
    │ PK id               │         │ PK id            │
    │    title            │◄────────┤ FK template_id   │
    │    objection        │         │ FK lead_id       │
    │    response         │         │    objection     │
    │    category         │         │    response_sent │
    │    success_rate     │         │    was_successful│
    └─────────────────────┘         └──────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                        FOLLOW-UP AUTOMATION                                  │
└─────────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────────┐
    │ FOLLOW_UP_SEQUENCES  │
    ├──────────────────────┤
    │ PK id                │
    │    name              │
    │    trigger_type      │
    └──────┬───────────────┘
           │
           ├───────────────┐
           │               │
    ┌──────▼─────────┐  ┌─▼────────────────────┐
    │ FOLLOW_UP_STEPS│  │ FOLLOW_UP_ENROLLMENTS│
    ├────────────────┤  ├──────────────────────┤
    │ PK id          │  │ PK id                │
    │ FK sequence_id │  │ FK lead_id           │
    │    step_number │  │ FK sequence_id       │
    │    delay_days  │  │    current_step      │
    │    action_type │  │    status            │
    │    message     │  │    next_action_at    │
    └────────────────┘  └──────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                        BROADCAST CAMPAIGNS                                   │
└─────────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────────┐
    │ BROADCAST_CAMPAIGNS  │
    ├──────────────────────┤
    │ PK id                │
    │    name              │
    │    message           │
    │    segment_filter    │
    │    status            │
    │    total_recipients  │
    │    sent_count        │
    │    delivered_count   │
    │    read_count        │
    └──────┬───────────────┘
           │
    ┌──────▼──────────────┐
    │ BROADCAST_RECIPIENTS│
    ├─────────────────────┤
    │ PK id               │
    │ FK campaign_id      │
    │ FK lead_id          │
    │    status           │
    │    sent_at          │
    │    read_at          │
    └─────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                        TASKS & ANALYTICS                                     │
└─────────────────────────────────────────────────────────────────────────────┘

    ┌──────────────┐      ┌─────────────┐      ┌──────────────┐
    │    TASKS     │      │ DAILY_STATS │      │ ACTIVITY_LOGS│
    ├──────────────┤      ├─────────────┤      ├──────────────┤
    │ PK id        │      │ PK id       │      │ PK id        │
    │ FK lead_id   │      │    date     │      │ FK user_id   │
    │ FK assigned  │      │    new_leads│      │    action    │
    │    title     │      │    won_deals│      │    entity    │
    │    status    │      │    revenue  │      └──────────────┘
    │    due_date  │      └─────────────┘
    └──────────────┘
```

## Key Relationships

1. **Users ← → Leads**: One user can manage many leads (assigned_to)
2. **Leads ← → Deals**: One lead can have many deals
3. **Leads ← → Interactions**: One lead has many interactions (timeline)
4. **Leads ← → WhatsApp Messages**: One lead has many WhatsApp conversations
5. **Leads ← → Tags**: Many-to-many relationship via lead_tags
6. **Deals → Pipeline Stages**: Each deal belongs to one stage
7. **Follow-up Sequences → Steps**: One sequence has many steps
8. **Follow-up Enrollments**: Links leads to sequences with progress tracking
9. **Broadcast Campaigns → Recipients**: One campaign targets many leads
10. **Objection Templates → Logs**: Track usage and success of templates

## Indexes for Performance

All foreign keys are indexed for fast joins. Additional indexes on:
- Lead status, source, created_at for filtering
- WhatsApp message direction, created_at for timeline
- Task due_date, status for reminders
- Broadcast scheduled_at for automation

## JSON Fields

Some fields use JSON for flexible storage:
- `leads.metadata`: Custom fields per lead
- `segments.filter_rules`: Dynamic segment criteria
- `follow_up_sequences.trigger_config`: Flexible trigger conditions
- `broadcast_campaigns.segment_filter`: Complex filtering
- `daily_stats.metrics`: Extended metrics
