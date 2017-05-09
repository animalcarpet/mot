<?php

return [
    'modules' => [
        'DvsaFeature',
        'DoctrineModule',
        'DoctrineORMModule',
        'DvsaCommonApi',
        'DvsaEntities',
        'OrganisationApi',
        'NotificationApi',
        'UserApi',
        'DvsaMotApi',
        'DvsaAuthorisation',
        'DvsaAuthentication',
    ],
    'test_namespaces' => [
        'OrganisationApiTest' => __DIR__.'/../../OrganisationApi/test/OrganisationApiTest',
        'DvsaCommonApiTest' => __DIR__.'/../../DvsaCommonApi/test/DvsaCommonApiTest',
        'UserApiTest' => __DIR__.'/UserApiTest',
    ],
];
