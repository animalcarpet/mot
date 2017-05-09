<?php

return [
    'modules' => [
        'DvsaFeature',
        'DoctrineModule',
        'DoctrineORMModule',
        'DvsaCommonApi',
        'CensorApi',
        'NonWorkingDaysApi',
        'DvsaMotApi',
        'OrganisationApi',
        'DvsaEntities',
        'SiteApi',
        'DataCatalogApi',
        'NotificationApi',
        'UserApi',
        'VehicleApi',
        'DvsaAuthorisation',
        'DvsaAuthentication',
    ],
    'test_namespaces' => [
        'DvsaMotApiTest' => __DIR__.'/'.'DvsaMotApiTest',
        'DvsaEntitiesTest' => __DIR__.'/'.'/../../DvsaEntities/test/DvsaEntitiesTest',
        'DvsaCommonApiTest' => __DIR__.'/../../DvsaCommonApi/test/DvsaCommonApiTest',
    ],
];
