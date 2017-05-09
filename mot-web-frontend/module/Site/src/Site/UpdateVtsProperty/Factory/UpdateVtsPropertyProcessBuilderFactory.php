<?php

namespace Site\UpdateVtsProperty\Factory;

use DvsaClient\Mapper\SiteMapper;
use Site\UpdateVtsProperty\Process\UpdateVtsAddressProcess;
use Site\UpdateVtsProperty\Process\UpdateVtsClassesReviewProcess;
use Site\UpdateVtsProperty\Process\UpdateVtsCountryProcess;
use Site\UpdateVtsProperty\Process\UpdateVtsEmailProcess;
use Site\UpdateVtsProperty\Process\UpdateVtsNameProcess;
use Site\UpdateVtsProperty\Process\UpdateVtsPhoneProcess;
use Site\UpdateVtsProperty\Process\UpdateVtsStatusProcess;
use Site\UpdateVtsProperty\Process\UpdateVtsTypeProcess;
use Site\UpdateVtsProperty\UpdateVtsPropertyProcessBuilder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UpdateVtsPropertyProcessBuilderFactory implements FactoryInterface
{
    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return UpdateVtsPropertyProcessBuilder
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $processBuilder = new UpdateVtsPropertyProcessBuilder(
            $serviceLocator->get(SiteMapper::class)
        );
        $processBuilder->add($serviceLocator->get(UpdateVtsNameProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateVtsClassesReviewProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateVtsCountryProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateVtsStatusProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateVtsTypeProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateVtsPhoneProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateVtsAddressProcess::class));
        $processBuilder->add($serviceLocator->get(UpdateVtsEmailProcess::class));

        return $processBuilder;
    }
}
