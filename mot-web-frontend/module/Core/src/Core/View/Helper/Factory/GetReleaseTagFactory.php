<?php

namespace Core\View\Helper\Factory;

use Core\View\Helper\GetReleaseTag;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GetReleaseTagFactory implements FactoryInterface
{
    /**
     * This ViewHelper service will check for the following variable in application.config.php
     * footer_can_render_release_tag
     * The default is FALSE.
     *
     * @param ServiceLocatorInterface $viewHelperServiceLocator
     *
     * @return GetReleaseTag
     */
    public function createService(ServiceLocatorInterface $viewHelperServiceLocator)
    {
        $sl = $viewHelperServiceLocator->getServiceLocator();
        $appConfig = $sl->get('ApplicationConfig');
        $config = $sl->get('config');

        $releaseTag = '';
        if (isset($config['release_tag'])) {
            $releaseTag = $config['release_tag'];
        }

        // Check ENV
        $canRenderReleaseTagName = false;
        if (isset($appConfig['footer_can_render_release_tag'])) {
            $canRenderReleaseTagName = $appConfig['footer_can_render_release_tag'];
        }

        $helper = new GetReleaseTag($releaseTag);
        $helper->setCanRenderReleaseTagName($canRenderReleaseTagName);

        return $helper;
    }
}
