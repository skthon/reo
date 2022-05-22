<?php

namespace App\Models;

use App\Traits\HasUUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SearchProfile extends Model
{
    use HasFactory;
    use HasUUID;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'max_price',
        'min_price',
        'max_area',
        'min_area',
        'max_year_of_construction',
        'min_year_of_construction',
        'max_rooms',
        'min_rooms',
        'max_return_actual',
        'min_return_actual',
    ];

    /**
     * Get the user belonging to the current property
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_uuid', 'uuid');
    }

    /**
     * Get the property type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property_type(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PropertyType::class, 'property_type_uuid', 'uuid');
    }
}
