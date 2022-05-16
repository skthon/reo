<?php

namespace App\Repositories;

use App\Models\Property;
use App\Abstracts\Repositories\EloquentModelRepository;

class PropertyRepository extends EloquentModelRepository
{
    /**
     * Return Eloquent Model covered by this EloquentModelRepository.
     *
     * @return string
     */
    public static function getModel(): string
    {
        return Property::class;
    }

    /**
     * Get the property model based on the property_uuid
     *
     * @param  string   $propertyUuid
     * @return \App\Models\Property
     */
    public static function findByUuid(string $propertyUuid): ?Property
    {
        return Property::withUuid($propertyUuid)->first();
    }
}
