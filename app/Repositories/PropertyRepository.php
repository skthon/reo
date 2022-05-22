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

    /**
     * Check if property is qualified for searching profiles. This method will check if all the fields are not null
     *
     * @param \App\Models\Property $property
     * @return boolean
     */
    public static function doesPropertyQualifyForSearch(Property $property): bool
    {
        return collect($property->getAttributes())
            ->only(Property::getRangeFields())
            ->filter()
            ->isEmpty();
    }
}
