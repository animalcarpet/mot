<?php

namespace MailerApi\Factory\Logic;

use MailerApi\Logic\UsernameCreator;
use MailerApi\Service\TemplateResolverService;
use MailerApi\Service\MailerService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserNameCreatorFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new UsernameCreator(
            $serviceLocator->get(MailerService::class),
            $serviceLocator->get(TemplateResolverService::class),
            $serviceLocator->get('config')
        );
    }
}
