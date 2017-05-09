<?php

return [
    'modules' => [
        'DvsaCommonApi',
        'DvsaEntities',
        'DvsaMotApi',
        'DvsaAuthorisation',
    ],
    'test_namespaces' => [
        'DvsaEntitiesTest' => __DIR__.'/'.'DvsaEntitiesTest',
        'DvsaCommonApiTest' => __DIR__.'/../../DvsaCommonApi/test/DvsaCommonApiTest',
    ],
];
