<?php

return [
    'modules' => [
        'DvsaFeature',
        'DvsaCommonApi',
        'DvsaEntities',
        'DvsaAuthorisation',
        'MailerApi',
    ],
    'test_namespaces' => [
        'DvsaCommonApiTest' => __DIR__.'/../../DvsaCommonApi/test/DvsaCommonApiTest',
    ],
];
