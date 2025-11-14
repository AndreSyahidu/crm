<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'message_id',
        'from_number',
        'to_number',
        'direction',
        'type',
        'content',
        'media_url',
        'media_mime_type',
        'status',
        'is_from_broadcast',
        'broadcast_id',
        'read_at',
        'delivered_at',
        'sent_at',
        'metadata',
    ];

    protected $casts = [
        'is_from_broadcast' => 'boolean',
        'read_at' => 'datetime',
        'delivered_at' => 'datetime',
        'sent_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function broadcast()
    {
        return $this->belongsTo(BroadcastCampaign::class, 'broadcast_id');
    }

    // Scopes
    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at')
            ->where('direction', 'inbound');
    }

    public function scopeFromNumber($query, $number)
    {
        return $query->where('from_number', $number);
    }

    // Helpers
    public function markAsRead()
    {
        $this->read_at = now();
        $this->status = 'read';
        $this->save();
    }

    public function markAsDelivered()
    {
        $this->delivered_at = now();
        $this->status = 'delivered';
        $this->save();
    }
}
