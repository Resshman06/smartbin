<?php
declare(strict_types=1);
namespace App\Controllers;

use App\{Auth, Container, Http};

final class AlertController
{
    /** GET /api/v1/alerts?resolved=0 */
    public static function index(array $p): void
    {
        Auth::require(['mobile', 'web']);
        Http::ok(Container::store()->listAlerts(!empty($_GET['resolved'])));
    }
}
