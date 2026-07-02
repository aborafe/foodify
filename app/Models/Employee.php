<?php

namespace App\Models;

use App\Contracts\AdminSearchable;
use App\Models\Concerns\AdminSearchableModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'full_name',
    'email',
    'phone',
    'role',
    'password',
    'email_verified_at',
    'is_active',
])]
#[Hidden(['password', 'remember_token'])]
class Employee extends Authenticatable implements AdminSearchable
{
    use AdminSearchableModel, Notifiable;

    protected static array $adminSearchableColumns = ['full_name', 'email', 'phone', 'role'];

    protected static string $adminSearchTitleColumn = 'full_name';

    protected static string $adminSearchRouteName = 'admin.employees.index';

    protected $attributes = [
        'role' => 'cashier',
        'is_active' => true,
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
