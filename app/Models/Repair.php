<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Repair extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'vehicle_id',
        'repair_date',
        'odometer',
        'problem',
        'description',
        'location',
        'workshop_name',
        'invoice_number',
        'notes',
        'total_cost',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'repair_date' => 'date',
        'total_cost' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (Repair $repair): void {
            foreach ($repair->attachments as $attachment) {
                Storage::delete($attachment->file_path);
                $attachment->delete();
            }
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RepairItem::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Recalculate total_cost from the sum of all repair item totals.
     */
    public function recalculateTotalCost(): void
    {
        $this->total_cost = $this->items()->sum('total');
        $this->save();
    }
}
