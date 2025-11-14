<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjectionLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'lead_id',
        'template_id',
        'objection',
        'response_sent',
        'was_successful',
        'resolved_at',
    ];

    protected $casts = [
        'was_successful' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function template()
    {
        return $this->belongsTo(ObjectionTemplate::class);
    }

    // Helpers
    public function markAsResolved($successful = true)
    {
        $this->was_successful = $successful;
        $this->resolved_at = now();
        $this->save();

        if ($this->template) {
            $this->template->updateSuccessRate();
        }
    }
}
