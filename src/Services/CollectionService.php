<?php
declare(strict_types=1);
namespace App\Services;

use App\Container;

final class CollectionService
{
    /** Driver confirms pickup: log it, reset bin, resolve overflow alert. */
    public static function confirm(int $binId, string $driver): ?array
    {
        $store = Container::store();
        $bin = $store->getBin($binId);
        if (!$bin) return null;

        $store->addCollection($binId, $driver, (float)$bin['fill_percent']);
        $store->updateBin($binId, ['fill_percent' => 0.0, 'status' => 'ok']);
        $store->resolveAlerts($binId, 'overflow_risk');
        return ['bin_id' => $binId, 'status' => 'ok', 'message' => 'Collection recorded'];
    }
}
