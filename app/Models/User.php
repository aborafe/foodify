<?php

namespace App\Models;

use App\Contracts\AdminSearchable;
use App\Models\Concerns\AdminSearchableModel;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'full_name',
    'phone',
    'email',
    'password',
    'birth_date',
    'address',
    'image',
    'phone_verified_at',
    'is_active',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements AdminSearchable
{
    /** @use HasFactory<UserFactory> */
    use AdminSearchableModel, HasApiTokens, HasFactory, Notifiable;

    protected static array $adminSearchableColumns = ['full_name', 'phone', 'email', 'address'];

    protected static string $adminSearchTitleColumn = 'full_name';

    protected static string $adminSearchRouteName = 'admin.customers.show';

    public function otps(): HasMany
    {
        return $this->hasMany(Otp::class, 'phone', 'phone');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'phone_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
