<?php

namespace App\Models;

use App\Traits\HasUUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyType extends Model
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
    ];

    const PROPERTY_TYPES = [
        'RESIDENTIAL_BUILDING',
        'APARTMENT_FLAT',
        'COMMERCIAL_BUILDING',
        // ... few more
    ];

    /**
     * Get the company members.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function search_profiles(): HasMany
    {
        return $this->hasMany(\App\Models\SearchProfile::class, 'uuid', 'property_type_uuid');
    }
}
