<?php
// Usage: php scripts/generate_key.php <name> <sensor|mobile|web|admin>
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

[$script, $name, $role] = $argv + [null, null, null];
if (!$name || !in_array($role, ['sensor', 'mobile', 'web', 'admin'], true)) {
    exit("Usage: php scripts/generate_key.php <name> <sensor|mobile|web|admin>\n");
}
$key = bin2hex(random_bytes(24));
App\Container::store()->addApiKey($name, $role, hash('sha256', $key));
echo "API key for '$name' ($role):\n$key\n\nSave it now - it cannot be shown again.\n";
