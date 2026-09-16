<?php

namespace App\Services\Booking;

use App\Models\Booking;
use Carbon\Carbon;

class BookingCodeGenerator
{
    public function generate(Carbon $date): string
    {
        $prefix = 'HH-'.$date->format('Ymd').'-';

        $countToday = Booking::where('code', 'like', $prefix.'%')->count();

        return $prefix.str_pad((string) ($countToday + 1), 5, '0', STR_PAD_LEFT);
    }
}
