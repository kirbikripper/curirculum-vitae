<?php

namespace App\Services\Grid;

use App\Services\Grid\Contracts\GridServiceInterface;
use App\Services\Grid\Traits\GridServiceValidatorTrait;
use App\Structures\QueryFilterType;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class AbstractGridService implements GridServiceInterface
{
    use GridServiceValidatorTrait;

    protected array $sortOrders = [];

    public function __construct(
        private readonly Builder $query,
        Request $request,
    ) {
        $this->validate($request->all());
    }

    public function get(): LengthAwarePaginator
    {
        $query = $this->query;

        $query = $this->filter($query);

        $query = $this->sort($query);

        return $this->paginate($query, $this->validatedRequest['page'] ?? 1, $this->validatedRequest['per_page'] ?? 10);
    }

    public function getMappings(Collection $collection): array
    {
        $mappings = [];
        if ($collection->isEmpty()) {
            return $mappings;
        }

        $firstElement = $collection->first();
        $columns = array_keys(
            $firstElement instanceof Model
                ? $firstElement->getAttributes()
                : (array) $firstElement
        );

        foreach ($columns as $column) {
            $functionName = Str::camel("get_mappings_for_column_{$column}");
            if (!method_exists($this, $functionName)) {
                continue;
            }

            $currentMapping = $this->$functionName($collection->pluck($column)->unique()->toArray(), $collection);
            if (!empty($currentMapping)) {
                $mappings[$column] = $currentMapping;
            }
        }

        return $mappings;
    }

    protected function paginate(Builder $query, int $page, int $perPage): LengthAwarePaginator
    {
        return $query->paginate(perPage: $perPage, page: $page);
    }

    private function filter(Builder $query): Builder
    {
        foreach ($this->filters as $column => $filter) {
            foreach ($filter['types'] as $type) {
                if (get_class($type) !== QueryFilterType::class) {
                    continue;
                }

                $filterValue = $this->validatedRequest['filters'][$column] ?? null;
                if (count($filter['types']) !== 1) {
                    $filterValue = $filterValue[$type->value] ?? null;
                }

                if (empty($filterValue)) {
                    continue;
                }

                $queryColumn = $filter['alias'] ?? $column;
                switch ($type) {
                    case QueryFilterType::EQUAL:
                        $query->where($queryColumn, $filterValue);
                        break;
                    case QueryFilterType::LIKE:
                        $query->where($queryColumn, 'like', "%{$filterValue}%");
                        break;
                    case QueryFilterType::MORE:
                        $query->where($queryColumn, '>=', $filterValue);
                        break;
                    case QueryFilterType::LESS:
                        $query->where($queryColumn, '<=', $filterValue);
                        break;
                    case QueryFilterType::IN:
                        $query->whereIn($queryColumn, $filterValue);
                        break;
                }
            }
        }

        return $query;
    }

    private function sort(Builder $query): Builder
    {
        foreach ($this->validatedRequest['sorts'] ?? [] as $sortRule) {
            if (empty($this->sortOrders[$sortRule['column']])) {
                $query->orderBy($sortRule['column'], $sortRule['sort']);
                continue;
            }

            foreach (
                (
                    ($sortRule['sort'] === 'asc')
                        ? $this->sortOrders[$sortRule['column']]
                        : array_reverse($this->sortOrders[$sortRule['column']])
                ) as $value
            ) {
                $query->orderByRaw("{$sortRule['column']} = '{$value}' DESC");
            }
        }

        return $query;
    }
}
