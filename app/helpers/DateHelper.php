<?php

class DateHelper
{
    public static function now(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format);
    }

    public static function toIso(string $datetime): string
    {
        $dt = new DateTime($datetime, new DateTimeZone('Asia/Jakarta'));
        return $dt->format('c');
    }

    public static function toDisplay(string $datetime): string
    {
        $dt = new DateTime($datetime, new DateTimeZone('Asia/Jakarta'));
        setlocale(LC_TIME, 'id_ID.utf8', 'Indonesian');
        return $dt->format('d F Y, H:i') . ' WIB';
    }

    public static function diffInHours(string $start, string $end): float
    {
        $startDt = new DateTime($start);
        $endDt = new DateTime($end);
        $diff = $endDt->getTimestamp() - $startDt->getTimestamp();
        return round($diff / 3600, 2);
    }

    public static function today(string $format = 'Y-m-d'): string
    {
        return date($format);
    }
}
