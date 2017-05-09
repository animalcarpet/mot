<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotTest\Factory\Controller;

use Application\View\Helper\AuthorisationHelper;
use Core\Service\MotEventManager;
use DvsaCommon\ApiClient\MotTest\DuplicateCertificate\MotTestDuplicateCertificateApiResource;
use DvsaMotTest\Controller\MotTestController;
use DvsaMotTest\Model\OdometerReadingViewObject;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MotTestControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return MotTestController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        /** @var AuthorisationHelper $authService */
        $authService = $serviceLocator->get('authorisationHelper');

        $eventManager = $serviceLocator->get(MotEventManager::class);

        $odometerViewObject = new OdometerReadingViewObject();

        $duplicateCertificateApiResource = $serviceLocator->get(MotTestDuplicateCertificateApiResource::class);

        return new MotTestController(
            $authService,
            $eventManager,
            $odometerViewObject,
            $duplicateCertificateApiResource
        );
    }
}
