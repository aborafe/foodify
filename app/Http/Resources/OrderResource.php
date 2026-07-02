<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'payment_method_id' => $this->payment_method_id,
            'subtotal' => $this->subtotal,
            'delivery_fee' => $this->delivery_fee,
            'manual_adjustment' => $this->manual_adjustment,
            'total' => $this->total,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'delivery_address' => $this->delivery_address,
            'notes' => $this->notes,
            'estimated_delivery_time' => $this->estimated_delivery_time,
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'payment_method' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
