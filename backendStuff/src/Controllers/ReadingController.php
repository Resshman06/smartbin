<?php
declare(strict_types=1);
namespace App\Controllers;

use App\{Auth, Http};
use App\Services\ReadingService;

final class ReadingController
{
    /** POST /api/v1/readings  (role: sensor) */
    public static function store(array $p): void
    {
        Auth::require(['sensor']);
        $b = Http::body();
        if (!isset($b['sensor_id'], $b['distance_cm']) || !is_numeric($b['distance_cm']) || $b['distance_cm'] < 0)
            Http::error(422, 'sensor_id and numeric distance_cm (>=0) are required');
        if (isset($b['battery_percent']) && (!is_numeric($b['battery_percent']) || $b['battery_percent'] < 0 || $b['battery_percent'] > 100))
            Http::error(422, 'battery_percent must be 0-100');

        $result = ReadingService::ingest((string)$b['sensor_id'], (float)$b['distance_cm'],
            isset($b['battery_percent']) ? (float)$b['battery_percent'] : null, $b['recorded_at'] ?? null);
        if (isset($result['error'])) Http::error(404, $result['error']);
        Http::ok($result, 201);
    }
}
