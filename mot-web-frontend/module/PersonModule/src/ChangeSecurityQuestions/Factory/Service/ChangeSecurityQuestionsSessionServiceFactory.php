<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Service;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;
use DvsaClient\MapperFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

class ChangeSecurityQuestionsSessionServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sessionContainer =  new Container(ChangeSecurityQuestionsSessionService::UNIQUE_KEY);

        $mapperFactory = $serviceLocator->get(MapperFactory::class);

        return new ChangeSecurityQuestionsSessionService(
            $sessionContainer,
            $mapperFactory
        );
    }
}