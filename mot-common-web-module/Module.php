<?php

namespace DvsaCommon;

use DvsaCommon\Factory\Formatting\DefectSentenceCaseConverterFactory;
use DvsaCommon\Factory\HttpRestJson\ClientFactory;
use DvsaCommon\Factory\Validator\UsernameValidatorFactory;
use DvsaCommon\Formatting\DefectSentenceCaseConverter;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\Obfuscate\Factory\ParamEncrypterFactory;
use DvsaCommon\Obfuscate\Factory\ParamObfuscatorFactory;
use DvsaCommon\Obfuscate\ParamEncrypter;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\Validator\UsernameValidator;
use Zend\Loader\ClassMapAutoloader;
use Zend\Loader\StandardAutoloader;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * DvsaCommon Module.
 */
class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAutoloaderConfig()
    {
        return [
            ClassMapAutoloader::class => [
                __DIR__ . '/autoload_classmap.php',
            ],
            StandardAutoloader::class => [
                'namespaces' => [__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                Client::class => ClientFactory::class,
                ParamEncrypter::class => ParamEncrypterFactory::class,
                ParamObfuscator::class => ParamObfuscatorFactory::class,
                UsernameValidator::class => UsernameValidatorFactory::class,
                DefectSentenceCaseConverter::class => DefectSentenceCaseConverterFactory::class,
            ],
        ];
    }
}
