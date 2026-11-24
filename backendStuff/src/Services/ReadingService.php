<?php
declare(strict_types=1);
namespace App\Services;

use App\Container;

/** Core logic: sensor distance -> fill %, false-reading filter, alert rules. */
final class ReadingService
{
    public static function ingest(string $sensorId, float $distanceCm, ?float $battery, ?string $recordedAt): array
    {
        $store     = Container::store();
        $threshold = (float) env('FILL_ALERT_THRESHOLD', 80);
        $confirmN  = (int) env('CONFIRM_READINGS', 3);
        $lowBatt   = (float) env('LOW_BATTERY_THRESHOLD', 15);

        $bin = $store->findBinBySensor($sensorId);
        if (!$bin) return ['error' => 'Unknown sensor_id'];

        // Sensor is on the lid: small distance = full bin.
        $empty = (float) $bin['empty_distance_cm'];
        $fill  = round(max(0.0, min(100.0, (($empty - $distanceCm) / $empty) * 100)), 1);

        $store->addReading((int)$bin['id'], $distanceCm, $fill, $battery, $recordedAt ?? date('Y-m-d H:i:s'));

        // False-reading filter: N consecutive readings >= threshold
        $recent = array_column($store->recentReadings((int)$bin['id'], $confirmN), 'fill_percent');
        $confirmed = count($recent) === $confirmN && min($recent) >= $threshold;

        $status = $bin['status'] === 'fault' ? 'fault'
            : ($confirmed ? 'needs_collection' : ($fill < $threshold ? 'ok' : $bin['status']));

        $store->updateBin((int)$bin['id'], [
            'fill_percent'    => $fill,
            'battery_percent' => $battery ?? $bin['battery_percent'],
            'status'          => $status,
            'last_reading_at' => date('c'),
        ]);

        if ($confirmed) self::alertOnce((int)$bin['id'], 'overflow_risk',
            "Bin {$bin['bin_code']} is {$fill}% full - collection needed");
        if ($battery !== null && $battery < $lowBatt) self::alertOnce((int)$bin['id'], 'low_battery',
            "Sensor battery for bin {$bin['bin_code']} is {$battery}%");

        return ['bin_id' => (int)$bin['id'], 'fill_percent' => $fill, 'status' => $status, 'alert_confirmed' => $confirmed];
    }

    private static function alertOnce(int $binId, string $type, string $msg): void
    {
        $store = Container::store();
        if (!$store->hasOpenAlert($binId, $type)) $store->addAlert($binId, $type, $msg);
    }
}
