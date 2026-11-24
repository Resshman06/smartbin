<?php
declare(strict_types=1);
namespace App;

final class Http
{
    public static function json(int $status, array $payload): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function ok(mixed $data, int $status = 200): never
    {
        self::json($status, ['success' => true, 'data' => $data]);
    }

    public static function error(int $status, string $message): never
    {
        self::json($status, ['success' => false, 'error' => $message]);
    }

    public static function body(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === '' || $raw === false) return [];
        $data = json_decode($raw, true);
        if (!is_array($data)) self::error(400, 'Invalid JSON body');
        return $data;
    }
}
