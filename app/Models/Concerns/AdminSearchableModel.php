<?php

namespace App\Models\Concerns;

trait AdminSearchableModel
{
    /**
     * @return array<int, string>
     */
    public static function adminSearchableColumns(): array
    {
        return static::$adminSearchableColumns ?? [];
    }

    public static function adminSearchTitleColumn(): string
    {
        return static::$adminSearchTitleColumn ?? 'id';
    }

    public static function adminSearchRouteName(): string
    {
        return static::$adminSearchRouteName ?? '';
    }
}
