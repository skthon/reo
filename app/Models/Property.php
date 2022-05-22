<?php

namespace App\Models;

use App\Traits\HasUUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Property extends Model
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
        'address',
        'price',
        'area',
        'year_of_construction',
        'rooms',
        'heating_type',
        'return_actual',
        'parking',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'parking' => 'boolean',
        'status'  => 'boolean',
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

    public static function getRangeFields(): array
    {
        return [
            'price',
            'year_of_construction',
            'area',
            'rooms',
            'return_actual'
        ];
    }

    public static function getDirectFields(): array
    {
        return [];
    }
}
