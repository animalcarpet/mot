<?php

namespace Organisation\UpdateAeProperty\Factory;

use Organisation\UpdateAeProperty\Process\CreateAePrincipalProcess;
use Organisation\UpdateAeProperty\Process\UpdateAeAreaOfficeProcess;
use Organisation\UpdateAeProperty\Process\UpdateAeBusinessTypeProcess;
use Organisation\UpdateAeProperty\Process\UpdateAeCorrespondenceAddressProcess;
use Organisation\UpdateAeProperty\Process\UpdateAeCorrespondenceEmailProcess;
use Organisation\UpdateAeProperty\Process\UpdateAeCorrespondencePhoneProcess;
use Organisation\UpdateAeProperty\Process\UpdateAeNameProcess;
use Organisation\UpdateAeProperty\Process\UpdateAeRegisteredAddressProcess;
use Organisation\UpdateAeProperty\Process\UpdateAeRegisteredEmailProcess;
use Organisation\UpdateAeProperty\Process\UpdateAeRegisteredPhoneProcess;
use Organisation\UpdateAeProperty\Process\UpdateAeStatusProcess;
use Organisation\UpdateAeProperty\Process\UpdateAeTradingNameProcess;
use Organisation\UpdateAeProperty\UpdateAePropertyProcessBuilder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UpdateAePropertyProcessBuilderFactory implements FactoryInterface
{
    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return UpdateAePropertyProcessBuilder
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $processBuilder = new UpdateAePropertyProcessBuilder();

        $processBuilder->add($serviceLocator->get(UpdateAeNameProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateAeTradingNameProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateAeBusinessTypeProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateAeAreaOfficeProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateAeRegisteredEmailProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateAeCorrespondenceEmailProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateAeRegisteredPhoneProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateAeCorrespondencePhoneProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateAeRegisteredAddressProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateAeCorrespondenceAddressProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateAeStatusProcess::class));
        $processBuilder->add($serviceLocator->get(CreateAePrincipalProcess::class));

        return $processBuilder;
    }
}
