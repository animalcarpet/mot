<?php

return [
    'modules' => [
        'DoctrineModule',
        'DoctrineORMModule',
        'DvsaAuthentication',
        'DvsaAuthorisation',
        'DvsaCommonApi',
        'DvsaEntities',
        'DvsaMotApi',
        'UserApi',
        'AccountApi',
    ],
    'test_namespaces' => [
        'DvsaCommonApiTest' => __DIR__.'/../../DvsaCommonApi/test/DvsaCommonApiTest',
    ],
];
