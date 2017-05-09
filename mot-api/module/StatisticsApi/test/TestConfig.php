<?php

return [
    'modules' => [
        'DvsaFeature',
        'DoctrineModule',
        'DoctrineORMModule',
        'DvsaCommonApi',
        'SiteApi',
        'NotificationApi',
        'DvsaEntities',
        'OrganisationApi',
        'DvsaMotApi',
        'DvsaAuthentication',
        'DvsaAuthorisation',
        'Dvsa\Mot\Api\StatisticsApi',
    ],
    'test_namespaces' => [
        'Dvsa\Mot\Api\StatisticsApiTest' => __DIR__.'/'.'Dvsa\Mot\Api\StatisticsApiTest',
    ],
];
