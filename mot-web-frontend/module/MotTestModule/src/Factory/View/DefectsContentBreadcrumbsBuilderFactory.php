<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\View;

use Dvsa\Mot\Frontend\MotTestModule\View\DefectsContentBreadcrumbsBuilder;
use Zend\Mvc\Router\RouteStackInterface as Router;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creating DefectsContentBreadcrumbsBuilder instances.
 */
class DefectsContentBreadcrumbsBuilderFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return DefectsContentBreadcrumbsBuilder
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var Router $router */
        $router = $serviceLocator->get('Router');

        return new DefectsContentBreadcrumbsBuilder($router);
    }
}