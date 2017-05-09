<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestSupportAccessTokenManager;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use Zend\Log\Logger;

abstract class BaseTestSupportRestfulController extends AbstractRestfulController
{
    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->getServiceLocator()->get('ApplicationLog');
    }

    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        $a = parent::dispatch($request, $response);
        $tokenManager = $this->getServiceLocator()->get(TestSupportAccessTokenManager::class);
        /* @var TestSupportAccessTokenManager $tokenManager */
        // TODO to be considered after ldap update is reworked to be faster
        //$tokenManager->invalidateTokens();
        return $a;
    }
}
