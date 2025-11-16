<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyRecapMail extends Mailable
{
    use Queueable, SerializesModels;

    public $stats;
    public $userStats;

    /**
     * Create a new message instance.
     *
     * @param array $stats Global daily statistics
     * @param array $userStats User-specific statistics
     */
    public function __construct($stats, $userStats)
    {
        $this->stats = $stats;
        $this->userStats = $userStats;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Daily CRM Recap - ' . now()->format('d M Y'))
                    ->view('emails.daily-recap')
                    ->with([
                        'stats' => $this->stats,
                        'userStats' => $this->userStats,
                        'date' => now()->subDay()->format('l, d F Y')
                    ]);
    }
}
