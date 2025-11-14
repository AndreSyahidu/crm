<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUpEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'sequence_id',
        'current_step',
        'status',
        'next_action_at',
        'enrolled_at',
        'completed_at',
    ];

    protected $casts = [
        'current_step' => 'integer',
        'next_action_at' => 'datetime',
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function sequence()
    {
        return $this->belongsTo(FollowUpSequence::class, 'sequence_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDueForAction($query)
    {
        return $query->where('status', 'active')
            ->where('next_action_at', '<=', now());
    }

    // Helpers
    public function processNextStep()
    {
        $steps = $this->sequence->steps()->orderBy('step_number')->get();
        $currentStepIndex = $this->current_step;

        if ($currentStepIndex >= $steps->count()) {
            $this->complete();
            return false;
        }

        $step = $steps[$currentStepIndex];

        // Execute step action
        switch ($step->action_type) {
            case 'whatsapp':
                $this->sendWhatsAppMessage($step);
                break;
            case 'email':
                $this->sendEmail($step);
                break;
            case 'task':
                $this->createTask($step);
                break;
        }

        // Update enrollment
        $this->current_step++;

        $nextStep = $steps->get($this->current_step);
        if ($nextStep) {
            $this->next_action_at = now()->addDays($nextStep->delay_days);
        } else {
            $this->complete();
        }

        $this->save();
        return true;
    }

    protected function sendWhatsAppMessage($step)
    {
        $message = $step->renderMessage($this->lead);

        // Add to message queue
        MessageQueue::create([
            'to_number' => $this->lead->whatsapp_number ?? $this->lead->phone,
            'message' => $message,
            'priority' => 5,
        ]);
    }

    protected function sendEmail($step)
    {
        // Implement email sending logic
    }

    protected function createTask($step)
    {
        Task::create([
            'lead_id' => $this->lead_id,
            'assigned_to' => $this->lead->assigned_to,
            'title' => $step->subject ?? 'Follow-up task',
            'description' => $step->renderMessage($this->lead),
            'due_date' => now()->addDay(),
        ]);
    }

    public function pause()
    {
        $this->status = 'paused';
        $this->save();
    }

    public function resume()
    {
        $this->status = 'active';
        $this->save();
    }

    public function complete()
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();
    }
}
