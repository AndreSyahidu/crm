<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipelineStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'position',
        'color',
        'is_active',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function deals()
    {
        return $this->hasMany(Deal::class, 'stage_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    // Helpers
    public function getTotalValue()
    {
        return $this->deals()->sum('value');
    }

    public function getDealsCount()
    {
        return $this->deals()->count();
    }
}
