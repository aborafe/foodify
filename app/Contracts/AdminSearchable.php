<?php

namespace App\Contracts;

interface AdminSearchable
{
    /**
     * @return array<int, string>
     */
    public static function adminSearchableColumns(): array;

    public static function adminSearchTitleColumn(): string;

    public static function adminSearchRouteName(): string;
}
