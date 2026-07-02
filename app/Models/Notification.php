<?php

namespace App\Models;

use App\Contracts\AdminSearchable;
use App\Models\Concerns\AdminSearchableModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'title',
    'body',
    'type',
    'image',
    'is_read',
    'is_admin_visible',
    'admin_context',
    'admin_url',
])]
class Notification extends Model implements AdminSearchable
{
    use AdminSearchableModel, HasFactory;

    protected static array $adminSearchableColumns = ['title', 'body', 'type'];

    protected static string $adminSearchTitleColumn = 'title';

    protected static string $adminSearchRouteName = 'admin.notifications';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_admin_visible' => 'boolean',
        ];
    }
}
