<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'title',
        'value',
        'stage_id',
        'probability',
        'expected_close_date',
        'actual_close_date',
        'priority',
        'position_in_stage',
        'assigned_to',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
        'position_in_stage' => 'integer',
    ];

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function stage()
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Scopes
    public function scopeByStage($query, $stageId)
    {
        return $query->where('stage_id', $stageId);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    public function scopeClosingSoon($query, $days = 30)
    {
        return $query->where('expected_close_date', '<=', now()->addDays($days))
            ->whereNull('actual_close_date');
    }

    // Helpers
    public function moveToStage($stageId)
    {
        $this->stage_id = $stageId;
        $this->save();

        // Log interaction
        Interaction::create([
            'lead_id' => $this->lead_id,
            'type' => 'deal_update',
            'title' => 'Deal moved to ' . $this->stage->name,
            'description' => "Deal '{$this->title}' moved to stage {$this->stage->name}",
        ]);
    }

    public function calculateWeightedValue()
    {
        return $this->value * ($this->probability / 100);
    }
}
