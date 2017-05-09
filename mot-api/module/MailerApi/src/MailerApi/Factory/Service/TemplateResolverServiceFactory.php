<?php

namespace MailerApi\Factory\Service;

use MailerApi\Service\TemplateResolverService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Resolver\AggregateResolver;

class TemplateResolverServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new TemplateResolverService(
            new AggregateResolver()
        );
    }
}
