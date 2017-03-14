<?php

namespace DashboardTest\Factory\Security;

use Dashboard\Factory\Security\DashboardGuardFactory;
use Dashboard\Security\DashboardGuard;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class DashboardGuardFactoryTest
 */
class DashboardGuardFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            DashboardGuardFactory::class,
            DashboardGuard::class,
            [
                MotAuthorisationServiceInterface::class => MotAuthorisationServiceInterface::class,
            ]
        );
    }
}
