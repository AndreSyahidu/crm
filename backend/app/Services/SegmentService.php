<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Segment;
use Illuminate\Support\Collection;

class SegmentService
{
    public function applyFilters(array $filters): Collection
    {
        $query = Lead::query();

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'status':
                    if (is_array($value)) {
                        $query->whereIn('status', $value);
                    } else {
                        $query->where('status', $value);
                    }
                    break;

                case 'source':
                    if (is_array($value)) {
                        $query->whereIn('source', $value);
                    } else {
                        $query->where('source', $value);
                    }
                    break;

                case 'tags':
                    $query->whereHas('tags', function ($q) use ($value) {
                        $q->whereIn('tags.id', is_array($value) ? $value : [$value]);
                    });
                    break;

                case 'lead_score_min':
                    $query->where('lead_score', '>=', $value);
                    break;

                case 'lead_score_max':
                    $query->where('lead_score', '<=', $value);
                    break;

                case 'revenue_min':
                    $query->where('expected_revenue', '>=', $value);
                    break;

                case 'revenue_max':
                    $query->where('expected_revenue', '<=', $value);
                    break;

                case 'last_contact_days':
                    $query->where('last_contact_at', '<=', now()->subDays($value));
                    break;

                case 'created_after':
                    $query->where('created_at', '>=', $value);
                    break;

                case 'created_before':
                    $query->where('created_at', '<=', $value);
                    break;

                case 'assigned_to':
                    if (is_array($value)) {
                        $query->whereIn('assigned_to', $value);
                    } else {
                        $query->where('assigned_to', $value);
                    }
                    break;

                case 'has_whatsapp':
                    if ($value) {
                        $query->whereNotNull('whatsapp_number');
                    }
                    break;

                case 'company':
                    $query->where('company', 'like', "%{$value}%");
                    break;
            }
        }

        return $query->get();
    }

    public function calculateSegmentSize(array $filters): int
    {
        return $this->applyFilters($filters)->count();
    }

    public function getSegmentLeadIds(Segment $segment): array
    {
        if (!$segment->is_dynamic) {
            // Static segment - return cached IDs
            return $segment->leads()->pluck('leads.id')->toArray();
        }

        // Dynamic segment - recalculate
        $leads = $this->applyFilters($segment->filter_rules);
        return $leads->pluck('id')->toArray();
    }
}
