<?php
declare(strict_types=1);
namespace App\Controllers;

use App\{Auth, Container, Http};
use App\Services\SummaryService;

final class BinController
{
    /** GET /api/v1/bins?status=needs_collection */
    public static function index(array $p): void
    {
        Auth::require(['mobile', 'web']);
        Http::ok(Container::store()->listBins($_GET['status'] ?? null));
    }

    /** GET /api/v1/bins/{id} */
    public static function show(array $p): void
    {
        Auth::require(['mobile', 'web']);
        $store = Container::store();
        $bin = $store->getBin((int)$p['id']);
        if (!$bin) Http::error(404, 'Bin not found');
        $bin['recent_readings'] = $store->recentReadings((int)$p['id'], 24);
        Http::ok($bin);
    }

    /** GET /api/v1/summary */
    public static function summary(array $p): void
    {
        Auth::require(['web', 'mobile']);
        Http::ok(SummaryService::dashboard());
    }
}
