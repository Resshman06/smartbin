<?php
declare(strict_types=1);
namespace App;

/** API-key auth. Header: X-API-Key. Roles: sensor, mobile, web, admin. Only SHA-256 hashes are stored. */
final class Auth
{
    public static function require(array $allowedRoles): array
    {
        $key = $_SERVER['HTTP_X_API_KEY'] ?? '';
        if ($key === '') Http::error(401, 'Missing X-API-Key header');

        $store = Container::store();
        $row = $store->findApiKeyByHash(hash('sha256', $key));
        if (!$row) Http::error(401, 'Invalid API key');
        if ($row['role'] !== 'admin' && !in_array($row['role'], $allowedRoles, true)) {
            Http::error(403, 'This API key is not allowed to access this endpoint');
        }
        $store->touchApiKey((int) $row['id']);
        return $row;
    }
}
