<?php

return [
    'modules' => [
        'DoctrineModule',
        'DvsaCommonApi',
        'DvsaEventApi',
        'DvsaEntities',
        'DvsaMotApi',
        'DvsaAuthentication',
    ],
    'test_namespaces' => [
        'DvsaEventApiTest' => __DIR__.'/'.'DvsaEventApiTest',
        'DvsaCommonApiTest' => __DIR__.'/../../DvsaCommonApi/test/DvsaCommonApiTest',
        'DvsaMotApiTest' => __DIR__.'/../../DvsaMotApi/test/DvsaMotApiTest',
    ],
];
