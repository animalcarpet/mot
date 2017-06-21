<?php

namespace DvsaCommonTest\TestUtils;

use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\ServiceManager;

/**
 * Used to aid in testing factory classes which in 99% of cases are about retrieving dependencies
 * from appropriate service locator implementation and returning an object of a known class.
 *
 * Example of use:
 *
 *       ServiceFactoryTestHelper::testCreateServiceForSM(
 *           IdentitySessionStateServiceFactory::class,
 *           IdentitySessionStateService::class,
 *           [
 *               'MotIdentityProvider' => MotIdentityProviderInterface::class,
 *               OpenAMClientInterface::class,
 *               'tokenService' => WebAuthenticationCookieService::class,
 *               'Application/Logger' => LoggerInterface::class
 *           ]
 *       );
 *
 * Injection map is an array of elements which define dependencies looked up in ServiceLocator
 * There are 3 types of entries:
 * 1)   classic mapping of serviceName to serviceClass:
 *          'serviceName' => 'serviceClass'
 * 2)   shorthand mapping of serviceName to serviceClass:
 *          'serviceClass'
 * 3)   serviceName to callable returning object:
 *          'serviceName' => callable() - used when an object under serviceName is not trival to define (not a string or class)
 * 4)   serviceName to object
 *          'serviceName' => object - used when an object is not a class or callable, but a simple string or integer
 */
class ServiceFactoryTestHelper
{
    /**
     * Used to test factories that lookup ServiceManager implementation of ServiceLocator i.e.
     * for building all kind of 'services'.
     *
     * @param string $factoryClass
     * @param string $resultClass
     * @param array  $injectionMap
     */
    public static function testCreateServiceForSM($factoryClass, $resultClass, $injectionMap)
    {
        $sm = new ServiceManager();
        $sm->setAllowOverride(true);
        self::fillContainer($sm, $injectionMap);
        self::test($sm, $factoryClass, $resultClass);
    }

    /**
     * Used to test factories that lookup ControllerManager implementation of ServiceLocator i.e.
     * for building all kind of 'controllers'.
     *
     * @param $factoryClass
     * @param $resultClass
     * @param $injectionMap
     */
    public static function testCreateServiceForCM($factoryClass, $resultClass, $injectionMap)
    {
        $cm = new ControllerManager(null);
        $sm = new ServiceManager();
        $sm->setAllowOverride(true);
        $cm->setServiceLocator($sm);
        self::fillContainer($sm, $injectionMap);
        self::test($cm, $factoryClass, $resultClass);
    }

    /**
     * Used to test factories that lookup PluginManager implementation of ServiceLocator i.e.
     * for building all kind of 'controllers'.
     *
     * @param $factoryClass
     * @param $resultClass
     * @param $injectionMap
     */
    public static function testCreateServiceWithPluginManager($factoryClass, $resultClass, $injectionMap)
    {
        $pm = new PluginManager();
        $sm = new ServiceManager();
        $sm->setAllowOverride(true);
        $pm->setServiceLocator($sm);
        self::fillContainer($sm, $injectionMap);
        self::test($pm, $factoryClass, $resultClass);
    }

    private static function fillContainer($container, $injectionMap)
    {
        $sm = $container;
        foreach ($injectionMap as $key => $val) {
            if (is_callable($val)) {
                $sm->setService($key, $val());
            } elseif (interface_exists($val) || class_exists($val)) {
                // convenience shortcut - if there is no key, take value as key
                if (is_int($key)) {
                    $key = $val;
                }
                $sm->setService($key, XMock::of($val));
            } else {
                $sm->setService($key, $val);
            }
        }
    }

    private static function test($container, $factoryClass, $resultClass)
    {
        \PHPUnit_Framework_Assert::assertInstanceOf(
            $resultClass,
            (new $factoryClass())->createService($container)
        );
    }
}
