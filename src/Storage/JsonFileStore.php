<?php
declare(strict_types=1);
namespace App\Storage;

use App\Contracts\DataStore;

/** Temporary stand-in so the logic can run/test with no database. Not for production. */
final class JsonFileStore implements DataStore
{
    private string $file;

    public function __construct(?string $file = null)
    {
        $this->file = $file ?? dirname(__DIR__, 2) . '/data/store.json';
        if (!is_file($this->file)) $this->save($this->seed());
    }

    private function seed(): array
    {
        $coords = [[3.0745,101.6070],[3.0750,101.6082],[3.0738,101.6091],[3.0729,101.6078],[3.0722,101.6066],
                   [3.0731,101.6055],[3.0742,101.6050],[3.0756,101.6058],[3.0760,101.6072],[3.0753,101.6095]];
        $bins = []; $id = 1;
        foreach ($coords as $i => [$lat, $lng]) {
            $b = $i + 1;
            for ($n = 1; $n <= 2; $n++) {
                $bins[] = ['id' => $id++, 'bin_code' => sprintf('B%02d-%d', $b, $n),
                    'sensor_id' => sprintf('SENSOR-%02d-%d', $b, $n), 'block_id' => $b,
                    'block_name' => 'Block ' . chr(64 + $b), 'latitude' => $lat, 'longitude' => $lng,
                    'empty_distance_cm' => 100.0, 'fill_percent' => 0.0, 'battery_percent' => null,
                    'status' => 'ok', 'last_reading_at' => null];
            }
        }
        return ['bins' => $bins, 'api_keys' => [], 'readings' => [], 'alerts' => [], 'collections' => [], 'next' => 1];
    }

    private function load(): array
    {
        return json_decode((string) file_get_contents($this->file), true) ?: $this->seed();
    }

    private function save(array $d): void
    {
        file_put_contents($this->file, json_encode($d, JSON_PRETTY_PRINT), LOCK_EX);
    }

    private function nextId(array &$d): int { return $d['next']++; }

    // --- API keys ---
    public function findApiKeyByHash(string $hash): ?array
    {
        foreach ($this->load()['api_keys'] as $k)
            if ($k['key_hash'] === $hash && $k['is_active']) return ['id' => $k['id'], 'name' => $k['name'], 'role' => $k['role']];
        return null;
    }
    public function touchApiKey(int $id): void
    {
        $d = $this->load();
        foreach ($d['api_keys'] as &$k) if ($k['id'] === $id) $k['last_used_at'] = date('c');
        $this->save($d);
    }
    public function addApiKey(string $name, string $role, string $hash): void
    {
        $d = $this->load();
        $d['api_keys'][] = ['id' => $this->nextId($d), 'name' => $name, 'role' => $role,
            'key_hash' => $hash, 'is_active' => true, 'last_used_at' => null];
        $this->save($d);
    }

    // --- Bins ---
    public function findBinBySensor(string $sensorId): ?array
    {
        foreach ($this->load()['bins'] as $b) if ($b['sensor_id'] === $sensorId) return $b;
        return null;
    }
    public function getBin(int $id): ?array
    {
        foreach ($this->load()['bins'] as $b) if ($b['id'] === $id) return $b;
        return null;
    }
    public function listBins(?string $status = null): array
    {
        $bins = $this->load()['bins'];
        if ($status !== null) $bins = array_values(array_filter($bins, fn($b) => $b['status'] === $status));
        usort($bins, fn($a, $b) => $b['fill_percent'] <=> $a['fill_percent']);
        return $bins;
    }
    public function updateBin(int $id, array $fields): void
    {
        $d = $this->load();
        foreach ($d['bins'] as &$b) if ($b['id'] === $id) $b = array_merge($b, $fields);
        $this->save($d);
    }

    // --- Readings ---
    public function addReading(int $binId, float $distanceCm, float $fillPercent, ?float $battery, string $recordedAt): void
    {
        $d = $this->load();
        $d['readings'][] = ['id' => $this->nextId($d), 'bin_id' => $binId, 'distance_cm' => $distanceCm,
            'fill_percent' => $fillPercent, 'battery_percent' => $battery, 'recorded_at' => $recordedAt];
        $this->save($d);
    }
    public function recentReadings(int $binId, int $limit): array
    {
        $r = array_values(array_filter($this->load()['readings'], fn($x) => $x['bin_id'] === $binId));
        return array_slice(array_reverse($r), 0, $limit);
    }

    // --- Alerts ---
    public function hasOpenAlert(int $binId, string $type): bool
    {
        foreach ($this->load()['alerts'] as $a)
            if ($a['bin_id'] === $binId && $a['type'] === $type && !$a['is_resolved']) return true;
        return false;
    }
    public function addAlert(int $binId, string $type, string $message): void
    {
        $d = $this->load();
        $bin = null; foreach ($d['bins'] as $b) if ($b['id'] === $binId) $bin = $b;
        $d['alerts'][] = ['id' => $this->nextId($d), 'bin_id' => $binId, 'bin_code' => $bin['bin_code'] ?? '',
            'type' => $type, 'message' => $message, 'is_resolved' => false, 'created_at' => date('c')];
        $this->save($d);
    }
    public function resolveAlerts(int $binId, string $type): void
    {
        $d = $this->load();
        foreach ($d['alerts'] as &$a)
            if ($a['bin_id'] === $binId && $a['type'] === $type) $a['is_resolved'] = true;
        $this->save($d);
    }
    public function listAlerts(bool $resolved): array
    {
        $a = array_values(array_filter($this->load()['alerts'], fn($x) => $x['is_resolved'] === $resolved));
        return array_slice(array_reverse($a), 0, 100);
    }
    public function openAlertCount(): int
    {
        return count(array_filter($this->load()['alerts'], fn($x) => !$x['is_resolved']));
    }

    // --- Collections ---
    public function addCollection(int $binId, string $driverName, ?float $fillBefore): void
    {
        $d = $this->load();
        $d['collections'][] = ['id' => $this->nextId($d), 'bin_id' => $binId, 'driver_name' => $driverName,
            'fill_before' => $fillBefore, 'collected_at' => date('c')];
        $this->save($d);
    }
}
