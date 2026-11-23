<?php
declare(strict_types=1);
namespace App\Services;

use App\Container;

final class SummaryService
{
    public static function dashboard(): array
    {
        $store = Container::store();
        $bins  = $store->listBins();
        $count = count($bins);
        return [
            'total_bins'       => $count,
            'needs_collection' => count(array_filter($bins, fn($b) => $b['status'] === 'needs_collection')),
            'faulty'           => count(array_filter($bins, fn($b) => $b['status'] === 'fault')),
            'avg_fill'         => $count ? round(array_sum(array_column($bins, 'fill_percent')) / $count, 1) : 0,
            'open_alerts'      => $store->openAlertCount(),
        ];
    }
}
