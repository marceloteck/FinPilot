<?php

namespace App\Support;

use Carbon\Carbon;

class PeriodContext
{
    public static function current(?int $year = null, ?string $month = null): array
    {
        $activeYear = $year ?? Carbon::now()->year;
        $activeMonth = $month ?? 'all';

        if ($activeMonth !== 'all') {
            $start = Carbon::createFromDate($activeYear, (int) $activeMonth, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } else {
            $start = Carbon::createFromDate($activeYear, 1, 1)->startOfDay();
            $end = Carbon::createFromDate($activeYear, 12, 31)->endOfDay();
        }

        return [
            'year' => $activeYear,
            'month' => $activeMonth,
            'start_date' => $start,
            'end_date' => $end,
        ];
    }
}
