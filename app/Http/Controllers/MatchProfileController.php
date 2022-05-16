<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Repositories\PropertyRepository;
use App\Repositories\SearchProfileRepository;

class MatchProfileController extends Controller
{
    /**
     * Fetch the home prices data based on the request
     *
     * @todo  ADD Authentication
     * @param  string $propertyID
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(string $propertyUuid): JsonResponse
    {
        if (! Str::isUUid($propertyUuid)) {
            return response()->json([
                "error" => 'Bad input, Please specify property id to filter the search profiles'
            ], 422);
        }

        $property = PropertyRepository::findByUuid($propertyUuid);
        if (! $property) {
            return response()->json([
                "error" => 'Invalid input, Please specify valid property id to filter the search profiles'
            ], 422);
        }

        try {
            $searchProfiles = SearchProfileRepository::getMatchingProfiles($property, 0.25);
            return response()->json([
                'property'          => $property->toArray(),
                'matching_profiles' => $searchProfiles,
            ], 200);
        } catch (Exception $ex) {
            Log::info(" Error fetching matching profiles " . $ex->getMessage());
            return response()->json(['error' => "Internal error"], 500);
        }
    }
}
