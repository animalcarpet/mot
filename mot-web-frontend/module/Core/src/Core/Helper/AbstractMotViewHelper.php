<?php

namespace Core\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;

/**
 * Extra shared functionality common to all ViewHelpers.
 */
class AbstractMotViewHelper extends AbstractHelper implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /** @return ServiceLocatorInterface */
    protected function getServiceLayerServiceLocator()
    {
        /*
         * This odd method call is required - view helpers don't
         * have the usual ServiceLocator, you need to call getServiceLocator again.
         */
        return $this->getServiceLocator()->getServiceLocator();
    }
}
