<?php

namespace App\Models;

use App\Contracts\AdminSearchable;
use App\Models\Concerns\AdminSearchableModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'image',
    'is_active',
])]
class Category extends Model implements AdminSearchable
{
    use AdminSearchableModel, HasFactory;

    protected static array $adminSearchableColumns = ['name'];

    protected static string $adminSearchTitleColumn = 'name';

    protected static string $adminSearchRouteName = 'admin.categories';

    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
