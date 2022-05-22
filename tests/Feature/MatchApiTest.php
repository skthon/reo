<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Property;
use Illuminate\Support\Str;
use App\Models\PropertyType;
use Tests\Traits\MatchApiHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Factories\Sequence;

class MatchApiTest extends TestCase
{
    use RefreshDatabase;
    use MatchApiHelper;

    public function test_responds_with_an_error_if_invalid_api_endpoint_is_provided()
    {
        $response = $this->get('/api/match/');
        $response->assertStatus(404);
    }

    public function test_responds_with_an_error_if_incremental_property_id_is_provided()
    {
        $response = $this->get('/api/match/1');
        $response->assertStatus(422);
        $response->assertJson([
            "error" => "Bad input, Please specify property id to filter the search profiles",
        ]);
    }

    public function test_responds_with_an_error_if_non_existing_property_id_is_provided()
    {
        $uuid = (string) Str::orderedUuid();
        $response = $this->get("/api/match/{$uuid}");
        $response->assertStatus(422);
        $response->assertJson([
            "error" => "Invalid input, Please specify valid property id to filter the search profiles",
        ]);
    }

    public function test_responds_with_an_error_when_unfilled_property_is_provided()
    {
        $propertyType = PropertyType::factory()->create();
        $property = Property::factory()->create([
            'price'         => null,
            'area'          => null,
            'rooms'         => null,
            'return_actual' => null,
            'year_of_construction' => null,
        ]);
        $property->property_type()->associate($propertyType)->save();

        $response = $this->get("/api/match/{$property->uuid}");
        $response->assertStatus(422);
        $response->assertJson([
            "error" => "Property does not have enough details to qualify for searching profiles.",
        ]);
    }

    public function test_responds_with_empty_results_when_no_search_profiles_are_created()
    {
        $propertyType = PropertyType::factory()->create();
        $property = Property::factory()->create();
        $property->property_type()->associate($propertyType)->save();

        $response = $this->get("/api/match/{$property->uuid}");
        $response->assertStatus(200);
        $response->assertJson([
            "matching_profiles" => [],
            "property" => $property->makeHidden(['property_type'])->toArray(),
        ]);
    }

