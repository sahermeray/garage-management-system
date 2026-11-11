<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairItem extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'repair_id',
        'description',
        'type',
        'quantity',
        'unit_price',
        'total',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (RepairItem $item): void {
            $item->total = (float) $item->quantity * (float) $item->unit_price;
        });
    }

    public function repair(): BelongsTo
    {
        return $this->belongsTo(Repair::class);
    }
}
