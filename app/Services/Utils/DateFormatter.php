<?php

namespace App\Services\Utils;

use Carbon\Carbon;

class DateFormatter
{

    static public function format($date, $type = 'start'): string
    {
        $time = ' 00:00:00';
        if ($type === 'end') $time = ' 23:59:59';
        return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d') . $time;
    }

    static public function today($type = 'end'): string
    {
        $time = ' 00:00:00';
        if ($type === 'end') $time = ' 23:59:59';
        return Carbon::today()->format('Y-m-d') . $time;
    }

    static public function uzbekMonths(): array
    {
        return [
            1 => 'Yanvar',
            2 => 'Fevral',
            3 => 'Mart',
            4 => 'Aprel',
            5 => 'May',
            6 => 'Iyun',
            7 => 'Iyul',
            8 => 'Avgust',
            9 => 'Sentyabr',
            10 => 'Oktyabr',
            11 => 'Noyabr',
            12 => 'Dekabr',
        ];
    }
}
