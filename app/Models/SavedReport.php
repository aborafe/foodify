<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'metric',
    'date_range',
    'export_format',
    'included_sections',
    'status',
    'generated_at',
])]
class SavedReport extends Model
{
    use HasFactory;

    protected $attributes = [
        'export_format' => 'pdf',
        'status' => 'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'included_sections' => 'array',
            'generated_at' => 'datetime',
        ];
    }
}
