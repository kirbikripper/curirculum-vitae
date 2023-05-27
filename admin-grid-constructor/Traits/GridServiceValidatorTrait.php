<?php

namespace App\Services\Grid\Traits;

use App\Structures\QueryFilterType;
use App\Structures\RequestDataType;
use Illuminate\Support\Facades\Validator;

trait GridServiceValidatorTrait
{
    protected array $filters = [];

    protected array $sorts = [];

    private array $validatedRequest;

    public function getConfig(): array
    {
        if (empty((int) ($this->validatedRequest['config'] ?? 0))) {
            return [];
        }

        return [
            'filters' => $this->filters,
            'sorts' => $this->sorts,
        ];
    }

    private function getRules(): array
    {
        $rules = [
            'filters' => 'nullable|array',
            'sorts' => 'nullable|array',
            'sorts.*' => 'required|array',
            'sorts.*.column' => 'required|string|in:' . implode(',', $this->sorts),
            'sorts.*.sort' => 'required|string|in:asc,desc',
            'page' => 'nullable|int|min:1',
            'per_page' => 'nullable|int|min:1|max:50',
            'config' => 'nullable|boolean',
        ];

        foreach ($this->filters as $column => $filter) {
            if (empty($filter['types'])) {
                continue;
            }

            $dataType = $filter['data']['type'] ?? RequestDataType::STRING;
            $isArray = $dataType === RequestDataType::ARRAY;

            $rule = [
                'nullable',
                $dataType->value,
            ];

            if (!$isArray) {
                $rule = array_merge($rule, $filter['additional_rules'] ?? []);
            }

            foreach ($filter['types'] as $type) {
                if (get_class($type) !== QueryFilterType::class) {
                    continue;
                }

                $filterName = count($filter['types']) === 1
                    ? $column
                    : "{$column}.{$type->value}";

                $rules["filters.{$filterName}"] = $rule;

                if ($isArray) {
                    $arrayElementType = $filter['data']['of'] ?? RequestDataType::STRING;

                    $currentRules = array_merge([$arrayElementType->value], $filter['additional_rules'] ?? []);
                    if (!empty($filter['values'])) {
                        $currentRules[] = 'in:' . implode(',', $filter['values']);
                    }

                    $rules["filters.{$filterName}.*"] = $currentRules;
                }
            }
        }

        return $rules;
    }

    private function validate(array $request): void
    {
        $this->validatedRequest = Validator::validate($request, $this->getRules());
    }
}
