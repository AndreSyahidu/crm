<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BroadcastCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'message',
        'media_url',
        'media_type',
        'segment_filter',
        'scheduled_at',
        'started_at',
        'completed_at',
        'status',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'read_count',
        'reply_count',
        'created_by',
    ];

    protected $casts = [
        'segment_filter' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'delivered_count' => 'integer',
        'read_count' => 'integer',
        'reply_count' => 'integer',
    ];

    // Relationships
    public function recipients()
    {
        return $this->hasMany(BroadcastRecipient::class, 'campaign_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDueToSend($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }

    // Helpers
    public function getTargetLeads()
    {
        $query = Lead::query();

        if ($this->segment_filter && is_array($this->segment_filter)) {
            foreach ($this->segment_filter as $key => $value) {
                $query->where($key, $value);
            }
        }

        return $query->get();
    }

    public function start()
    {
        $this->status = 'sending';
        $this->started_at = now();
        $this->save();

        // Create recipient records
        $leads = $this->getTargetLeads();
        $this->total_recipients = $leads->count();

        foreach ($leads as $lead) {
            BroadcastRecipient::create([
                'campaign_id' => $this->id,
                'lead_id' => $lead->id,
                'status' => 'pending',
            ]);
        }

        $this->save();
    }

    public function sendToRecipients()
    {
        $pending = $this->recipients()->where('status', 'pending')->get();

        foreach ($pending as $recipient) {
            $recipient->send($this->message, $this->media_url);
        }
    }

    public function updateStats()
    {
        $this->sent_count = $this->recipients()->whereIn('status', ['sent', 'delivered', 'read', 'replied'])->count();
        $this->delivered_count = $this->recipients()->whereIn('status', ['delivered', 'read', 'replied'])->count();
        $this->read_count = $this->recipients()->whereIn('status', ['read', 'replied'])->count();
        $this->reply_count = $this->recipients()->where('status', 'replied')->count();

        if ($this->sent_count >= $this->total_recipients) {
            $this->status = 'completed';
            $this->completed_at = now();
        }

        $this->save();
    }
}
