<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BroadcastRecipient extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'lead_id',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',
        'replied_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    // Relationships
    public function campaign()
    {
        return $this->belongsTo(BroadcastCampaign::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    // Helpers
    public function send($message, $mediaUrl = null)
    {
        try {
            MessageQueue::create([
                'to_number' => $this->lead->whatsapp_number ?? $this->lead->phone,
                'message' => $message,
                'media_url' => $mediaUrl,
                'priority' => 3,
            ]);

            $this->status = 'sent';
            $this->sent_at = now();
            $this->save();

            return true;
        } catch (\Exception $e) {
            $this->status = 'failed';
            $this->error_message = $e->getMessage();
            $this->save();

            return false;
        }
    }
}
