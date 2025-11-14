<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUpStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'sequence_id',
        'step_number',
        'delay_days',
        'action_type',
        'message_template',
        'subject',
    ];

    protected $casts = [
        'step_number' => 'integer',
        'delay_days' => 'integer',
    ];

    // Relationships
    public function sequence()
    {
        return $this->belongsTo(FollowUpSequence::class, 'sequence_id');
    }

    // Helpers
    public function renderMessage($lead)
    {
        $message = $this->message_template;

        // Replace placeholders
        $replacements = [
            '{name}' => $lead->name,
            '{company}' => $lead->company ?? '',
            '{email}' => $lead->email ?? '',
            '{phone}' => $lead->phone ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }
}
