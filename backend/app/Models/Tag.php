<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'description',
    ];

    // Relationships
    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'lead_tags');
    }

    // Helpers
    public function getLeadsCount()
    {
        return $this->leads()->count();
    }
}
