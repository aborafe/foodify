<?php

namespace App\Models;

use App\Contracts\AdminSearchable;
use App\Models\Concerns\AdminSearchableModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'order_number',
    'user_id',
    'payment_method_id',
    'subtotal',
    'delivery_fee',
    'manual_adjustment',
    'total',
    'payment_status',
    'status',
    'delivery_address',
    'notes',
    'estimated_delivery_time',
])]
class Order extends Model implements AdminSearchable
{
    use AdminSearchableModel, HasFactory;

    protected static array $adminSearchableColumns = ['order_number', 'status', 'payment_status', 'delivery_address', 'notes'];

    protected static string $adminSearchTitleColumn = 'order_number';

    protected static string $adminSearchRouteName = 'admin.orders';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'manual_adjustment' => 'decimal:2',
            'total' => 'decimal:2',
            'estimated_delivery_time' => 'integer',
        ];
    }
}
