<?php

return [
    'modules' => [
        'DvsaFeature',
        'DoctrineModule',
        'DoctrineORMModule',
        'DvsaCommonApi',
        'DvsaEntities',
        'CensorApi',
    ],
    'test_namespaces' => [
        'CensorApiTest' => __DIR__.'/'.'CensorApiTest',
    ],
];
