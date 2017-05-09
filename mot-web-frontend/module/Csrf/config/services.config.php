<?php

use Csrf\CsrfValidatingListener;
use Csrf\Factory\CsrfSupportFactory;

return [
    'factories' => [
        'CsrfSupport' => CsrfSupportFactory::class,
    ],
    'invokables' => [
        'CsrfValidatingListener' => CsrfValidatingListener::class,
    ],
];
