<?php

namespace App\DTOs\Profile;

readonly class UpdateProfileData
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(public array $attributes) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(attributes: $data);
    }
}
