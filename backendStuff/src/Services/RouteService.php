<?php
declare(strict_types=1);
namespace App\Services;

use App\Container;

/**
 * Builds a collection route for all bins that need emptying.
 * Uses nearest-neighbour ordering with haversine distance (no external API needed).
 * UPGRADE POINT: swap orderStops() for a Google Distance Matrix / VRP call later.
 */
final class RouteService
{
    public static function build(?float $startLat, ?float $startLng): array
    {
        $startLat ??= (float) env('DEPOT_LAT', 3.0738);
        $startLng ??= (float) env('DEPOT_LNG', 101.6065);

        $rows = Container::store()->listBins('needs_collection');

        // Group bins by block: one stop per block (2 bins share a collection point)
        $stops = [];
        foreach ($rows as $r) {
            $id = $r['block_id'];
            $stops[$id] ??= ['block_id' => $id, 'block_name' => $r['block_name'],
                'latitude' => (float)$r['latitude'], 'longitude' => (float)$r['longitude'], 'bins' => []];
            $stops[$id]['bins'][] = ['bin_id' => (int)$r['id'], 'bin_code' => $r['bin_code'],
                'fill_percent' => (float)$r['fill_percent']];
        }

        [$ordered, $total] = self::orderStops(array_values($stops), $startLat, $startLng);
        return ['start' => ['lat' => $startLat, 'lng' => $startLng],
                'total_stops' => count($ordered), 'total_distance_km' => round($total, 2), 'stops' => $ordered];
    }

    private static function orderStops(array $stops, float $lat, float $lng): array
    {
        $ordered = []; $total = 0.0; $seq = 1;
        while ($stops) {
            $bestKey = null; $bestD = INF;
            foreach ($stops as $k => $s) {
                $d = self::haversine($lat, $lng, $s['latitude'], $s['longitude']);
                if ($d < $bestD) { $bestD = $d; $bestKey = $k; }
            }
            $next = $stops[$bestKey];
            $next['sequence'] = $seq++;
            $next['leg_distance_km'] = round($bestD, 2);
            $ordered[] = $next;
            $total += $bestD; $lat = $next['latitude']; $lng = $next['longitude'];
            unset($stops[$bestKey]);
        }
        return [$ordered, $total];
    }

    private static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1); $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return 2 * $r * asin(sqrt($a));
    }
}
