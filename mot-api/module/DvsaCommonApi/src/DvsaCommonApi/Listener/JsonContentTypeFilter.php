<?php

namespace DvsaCommonApi\Listener;

use DvsaCommon\Utility\HeaderUtils;
use DvsaCommonApi\Service\Exception\ServiceException;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;

class JsonContentTypeFilter extends AbstractListenerAggregate
{
    const HTTP_CODE_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_CONTENT_TYPE_APPLICATION_JSON = 'application/json';

    public function attach(EventManagerInterface $eventManager)
    {
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, [$this, 'filter'], 9999999);
    }

    /**
     * Filters requests to make sure they have correct application/json content type.
     *
     * @param MvcEvent $event
     */
    public function filter(MvcEvent $event)
    {
        /** @var Request $request */
        $request = $event->getRequest();

        if (in_array(strtolower($request->getMethod()), ['post', 'put', 'patch'])
            && HeaderUtils::getContentType() !== self::HTTP_CONTENT_TYPE_APPLICATION_JSON
        ) {
            $exc = new ServiceException(null);
            $exc->addError('Unsupported media type', self::HTTP_CODE_UNSUPPORTED_MEDIA_TYPE);

            $event->setViewModel($exc->getJsonModel());
            $event->getResponse()->setStatusCode(self::HTTP_CODE_UNSUPPORTED_MEDIA_TYPE);
            $event->stopPropagation();
        }
    }
}
