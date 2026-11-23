<?php
declare(strict_types=1);
namespace App\Contracts;

/**
 * The ONLY thing the logic layer knows about storage.
 * Esheng (DB) implements this interface (e.g. MySqlStore) - no logic code changes needed.
 *
 * Bin arrays must contain: id, bin_code, sensor_id, block_id, block_name, latitude, longitude,
 * empty_distance_cm, fill_percent, battery_percent, status ('ok'|'needs_collection'|'fault'), last_reading_at
 */
interface DataStore
{
    // --- API keys ---
    public function findApiKeyByHash(string $hash): ?array;          // returns ['id','name','role'] if active
    public function touchApiKey(int $id): void;
    public function addApiKey(string $name, string $role, string $hash): void;

    // --- Bins ---
    public function findBinBySensor(string $sensorId): ?array;
    public function getBin(int $id): ?array;
    public function listBins(?string $status = null): array;
    public function updateBin(int $id, array $fields): void;         // partial update

    // --- Readings ---
    public function addReading(int $binId, float $distanceCm, float $fillPercent, ?float $battery, string $recordedAt): void;
    public function recentReadings(int $binId, int $limit): array;   // newest first: fill_percent, battery_percent, recorded_at

    // --- Alerts ---
    public function hasOpenAlert(int $binId, string $type): bool;
    public function addAlert(int $binId, string $type, string $message): void;
    public function resolveAlerts(int $binId, string $type): void;
    public function listAlerts(bool $resolved): array;               // id, bin_id, bin_code, type, message, is_resolved, created_at
    public function openAlertCount(): int;

    // --- Collections ---
    public function addCollection(int $binId, string $driverName, ?float $fillBefore): void;
}
