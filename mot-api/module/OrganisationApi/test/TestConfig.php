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
        'DvsaAuthorisation',
        'DvsaAuthentication',
    ],
    'test_namespaces' => [
        'OrganisationApiTest' => __DIR__.'/'.'OrganisationApiTest',
        'DvsaCommonApiTest' => __DIR__.'/../../DvsaCommonApi/test/DvsaCommonApiTest',
    ],
];
