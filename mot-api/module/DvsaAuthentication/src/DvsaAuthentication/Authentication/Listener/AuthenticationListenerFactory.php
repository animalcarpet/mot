<?php

namespace DvsaAuthentication\Authentication\Listener;

use Dvsa\Mot\AuditApi\Service\HistoryAuditService;
use Zend\Log\LoggerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationListenerFactory implements FactoryInterface
{
    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $sl
     *
     * @return ApiAuthenticationListener
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        /** @var \Zend\Authentication\AuthenticationService $auth */
        $auth = $sl->get('DvsaAuthenticationService');

        /** @var LoggerInterface $logger */
        $logger = $sl->get('Application\Logger');

        /** @var array $config */
        $config = $sl->get('config');

        $whitelist = isset($config['dvsa_authentication']['whitelist'])
            ? $config['dvsa_authentication']['whitelist']
            : [];

        $historyAuditService = $sl->get(HistoryAuditService::class);

        $listener = new ApiAuthenticationListener($auth, $logger, $whitelist, $historyAuditService);

        return $listener;
    }
}
