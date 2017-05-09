<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModule\Service;

use Dvsa\OpenAM\Exception\OpenAMClientException;
use Dvsa\OpenAM\OpenAMClient;
use Dvsa\OpenAM\OpenAMClientInterface;
use DvsaCommon\HttpRestJson\Client;
use Zend\Log\LoggerInterface;
use Zend\Session\ManagerInterface;
use Zend\Session\SessionManager;

/**
 * Performs necessary logic to log user out of the system.
 */
class WebLogoutService
{
    /**
     * @var OpenAMClient
     */
    private $client;

    /**
     * WebAuthenticationCookieService $cookieService.
     * */
    private $cookieService;

    /**
     * @var ManagerInterface
     */
    private $sessionManager;

    /**
     * @param OpenAMClientInterface          $client
     * @param WebAuthenticationCookieService $cookieService
     * @param ManagerInterface               $sessionManager
     * @param LoggerInterface                $logger
     */
    public function __construct(
        OpenAMClientInterface $client,
        WebAuthenticationCookieService $cookieService,
        ManagerInterface $sessionManager,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->cookieService = $cookieService;
        $this->sessionManager = $sessionManager;
        $this->logger = $logger;
    }

    public function logout()
    {
        $token = $this->cookieService->getToken();
        if (!is_null($token)) {
            $this->logoutOpenAMSession($token);
            $this->cookieService->tearDownCookie();
        }

        $this->sessionManager->destroy(['clearStorage' => true]);
    }

    /**
     * @param $token
     *
     * @retrun null
     */
    private function logoutOpenAMSession($token)
    {
        try {
            $this->client->logout($token);
        } catch (OpenAMClientException $e) {
            $this->logger->err(sprintf('Exception thrown while using OpenAM REST client to log out user with token "%s": "%s',
                $token, $e->getMessage()));
        }
    }
}
