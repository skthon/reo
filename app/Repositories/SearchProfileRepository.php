<?php

namespace App\Repositories;

use App\Models\Property;
use App\Models\SearchProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Abstracts\Repositories\EloquentModelRepository;

class SearchProfileRepository extends EloquentModelRepository
{
    /**
     * Return Eloquent Model covered by this EloquentModelRepository.
     *
     * @return string
     */
    public static function getModel(): string
    {
        return SearchProfile::class;
    }

    /**
     * Get all the matching search profiles based on the give property
     *
     * @param \App\Models\Property $property
     * @param float $deviation
     */
    public static function getMatchingProfiles(Property $property, $deviation)
    {
        // Get subQuery and the list of fields present in the subquery
        list($fieldsConsidered, $subQuery) = self::getSubQuery($property, $deviation);

        $looseCountExpression = collect([]);
        foreach ($fieldsConsidered->toArray() as $field) {
            $looseCountExpression->add(DB::raw("IF({$field} = 0.5, 1, 0)"));
        }

        $strictCountExpression = collect([]);
        foreach ($fieldsConsidered->toArray() as $field) {
            $strictCountExpression->add(DB::raw("IF({$field} = 1, 1, 0)"));
        }

        return DB::table( DB::raw("({$subQuery->toSql()}) as sub") )
            ->select([
                'searchProfileId',
                DB::raw(implode(" + ", $fieldsConsidered->toArray()) . " AS score"),
                DB::raw(implode(" + ", $strictCountExpression->toArray()) . " AS strictMatchesCount"),
                DB::raw(implode(" + ", $looseCountExpression->toArray()) . " AS looseMatchesCount")
            ])
            ->mergeBindings($subQuery->getQuery())
            ->get();
    }

    /**
     * Get the sub query instance by calculating the individual fields loose match scores and strict match scores
     *
     * @param \App\Models\Property $property
     * @param float $deviation
     */
    public static function getSubQuery(Property $property, $deviation)
    {
        $minMultiplier = max(1 - $deviation, 0);
        $maxMultiplier = max(1 + $deviation, 0);
        $selects = collect([]);
        $fieldsConsidered = collect([]);

        $subQuery = SearchProfile::where('property_type_uuid', $property->property_type_uuid);
        $selects->add('uuid AS searchProfileId');

        // Logic for adding direct conditions
        $subQuery->where(function ($subQuery) use ($property, &$selects, $minMultiplier, $maxMultiplier) {});

        // Logic for adding range conditions
        $subQuery->where(function ($subQuery) use ($property, &$selects, $minMultiplier, $maxMultiplier, &$fieldsConsidered) {
            foreach (Property::getRangeFields() as $field) {
                if ($property->$field) {
                    self::profileConditionBuilder($subQuery, $property, $field, $selects, $minMultiplier, $maxMultiplier);
                    $fieldsConsidered->add($field . "_score");
                }
            }
        });

        // Add all select fields
        $subQuery->select($selects->toArray());

        return [$fieldsConsidered, $subQuery];
    }

    /**
     * Build query conditions
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\Property $property
     * @param string $field
     * @param \Illuminate\Support\Collection $selects
     * @param float $minMultiplier
     * @param float $maxMultiplier
     */
    public static function profileConditionBuilder(
        Builder $query,
        Property $property,
        string $field,
        Collection &$selects,
        $minMultiplier,
        $maxMultiplier
    ) {
        $minColumn = "min_" . $field;
        $maxColumn = "max_" . $field;
        $value = $property->{$field};

        $aggregateColumns = collect([]);
        $aggregateColumns->add(DB::raw(
            "IF(`{$minColumn}` < {$value} AND `{$maxColumn}` > {$value}, 1, 0)"
        ));
        $query->orWhere(function ($query) use ($property, $maxColumn, $minColumn) {
            $query->where($minColumn, '<', $property->price)
                ->where($maxColumn, '>', $property->price);
        });

        $aggregateColumns->add(DB::raw(
            "IF(`{$minColumn}` IS NULL AND `{$maxColumn}` > {$value}, 1, 0)"
        ));
        $query->orWhere(function ($query) use ($property, $maxColumn, $minColumn) {
            $query->whereNull($minColumn)
                ->where($maxColumn, '>', $property->price);
        });

        $aggregateColumns->add(DB::raw(
            "IF(`{$minColumn}` < {$value} AND `{$maxColumn}` IS NULL, 1, 0)"
        ));
        $query->orWhere(function ($query) use ($property, $maxColumn, $minColumn) {
            $query->where($minColumn, '<', $property->price)
                ->whereNull($maxColumn);
        });

        $aggregateColumns->add(DB::raw(
            "IF(`{$minColumn}` * {$minMultiplier} < {$value} AND `{$maxColumn}` * {$maxMultiplier} > {$value}, 0.5, 0)"
        ));
        $query->orWhere(function ($query) use ($property, $minColumn, $maxColumn, $minMultiplier, $maxMultiplier) {
            $query->whereRaw("{$minColumn} * ? < ?", [$minMultiplier, $property->price])
                ->whereRaw("{$maxColumn} * ? > ?", [$maxMultiplier, $property->price]);
        });

        $aggregateColumns->add(DB::raw(
            "IF(`{$minColumn}` IS NULL AND `{$maxColumn}` * {$maxMultiplier} > {$value}, 0.5, 0)"
        ));
        $query->orWhere(function ($query) use ($property, $minColumn, $maxColumn, $maxMultiplier) {
            $query->whereNull($minColumn)
                ->whereRaw("{$maxColumn} * ? > ?", [$maxMultiplier, $property->price]);
        });

        $aggregateColumns->add(DB::raw(
            "IF(`{$minColumn}` * {$minMultiplier} < {$value} AND `{$maxColumn}` IS NULL, 0.5, 0)"
        ));
        $query->orWhere(function ($query) use ($property, $minColumn, $maxColumn, $minMultiplier) {
            $query->whereRaw("{$minColumn} * ? < ?", [$minMultiplier, $property->price])
                ->whereNull($maxColumn);
        });

        $selects->add(DB::raw("GREATEST(" .  implode(", " , $aggregateColumns->toArray()) . " ) AS {$field}_score"));

        return $query;
    }
}