    public function test_responds_with_empty_results_when_search_profiles_are_created_with_exclusive_filter()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 100000, 'year_of_construction' => 2000, 'area' => 500, 'rooms' => 2, 'return_actual' => 20],
            new Sequence(
                [
                    'min_price' => 140000,
                    'max_price' => 150000,
                    'min_year_of_construction' => 2010,
                    'max_year_of_construction' => 2020,
                    'min_area' => 1000,
                    'max_area' => 2000,
                    'max_rooms' => 5,
                    'min_rooms' => 10,
                    'min_return_actual' => 5,
                    'max_return_actual' => 10,
                ],
            )
        );

        $this->assertEmpty(
            $expectedResponse->get('matching_profiles')
        );
    }

    public function test_responds_with_empty_results_when_search_profiles_min_filters_is_null_and_max_filters_is_null()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 100000, 'year_of_construction' => 2000, 'area' => 500, 'rooms' => 2, 'return_actual' => 20],
            new Sequence(
                [
                    'min_price' => null,
                    'max_price' => null,
                    'min_year_of_construction' => null,
                    'max_year_of_construction' => null,
                    'min_area' => null,
                    'max_area' => null,
                    'max_rooms' => null,
                    'min_rooms' =>  null,
                    'min_return_actual' => null,
                    'max_return_actual' => null,
                ],
            )
        );

        $this->assertEmpty(
            $expectedResponse->get('matching_profiles')
        );
    }

    public function test_responds_with_results_when_search_profiles_are_created_with_inclusive_filters()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 100000, 'year_of_construction' => 2000, 'area' => 500, 'rooms' => 2, 'return_actual' => 20],
            new Sequence(
                [
                    'min_price' => 50000,
                    'max_price' => 150000,
                    'min_year_of_construction' => 1990,
                    'max_year_of_construction' => 2020,
                    'min_area' => 200,
                    'max_area' => 2000,
                    'min_rooms' => 2,
                    'max_rooms' => 8,
                    'min_return_actual' => 5,
                    'max_return_actual' => 30,
                ],
            )
        );

        $this->assertEquals(
            $expectedResponse->get('matching_profiles'),
            collect($searchProfiles)->map(function ($searchProfile) {
                return [
                    'searchProfileId'    => $searchProfile->uuid,
                    'score'              => 5,
                    'strictMatchesCount' => 5,
                    'looseMatchesCount'  => 0
                ];
            })->all()
        );
    }

    public function test_responds_with_loose_results_when_search_profiles_are_created_with_slightly_exclusive_filters()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 100000, 'year_of_construction' => 2000, 'area' => 500, 'rooms' => 5, 'return_actual' => 20],
            new Sequence(
                [
                    'min_price' => 50000,
                    'max_price' => 90000,
                    'min_year_of_construction' => 1990,
                    'max_year_of_construction' => 2000,
                    'min_area' => 600,
                    'max_area' => 2000,
                    'min_rooms' => 6,
                    'max_rooms' => 8,
                    'min_return_actual' => 5,
                    'max_return_actual' => 16,
                ],
            )
        );

        $this->assertEquals(
            $expectedResponse->get('matching_profiles'),
            collect($searchProfiles)->map(function ($searchProfile) {
                return [
                    'searchProfileId'    => $searchProfile->uuid,
                    'score'              => 3,
                    'strictMatchesCount' => 1,
                    'looseMatchesCount'  => 4
                ];
            })->all()
        );
    }

    public function test_responds_with_empty_results_when_search_profiles_min_filters_is_null_and_max_filters_is_exclusive()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 100000, 'year_of_construction' => 2000, 'area' => 500, 'rooms' => 5, 'return_actual' => 20],
            new Sequence(
                [
                    'min_price' => null,
                    'max_price' => 70000,
                    'min_year_of_construction' => null,
                    'max_year_of_construction' => 1995,
                    'min_area' => null,
                    'max_area' => 300,
                    'min_rooms' => null,
                    'max_rooms' => 3,
                    'min_return_actual' => null,
                    'max_return_actual' => 15,
                ],
            )
        );

        $this->assertEmpty(
            $expectedResponse->get('matching_profiles')
        );
    }

    public function test_responds_with_results_when_search_profiles_min_filters_is_null_and_max_filters_is_inclusive()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 100000, 'year_of_construction' => 2000, 'area' => 500, 'rooms' => 2, 'return_actual' => 20],
            new Sequence(
                [
                    'min_price' => null,
                    'max_price' => 110000,
                    'min_year_of_construction' => null,
                    'max_year_of_construction' => 2001,
                    'min_area' => null,
                    'max_area' => 1000,
                    'min_rooms' => null,
                    'max_rooms' => 3,
                    'min_return_actual' => 1,
                    'max_return_actual' => null,
                ],
            )
        );

        $this->assertEquals(
            $expectedResponse->get('matching_profiles'),
            collect($searchProfiles)->map(function ($searchProfile) {
                return [
                    'searchProfileId'    => $searchProfile->uuid,
                    'score'              => 5,
                    'strictMatchesCount' => 5,
                    'looseMatchesCount'  => 0
                ];
            })->all()
        );
    }

    public function test_responds_with_loose_results_when_search_profiles_min_filters_is_null_and_slightly_exclusive_max_filters()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 100000, 'year_of_construction' => 2000, 'area' => 500, 'rooms' => 5, 'return_actual' => 20],
            new Sequence(
                [
                    'min_price' => null,
                    'max_price' => 90000,
                    'min_year_of_construction' => null,
                    'max_year_of_construction' => 2000,
                    'min_area' => null,
                    'max_area' => 400,
                    'min_rooms' => null,
                    'max_rooms' => 4,
                    'min_return_actual' => null,
                    'max_return_actual' => 16,
                ],
            )
        );

        $this->assertEquals(
            $expectedResponse->get('matching_profiles'),
            collect($searchProfiles)->map(function ($searchProfile) {
                return [
                    'searchProfileId'    => $searchProfile->uuid,
                    'score'              => 3,
                    'strictMatchesCount' => 1,
                    'looseMatchesCount'  => 4
                ];
            })->all()
        );
    }

    public function test_responds_with_empty_results_when_search_profiles_min_filters_is_exclusive_and_max_filters_is_null()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 100000, 'year_of_construction' => 2000, 'area' => 500, 'rooms' => 5, 'return_actual' => 20],
            new Sequence(
                [
                    'min_price' => 150000,
                    'max_price' => null,
                    'min_year_of_construction' => 2010,
                    'max_year_of_construction' => null,
                    'min_area' => 700,
                    'max_area' => null,
                    'min_rooms' => 8,
                    'max_rooms' => null,
                    'min_return_actual' => 28,
                    'max_return_actual' => null,
                ],
            )
        );

        $this->assertEmpty(
            $expectedResponse->get('matching_profiles')
        );
    }

    public function test_responds_with_results_when_search_profiles_min_filters_is_inclusive_and_max_filters_is_null()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 100000, 'year_of_construction' => 2000, 'area' => 500, 'rooms' => 2, 'return_actual' => 20],
            new Sequence(
                [
                    'min_price' => 50000,
                    'max_price' => null,
                    'min_year_of_construction' => 1990,
                    'max_year_of_construction' => null,
                    'min_area' => 200,
                    'max_area' => null,
                    'min_rooms' => 2,
                    'max_rooms' => null,
                    'min_return_actual' => 5,
                    'max_return_actual' => null,
                ],
            )
        );

        $this->assertEquals(
            $expectedResponse->get('matching_profiles'),
            collect($searchProfiles)->map(function ($searchProfile) {
                return [
                    'searchProfileId'    => $searchProfile->uuid,
                    'score'              => 5,
                    'strictMatchesCount' => 5,
                    'looseMatchesCount'  => 0
                ];
            })->all()
        );
    }

    public function test_responds_with_loose_results_when_search_profiles_min_filters_is_slightly_exclusive_and_max_filters_is_null()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 100000, 'year_of_construction' => 2000, 'area' => 500, 'rooms' => 5, 'return_actual' => 20],
            new Sequence(
                [
                    'min_price' => 110000,
                    'max_price' => null,
                    'min_year_of_construction' => 2000,
                    'max_year_of_construction' => null,
                    'min_area' => 600,
                    'max_area' => null,
                    'min_rooms' => 6,
                    'max_rooms' => null,
                    'min_return_actual' => 24,
                    'max_return_actual' => null,
                ],
            )
        );

        $this->assertEquals(
            $expectedResponse->get('matching_profiles'),
            collect($searchProfiles)->map(function ($searchProfile) {
                return [
                    'searchProfileId'    => $searchProfile->uuid,
                    'score'              => 3,
                    'strictMatchesCount' => 1,
                    'looseMatchesCount'  => 4
                ];
            })->all()
        );
    }

    public function test_responds_with_results_sorted_by_score()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 100000, 'year_of_construction' => 2000, 'area' => 500, 'rooms' => 5, 'return_actual' => 20],
            new Sequence(
                // Score for this is 3
                [
                    'min_price' => 90000,
                    'max_price' => 120000,
                    'min_year_of_construction' => 2000,
                    'max_year_of_construction' => 2022,
                    'min_area' => 200,
                    'max_area' => 500,
                    'min_rooms' => 7,
                    'max_rooms' => 10,
                    'min_return_actual' => 1,
                    'max_return_actual' => 10,
                ],
                // Score for this is 5
                [
                    'min_price' => 90000,
                    'max_price' => 120000,
                    'min_year_of_construction' => 2000,
                    'max_year_of_construction' => 2022,
                    'min_area' => 200,
                    'max_area' => 500,
                    'min_rooms' => 2,
                    'max_rooms' => 10,
                    'min_return_actual' => 1,
                    'max_return_actual' => 25,
                ],
            )
        );

        $matchingProfiles = $expectedResponse->get('matching_profiles');
        $this->assertTrue($matchingProfiles[0]['score'] > $matchingProfiles[1]['score']);
    }
}
