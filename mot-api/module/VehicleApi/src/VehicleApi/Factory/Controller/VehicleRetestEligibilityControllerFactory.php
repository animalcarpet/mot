<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace VehicleApi\Factory\Controller;

use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;
use VehicleApi\Controller\VehicleRetestEligibilityController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create instance of controller VehicleRetestEligibilityController
 * Class VehicleRetestEligibilityControllerFactory.
 */
class VehicleRetestEligibilityControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return VehicleRetestEligibilityController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceLocatorInterface $sl */
        $sl = $controllerManager->getServiceLocator();

        /** @var RetestEligibilityValidator $validator */
        $validator = $sl->get(RetestEligibilityValidator::class);

        return new VehicleRetestEligibilityController($validator);
    }
}
