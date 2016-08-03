<?php

use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommonApi\Factory\DateTimeHolderFactory;

return [
    'service_manager' => [
        'abstract_factories' => [
            \DvsaCommonApi\Service\Hydrator\HydratorFactory::class,
        ],
        'factories'          => [
            DateTimeHolderInterface::class   => DateTimeHolderFactory::class,
        ],
    ],
    'view_manager'    => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];
