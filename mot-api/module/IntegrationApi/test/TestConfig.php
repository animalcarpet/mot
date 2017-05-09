<?php

return [
    'modules' => [
        'DvsaFeature',
        'DvsaCommonApi',
        'IntegrationApi',
        'DvsaEntities',
        'DvsaAuthorisation',
    ],
    'test_namespaces' => [
        'DvsaCommonApiTest' => __DIR__.'/../../DvsaCommonApi/test/DvsaCommonApiTest',
    ],
];
