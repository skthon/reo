<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\MatchApiHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Factories\Sequence;

class MatchApiPriceFilterTest extends TestCase
{
    use MatchAPIHelper;
    use RefreshDatabase;

    public function test_responds_with_empty_results_when_search_profiles_are_created_with_exclusive_price_filter()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 100000],
            new Sequence(
                ['min_price' => 140000, 'max_price' => 150000],
                ['min_price' => 50000, 'max_price' => 70000],
                ['min_price' => 150000, 'max_price' => 400000]
            )
        );

        $this->assertEmpty(
            $expectedResponse->get('matching_profiles')
        );
    }

    public function test_responds_with_empty_results_when_search_profiles_min_price_is_null_and_max_price_is_null()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 200000],
            new Sequence(
                ['min_price' => null, 'max_price' => null]
            )
        );

        $this->assertEmpty(
            $expectedResponse->get('matching_profiles')
        );
    }

    public function test_responds_with_results_when_search_profiles_are_created_with_inclusive_price_filter()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 200000],
            new Sequence(
                ['min_price' => 150000, 'max_price' => 300000],
                ['min_price' => 180000, 'max_price' => 210000],
                ['min_price' => 100000, 'max_price' => 250000]
            )
        );

        $this->assertEquals(
            $expectedResponse->get('matching_profiles'),
            collect($searchProfiles)->map(function ($searchProfile) {
                return [
                    'searchProfileId'    => $searchProfile->uuid,
                    'score'              => 1,
                    'strictMatchesCount' => 1,
                    'looseMatchesCount'  => 0
                ];
            })->all()
        );
    }

    public function test_responds_with_loose_results_when_search_profiles_are_created_with_slightly_exclusive_price_filter()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 200000],
            new Sequence(
                ['min_price' => 100000, 'max_price' => 190000],
                ['min_price' => 210000, 'max_price' => 300000],
            )
        );

        $this->assertEquals(
            $expectedResponse->get('matching_profiles'),
            collect($searchProfiles)->map(function ($searchProfile) {
                return [
                    'searchProfileId'    => $searchProfile->uuid,
                    'score'              => 0.5,
                    'strictMatchesCount' => 0,
                    'looseMatchesCount'  => 1
                ];
            })->all()
        );
    }

    public function test_responds_with_empty_results_when_search_profiles_min_price_is_null_and_max_price_is_exclusive()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 200000],
            new Sequence(
                ['min_price' => null, 'max_price' => 150000],
                ['min_price' => null, 'max_price' => 100000],
            )
        );

        $this->assertEmpty(
            $expectedResponse->get('matching_profiles')
        );
    }

    public function test_responds_with_results_when_search_profiles_min_price_is_null_and_max_price_is_inclusive()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 200000],
            new Sequence(
                ['min_price' => null, 'max_price' => 250000],
                ['min_price' => null, 'max_price' => 300000],
                ['min_price' => null, 'max_price' => 400000],
            )
        );

        $this->assertEquals(
            $expectedResponse->get('matching_profiles'),
            collect($searchProfiles)->map(function ($searchProfile) {
                return [
                    'searchProfileId'    => $searchProfile->uuid,
                    'score'              => 1,
                    'strictMatchesCount' => 1,
                    'looseMatchesCount'  => 0
                ];
            })->all()
        );
    }
    public function test_responds_with_loose_results_when_search_profiles_min_price_is_null_and_slightly_exclusive_max_price()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 200000],
            new Sequence(
                ['min_price' => null, 'max_price' => 160001],
                ['min_price' => null, 'max_price' => 170000],
                ['min_price' => null, 'max_price' => 180000],
            )
        );

        $this->assertEquals(
            $expectedResponse->get('matching_profiles'),
            collect($searchProfiles)->map(function ($searchProfile) {
                return [
                    'searchProfileId'    => $searchProfile->uuid,
                    'score'              => 0.5,
                    'strictMatchesCount' => 0,
                    'looseMatchesCount'  => 1
                ];
            })->all()
        );
    }

    public function test_responds_with_empty_results_when_search_profiles_min_price_is_exclusive_and_max_price_is_null()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 200000],
            new Sequence(
                ['min_price' => 300000, 'max_price' => null],
                ['min_price' => 270000, 'max_price' => null],
            )
        );

        $this->assertEmpty(
            $expectedResponse->get('matching_profiles')
        );
    }

    public function test_responds_with_results_when_search_profiles_min_price_is_inclusive_and_max_price_is_null()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 200000],
            new Sequence(
                ['min_price' => 100000, 'max_price' => null],
                ['min_price' => 150000, 'max_price' => null],
                ['min_price' => 190000, 'max_price' => null],
            )
        );

        $this->assertEquals(
            $expectedResponse->get('matching_profiles'),
            collect($searchProfiles)->map(function ($searchProfile) {
                return [
                    'searchProfileId'    => $searchProfile->uuid,
                    'score'              => 1,
                    'strictMatchesCount' => 1,
                    'looseMatchesCount'  => 0
                ];
            })->all()
        );
    }

    public function test_responds_with_loose_results_when_search_profiles_min_price_is_slightly_exclusive_and_max_price_is_null()
    {
        list($searchProfiles, $expectedResponse) = $this->seedDataRequiredForFilter(
            ['price' => 200000],
            new Sequence(
                ['min_price' => 200001, 'max_price' => null],
                ['min_price' => 220000, 'max_price' => null],
                ['min_price' => 240000, 'max_price' => null],
            )
        );

        $this->assertEquals(
            $expectedResponse->get('matching_profiles'),
            collect($searchProfiles)->map(function ($searchProfile) {
                return [
                    'searchProfileId'    => $searchProfile->uuid,
                    'score'              => 0.5,
                    'strictMatchesCount' => 0,
                    'looseMatchesCount'  => 1
                ];
            })->all()
        );
    }
}
