<?php
declare(strict_types=1);
namespace App\Controllers;

use App\{Auth, Http};
use App\Services\CollectionService;

final class CollectionController
{
    /** POST /api/v1/collections  {bin_id, driver_name} */
    public static function store(array $p): void
    {
        Auth::require(['mobile']);
        $b = Http::body();
        if (empty($b['bin_id']) || empty($b['driver_name'])) Http::error(422, 'bin_id and driver_name are required');
        $result = CollectionService::confirm((int)$b['bin_id'], (string)$b['driver_name']);
        if (!$result) Http::error(404, 'Bin not found');
        Http::ok($result, 201);
    }
}
