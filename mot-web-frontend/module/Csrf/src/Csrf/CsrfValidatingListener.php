<?php

namespace Csrf;

use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;

/**
 * Listener that inspects request payload in search for
 * token field and compares it with a server generated
 * one provided by the service.
 * This listener should be hooked in to request-response lifecycle as a preDispatch listener
 * ( DISPATCH event with high priority )
 * If there is no match, then InvalidCsrfException is thrown so it has to be caught later during the lifecycle
 * and properly handled.
 */
class CsrfValidatingListener
{
    private function isEnabled(ServiceManager $sm)
    {
        $isEnabled = $sm->get('config')['csrf']['enabled'];

        return $isEnabled;
    }

    public function validate(MvcEvent $evt)
    {
        if ($evt->getError() || $evt->getRouteMatch()->getMatchedRouteName() == 'login') {
            return;
        }

        if ($evt->getRequest()->isPost()) {
            $sm = $evt->getApplication()->getServiceManager();
            if (!$this->isEnabled($sm)) {
                return;
            }
            /** @var CsrfSupport $csrfSupport */
            $csrfSupport = $sm->get('CsrfSupport');
            $token = $csrfSupport->getCsrfToken();
            $requestToken = $evt->getRequest()->getPost()->offsetGet(CsrfConstants::REQ_TOKEN);
            if ($token !== $requestToken) {
                throw new InvalidCsrfException();
            }
        }
    }
}
