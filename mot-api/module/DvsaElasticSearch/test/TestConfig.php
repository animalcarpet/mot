<?php

return [
    'modules' => [
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
        'DvsaElasticSearch',
        'DvsaAuthentication',
        'DvsaAuthorisation',
    ],
    'test_namespaces' => [
        'DvsaMotApiTest' => __DIR__.'/'.'../../DvsaMotApiTest',
        'DvsaEntitiesTest' => __DIR__.'/'.'/../../DvsaEntities/test/DvsaEntitiesTest',
        'DvsaCommonApiTest' => __DIR__.'/../../DvsaCommonApi/test/DvsaCommonApiTest',
        'DvsaElasticSearchTest' => __DIR__.'/DvsaElasticSearchTest',
    ],
];
