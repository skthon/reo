<?php

namespace App\Abstracts\Repositories;

abstract class EloquentModelRepository
{
    /**
     * Return Eloquent Model covered by this EloquentModelRepository.
     *
     * @return string
     */
    abstract public static function getModel(): string;
}
