<?php

namespace MailerApi\Factory\Service;

use DvsaCommon\Validator\EmailAddressValidator;
use MailerApi\Validator\MailerValidator;
use DvsaMotApi\Service\UserService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use MailerApi\Service\MailerService;

class MailerServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new MailerService(
            $serviceLocator->get('config'),
            $serviceLocator->get('Application\Logger'),
            new MailerValidator($serviceLocator->get(UserService::class)),
            new EmailAddressValidator()
        );
    }
}
