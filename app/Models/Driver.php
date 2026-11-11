<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'notes',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * Returns false if the driver has any associated trips.
     */
    public function canBeDeleted(): bool
    {
        return ! $this->trips()->exists();
    }
}
