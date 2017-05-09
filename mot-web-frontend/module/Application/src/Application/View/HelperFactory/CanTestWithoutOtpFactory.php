<?php

namespace Application\View\HelperFactory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\View\Helper\CanTestWithoutOtp;
use Application\Service\CanTestWithoutOtpService;

class CanTestWithoutOtpFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $viewHelperServiceLocator)
    {
        $sl = $viewHelperServiceLocator->getServiceLocator();

        $canTestWithoutOtpService = $sl->get(CanTestWithoutOtpService::class);

        return new CanTestWithoutOtp($canTestWithoutOtpService);
    }
}
