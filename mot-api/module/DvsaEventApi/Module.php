<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaEventApi;

use DvsaCommonApi\Transaction\ControllerTransactionAwareInitializer;
use DvsaCommonApi\Transaction\ServiceTransactionAwareInitializer;
use DvsaEventApi\Factory\Service\EventServiceFactory;
use DvsaEventApi\Service\EventService;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use DvsaEventApi\Service\EventPersonCreationService;
use DvsaEventApi\Factory\Service\EventPersonCreationServiceFactory;

/**
 * Event module.
 */
class Module implements
    ConfigProviderInterface,
    ControllerProviderInterface,
    ServiceProviderInterface
{
    public static $em;

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                EventService::class => EventServiceFactory::class,
                EventPersonCreationService::class => EventPersonCreationServiceFactory::class,
            ],
            'invokables' => [
            ],
            'initializers' => [
                'transactionAware' => ServiceTransactionAwareInitializer::class,
            ],
        ];
    }

    /**
     * Expected to return \Zend\ServiceManager\Config object or array to seed
     * such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getControllerConfig()
    {
        return [
            'initializers' => [
                'transactionAware' => ControllerTransactionAwareInitializer::class,
            ],
        ];
    }
}
