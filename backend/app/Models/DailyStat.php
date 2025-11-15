<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'new_leads',
        'qualified_leads',
        'won_deals',
        'lost_deals',
        'total_revenue',
        'whatsapp_messages_sent',
        'whatsapp_messages_received',
        'conversion_rate',
        'average_deal_value',
        'metrics',
    ];

    protected $casts = [
        'date' => 'date',
        'new_leads' => 'integer',
        'qualified_leads' => 'integer',
        'won_deals' => 'integer',
        'lost_deals' => 'integer',
        'total_revenue' => 'decimal:2',
        'whatsapp_messages_sent' => 'integer',
        'whatsapp_messages_received' => 'integer',
        'conversion_rate' => 'decimal:2',
        'average_deal_value' => 'decimal:2',
        'metrics' => 'array',
    ];

    // Scopes
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);
    }

    public function scopeLastMonth($query)
    {
        return $query->whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year);
    }
}
