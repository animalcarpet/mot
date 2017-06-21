<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestSupportAccessTokenManager;
use Zend\Json\Json;
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

    //TODO please, delete this after removing empty calls
    public function processPostData(RequestInterface $request)
    {
        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            $data = $this->decodeJsonIfNotEmpty($request->getContent());
        } else {
            $data = $request->getPost()->toArray();
        }

        return $this->create($data);
    }

    //TODO please, delete this after removing empty calls
    protected function processBodyContent($request)
    {
        $content = $request->getContent();

        // JSON content? decode and return it.
        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            return $this->decodeJsonIfNotEmpty($request->getContent());
        }

        parse_str($content, $parsedParams);

        // If parse_str fails to decode, or we have a single element with empty value
        if (!is_array($parsedParams) || empty($parsedParams)
            || (1 == count($parsedParams) && '' === reset($parsedParams))
        ) {
            return $content;
        }

        return $parsedParams;
    }

    //TODO please, delete this after removing empty calls
    private function decodeJsonIfNotEmpty($content)
    {
        if(!empty($content)) {
            return Json::decode($content, $this->jsonDecodeType);
        } else {
            return null;
        }
    }
}
