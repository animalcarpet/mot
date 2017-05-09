<?php

return [
    'modules' => [
        'DvsaFeature',
        'DoctrineModule',
        'DoctrineORMModule',
        'DvsaCommonApi',
        'DvsaEntities',
        'UserApi',
        'OrganisationApi',
        'DvsaEntities',
        'NotificationApi',
        'DvsaMotApi',
        'DataCatalogApi',
        'VehicleApi',
        'DvsaAuthorisation',
        'DvsaAuthentication',
    ],
    'test_namespaces' => [
        'VehicleApiTest' => __DIR__.'/'.'VehicleApiTest',
        'DvsaCommonApiTest' => __DIR__.'/../../DvsaCommonApi/test/DvsaCommonApiTest',
        'DvsaMotApiTest' => __DIR__.'/../../DvsaMotApi/test/DvsaMotApiTest',
    ],
];
