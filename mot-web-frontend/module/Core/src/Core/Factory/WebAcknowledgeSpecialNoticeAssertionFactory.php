<?php

namespace Core\Factory;

use Core\Authorisation\Assertion\WebAcknowledgeSpecialNoticeAssertion;
use DvsaCommon\Auth\Assertion\AcknowledgeSpecialNoticeAssertion;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class WebAcknowledgeSpecialNoticeAssertionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new WebAcknowledgeSpecialNoticeAssertion(
            new AcknowledgeSpecialNoticeAssertion(
                $serviceLocator->get('AuthorisationService')
            )
        );
    }
}
