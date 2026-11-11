<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class FuelRecord extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'vehicle_id',
        'date',
        'liters',
        'price_per_liter',
        'total_cost',
        'odometer',
        'notes',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'date' => 'date',
        'liters' => 'decimal:2',
        'price_per_liter' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (FuelRecord $record): void {
            $record->total_cost = (float) $record->liters * (float) $record->price_per_liter;
        });

        static::deleting(function (FuelRecord $record): void {
            foreach ($record->attachments as $attachment) {
                Storage::delete($attachment->file_path);
                $attachment->delete();
            }
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
