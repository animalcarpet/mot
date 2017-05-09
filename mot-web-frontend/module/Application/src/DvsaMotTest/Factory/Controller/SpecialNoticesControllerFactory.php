<?php

namespace DvsaMotTest\Factory\Controller;

use Application\Data\ApiPersonalDetails;
use DvsaMotTest\Controller\SpecialNoticesController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Core\Authorisation\Assertion\WebAcknowledgeSpecialNoticeAssertion;

/**
 * Create SpecialNoticesController.
 */
class SpecialNoticesControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return SpecialNoticesController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        $markdown = $serviceLocator->get('MaglMarkdown\MarkdownService');

        return new SpecialNoticesController(
            $markdown,
            $serviceLocator->get(WebAcknowledgeSpecialNoticeAssertion::class),
            $serviceLocator->get(ApiPersonalDetails::class)
        );
    }
}
