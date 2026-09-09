<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pocket extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'current_amount',
        'auto_allocate_percentage',
        'is_active',
        'is_sweep_target',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'auto_allocate_percentage' => 'decimal:2',
            'is_active' => 'boolean',
            'is_sweep_target' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
