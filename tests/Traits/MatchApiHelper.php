<?php

namespace Tests\Traits;

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\SearchProfile;
use Illuminate\Database\Eloquent\Factories\Sequence;

trait MatchApiHelper
{
    /**
     * Create required data before running the tests
     *
     * @param array   $propertyAttributes
     * @param \Illuminate\Database\Eloquent\Factories\Sequence  $searchProfileAttributes
     */
    public function seedDataRequiredForFilter(array $propertyAttributes, Sequence $searchProfileAttributes)
    {
        $propertyType = PropertyType::factory()->create();
        $property = Property::factory()->create(array_merge([
            'price'         => null,
            'area'          => null,
            'rooms'         => null,
            'return_actual' => null,
            'year_of_construction' => null,
        ], $propertyAttributes));
        $property->property_type()->associate($propertyType)->save();
        $searchProfiles = SearchProfile::factory()->count(3)
            ->for($propertyType, "property_type")
            ->state($searchProfileAttributes)
            ->create();

        $response = $this->get("/api/match/{$property->uuid}");
        $statusCode = $response->status();
        $expectedResponse = collect(json_decode($response->content(), true));

        $this->assertEquals($statusCode, 200);
        $this->assertEquals(
            $expectedResponse->get('property'),
            $property->fresh()->toArray()
        );

        return [$searchProfiles, $expectedResponse];
    }
}
