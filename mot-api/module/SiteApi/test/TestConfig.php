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
    ],
    'test_namespaces' => [
        'SiteApiTest' => __DIR__.'/'.'SiteApiTest',
        'DvsaCommonApiTest' => __DIR__.'/../../DvsaCommonApi/test/DvsaCommonApiTest',
    ],
];
