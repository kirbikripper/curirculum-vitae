<?php

namespace App\Services\Grid;

use App\Structures\QueryFilterType;
use App\Structures\RequestDataType;

// test class for "accounts" table
final class ExampleGridService extends AbstractGridService
{
    protected array $filters = [
        'id' => [
            'types' => [
                QueryFilterType::IN,
            ],
            'data' => [
                'type' => RequestDataType::ARRAY,
                'of' => RequestDataType::INT,
            ],
        ],
        'firstname' => [
            'types' => [
                QueryFilterType::LIKE,
            ],
            'data' => [
                'type' => RequestDataType::STRING, // default
            ],
        ],
        'lastname' => [
            'types' => [
                QueryFilterType::EQUAL
            ],
            'additional_rules' => [
                'max:32'
            ],
        ],
        'email' => [
            'types' => [
                QueryFilterType::LIKE
            ],
        ],
        'balance' => [
            'types' => [
                QueryFilterType::MORE,
                QueryFilterType::LESS,
            ],
            'data' => [
                'type' => RequestDataType::FLOAT,
            ],
        ],
    ];

    protected array $sorts = [
        'id',
        'firstname',
        'lastname',
        'balance',
    ];
}
