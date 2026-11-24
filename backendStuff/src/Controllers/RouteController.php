<?php
declare(strict_types=1);
namespace App\Controllers;

use App\{Auth, Http};
use App\Services\RouteService;

final class RouteController
{
    /** GET /api/v1/route?lat=..&lng=..  (driver's current position optional) */
    public static function show(array $p): void
    {
        Auth::require(['mobile', 'web']);
        $lat = isset($_GET['lat']) && is_numeric($_GET['lat']) ? (float)$_GET['lat'] : null;
        $lng = isset($_GET['lng']) && is_numeric($_GET['lng']) ? (float)$_GET['lng'] : null;
        Http::ok(RouteService::build($lat, $lng));
    }
}
