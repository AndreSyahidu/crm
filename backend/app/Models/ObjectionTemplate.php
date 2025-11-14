<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjectionTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'objection',
        'response',
        'category',
        'usage_count',
        'success_rate',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'success_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs()
    {
        return $this->hasMany(ObjectionLog::class, 'template_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Helpers
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    public function updateSuccessRate()
    {
        $total = $this->logs()->count();
        $successful = $this->logs()->where('was_successful', true)->count();

        if ($total > 0) {
            $this->success_rate = ($successful / $total) * 100;
            $this->save();
        }
    }
}
