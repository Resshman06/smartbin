<?php
declare(strict_types=1);
namespace App;

use App\Contracts\DataStore;
use App\Storage\JsonFileStore;

final class Container
{
    private static ?DataStore $store = null;

    /** Esheng: replace JsonFileStore with MySqlStore here once it implements DataStore. */
    public static function store(): DataStore
    {
        return self::$store ??= new JsonFileStore();
    }
}
