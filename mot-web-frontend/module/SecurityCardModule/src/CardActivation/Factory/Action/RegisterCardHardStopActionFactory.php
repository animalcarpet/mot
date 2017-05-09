<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Action;

use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardHardStopAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardHardStopCondition;
use DvsaCommon\Configuration\MotConfig;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RegisterCardHardStopActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotConfig $config */
        $config = $serviceLocator->get(MotConfig::class);
        $condition = $serviceLocator->get(RegisterCardHardStopCondition::class);

        return new RegisterCardHardStopAction($condition, $config->get('helpdesk'));
    }
}
