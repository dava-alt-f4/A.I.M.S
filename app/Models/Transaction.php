<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'pocket_id', 'amount',
        'type', 'description', 'source', 'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Pocket, $this>
     */
    public function pocket(): BelongsTo
    {
        return $this->belongsTo(Pocket::class);
    }
}
