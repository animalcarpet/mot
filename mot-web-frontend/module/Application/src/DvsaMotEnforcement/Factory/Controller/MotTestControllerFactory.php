<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotEnforcement\Factory\Controller;

use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaMotEnforcement\Controller\MotTestController;
use DvsaMotTest\Model\OdometerReadingViewObject;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create MotTestSearchController.
 */
class MotTestControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return MotTestController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator     = $controllerManager->getServiceLocator();
        $paramObfuscator    = $serviceLocator->get(ParamObfuscator::class);
        $odometerViewObject = new OdometerReadingViewObject();

        return new MotTestController($paramObfuscator, $odometerViewObject);
    }
}
