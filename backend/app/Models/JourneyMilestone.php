<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JourneyMilestone extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'lead_id',
        'name',
        'description',
        'icon',
        'achieved_at',
        'metadata',
    ];

    protected $casts = [
        'achieved_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
