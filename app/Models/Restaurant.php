<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'subtitle',
        'address',
        'contact_number',
        'latitude',
        'longitude',
        'postal_code',
        'rating',
        'operating_hours',
        'qr_data',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'operating_hours' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'rating' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Amenities ticked for this restaurant (admin checkbox selection).
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class);
    }

    /**
     * Banner carousel images, ordered for display.
     */
    public function images(): HasMany
    {
        return $this->hasMany(RestaurantImage::class)->orderBy('sort_order');
    }
}
