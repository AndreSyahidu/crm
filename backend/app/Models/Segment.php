<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'filter_rules',
        'is_dynamic',
        'cached_count',
        'last_calculated_at',
        'created_by',
    ];

    protected $casts = [
        'filter_rules' => 'array',
        'is_dynamic' => 'boolean',
        'cached_count' => 'integer',
        'last_calculated_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'segment_leads');
    }

    // Helpers
    public function getLeads()
    {
        $service = new \App\Services\SegmentService();
        return $service->applyFilters($this->filter_rules);
    }

    public function syncLeads()
    {
        if (!$this->is_dynamic) {
            return;
        }

        $leadIds = $this->getLeads()->pluck('id')->toArray();
        $this->leads()->sync($leadIds);
        $this->cached_count = count($leadIds);
        $this->last_calculated_at = now();
        $this->save();
    }
}
