<?php

namespace App\DTOs\PaymentMethod;

readonly class StorePaymentMethodData
{
    public function __construct(
        public string $type,
        public ?string $cardBrand = null,
        public ?string $bankName = null,
        public ?string $lastFour = null,
        public bool $isDefault = false,
    ) {}

    /**
     * @param  array{type: string, card_brand?: string|null, bank_name?: string|null, last_four?: string|null, is_default?: bool|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            cardBrand: $data['card_brand'] ?? null,
            bankName: $data['bank_name'] ?? null,
            lastFour: $data['last_four'] ?? null,
            isDefault: (bool) ($data['is_default'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'card_brand' => $this->cardBrand,
            'bank_name' => $this->bankName,
            'last_four' => $this->lastFour,
            'is_default' => $this->isDefault,
        ];
    }
}
