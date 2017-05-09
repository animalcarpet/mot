<?php

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Csrf\CsrfTokenViewHelper;

return [
    'factories' => [
        'csrfToken' => function (ServiceLocatorAwareInterface $pluginManager) {
            $csrfSupport = $pluginManager->getServiceLocator()->get('CsrfSupport');

            return new CsrfTokenViewHelper($csrfSupport);
        },
    ],
];
