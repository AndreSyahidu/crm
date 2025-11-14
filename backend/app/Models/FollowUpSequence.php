<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUpSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'trigger_type',
        'trigger_config',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function steps()
    {
        return $this->hasMany(FollowUpStep::class, 'sequence_id');
    }

    public function enrollments()
    {
        return $this->hasMany(FollowUpEnrollment::class, 'sequence_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function enrollLead(Lead $lead)
    {
        return FollowUpEnrollment::create([
            'lead_id' => $lead->id,
            'sequence_id' => $this->id,
            'status' => 'active',
            'next_action_at' => now(),
        ]);
    }
}
