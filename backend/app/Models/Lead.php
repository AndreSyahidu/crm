<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'whatsapp_number',
        'company',
        'position',
        'source',
        'status',
        'lead_score',
        'expected_revenue',
        'actual_revenue',
        'lifetime_value',
        'acquisition_cost',
        'assigned_to',
        'last_contact_at',
        'converted_at',
        'lost_reason',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'lead_score' => 'integer',
        'expected_revenue' => 'decimal:2',
        'actual_revenue' => 'decimal:2',
        'lifetime_value' => 'decimal:2',
        'acquisition_cost' => 'decimal:2',
        'last_contact_at' => 'datetime',
        'converted_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    public function whatsappMessages()
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'lead_tags');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function journeyMilestones()
    {
        return $this->hasMany(JourneyMilestone::class);
    }

    public function objectionLogs()
    {
        return $this->hasMany(ObjectionLog::class);
    }

    public function followUpEnrollments()
    {
        return $this->hasMany(FollowUpEnrollment::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeHighValue($query, $threshold = 10000)
    {
        return $query->where('expected_revenue', '>=', $threshold);
    }

    public function scopeNeedsFollowUp($query, $days = 7)
    {
        return $query->where('last_contact_at', '<=', now()->subDays($days))
            ->whereNotIn('status', ['won', 'lost']);
    }

    // Helpers
    public function calculateROI()
    {
        if ($this->acquisition_cost > 0) {
            return (($this->actual_revenue - $this->acquisition_cost) / $this->acquisition_cost) * 100;
        }
        return 0;
    }

    public function updateLeadScore()
    {
        $score = 0;

        // Score based on interactions
        $score += $this->interactions()->count() * 5;

        // Score based on message replies
        $score += $this->whatsappMessages()->where('direction', 'inbound')->count() * 3;

        // Score based on deal value
        if ($this->expected_revenue > 0) {
            $score += min($this->expected_revenue / 1000, 50);
        }

        // Score based on recent activity
        if ($this->last_contact_at && $this->last_contact_at->diffInDays(now()) < 7) {
            $score += 20;
        }

        $this->lead_score = min($score, 100);
        $this->save();

        return $this->lead_score;
    }

    public function markAsWon($revenue)
    {
        $this->status = 'won';
        $this->actual_revenue = $revenue;
        $this->converted_at = now();
        $this->save();
    }

    public function markAsLost($reason)
    {
        $this->status = 'lost';
        $this->lost_reason = $reason;
        $this->save();
    }
}
