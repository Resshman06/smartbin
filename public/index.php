<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

use App\{Http, Router};
use App\Controllers\{ReadingController, BinController, AlertController, RouteController, CollectionController};

header('Access-Control-Allow-Origin: ' . env('CORS_ALLOW_ORIGIN', '*'));
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$r = new Router();
$r->add('GET',  '/api/v1/health',      fn() => Http::ok(['status' => 'running', 'time' => date('c')]));
$r->add('POST', '/api/v1/readings',    [ReadingController::class, 'store']);
$r->add('GET',  '/api/v1/bins',        [BinController::class, 'index']);
$r->add('GET',  '/api/v1/bins/{id}',   [BinController::class, 'show']);
$r->add('GET',  '/api/v1/summary',     [BinController::class, 'summary']);
$r->add('GET',  '/api/v1/alerts',      [AlertController::class, 'index']);
$r->add('GET',  '/api/v1/route',       [RouteController::class, 'show']);
$r->add('POST', '/api/v1/collections', [CollectionController::class, 'store']);

try {
    $path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
    $r->dispatch($_SERVER['REQUEST_METHOD'], $path);
} catch (Throwable $e) {
    error_log($e->getMessage());
    Http::error(500, 'Internal server error');
}
