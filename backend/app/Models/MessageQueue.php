<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageQueue extends Model
{
    use HasFactory;

    protected $table = 'message_queue';

    protected $fillable = [
        'to_number',
        'message',
        'media_url',
        'priority',
        'status',
        'attempts',
        'max_attempts',
        'error_message',
        'scheduled_at',
        'sent_at',
    ];

    protected $casts = [
        'priority' => 'integer',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where('attempts', '<', 'max_attempts')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            });
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc');
    }

    // Helpers
    public function markAsSent()
    {
        $this->status = 'sent';
        $this->sent_at = now();
        $this->save();
    }

    public function markAsFailed($error)
    {
        $this->status = 'failed';
        $this->error_message = $error;
        $this->save();
    }

    public function incrementAttempts()
    {
        $this->attempts++;
        if ($this->attempts >= $this->max_attempts) {
            $this->status = 'failed';
        }
        $this->save();
    }
}
