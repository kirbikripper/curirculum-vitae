<?php

namespace App\Services\Grid\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface GridServiceInterface
{
    public function getConfig(): array;

    public function get(): LengthAwarePaginator;

    public function getMappings(Collection $collection): array;
}
