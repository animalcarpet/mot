<?php

return [
    'abstract_factories' => [
        \Session\Service\SessionFactory::class,
    ],
    'factories' => [
        \Zend\Session\SessionManager::class => \Zend\Session\Service\SessionManagerFactory::class,
        \Zend\Session\Storage\StorageInterface::class => Zend\Session\Service\StorageFactory::class,
        \Zend\Session\Config\ConfigInterface::class => Zend\Session\Service\SessionConfigFactory::class,
    ],
];
