<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Listener;

use Dvsa\Mot\Frontend\AuthenticationModule\Controller\LogoutController;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use DvsaApplicationLogger\TokenService\TokenServiceInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Http\Response;
use Zend\Log\LoggerInterface;
use Zend\Mvc\MvcEvent;

/**
 * WebAuthenticationListener.
 */
class WebAuthenticationListener
{
    private $authenticationService;
    private $tokenService;
    private $logger;

    /**
     * @param AuthenticationService $authenticationService
     * @param TokenServiceInterface $tokenService
     * @param LoggerInterface       $logger
     */
    public function __construct(
        AuthenticationService $authenticationService,
        TokenServiceInterface $tokenService,
        LoggerInterface $logger
    ) {
        $this->authenticationService = $authenticationService;
        $this->tokenService = $tokenService;
        $this->logger = $logger;
    }

    /**
     * @param \Zend\Mvc\MvcEvent $event
     *
     * @return \Zend\Http\Response
     */
    public function __invoke(MvcEvent $event)
    {
        $shouldAuthenticate = true;
        $route = $event->getRequest()->getUri()->getPath();
        foreach (self::getWhitelist() as $preg) {
            if (preg_match($preg, $route) > 0) {
                $shouldAuthenticate = false;
                break;
            }
        }

        if ($shouldAuthenticate && $this->authenticationService->hasIdentity()) {
            /** @var Identity $identity */
            $identity = $this->authenticationService->getIdentity();
            $shouldAuthenticate = $identity->getAccessToken() !== $this->tokenService->getToken();
        }

        if ($shouldAuthenticate) {
            /** @var Result $result */
            $result = $this->authenticationService->authenticate();
            if ($result->getCode() !== Result::SUCCESS) {
                $logoutUrl = $event->getRouter()->assemble([], ['name' => LogoutController::ROUTE_LOGOUT]);
                /** @var Response $response */
                $response = $event->getResponse();
                $response->getHeaders()->addHeaderLine('Location', $logoutUrl);
                $response->setStatusCode(302);
                $event->stopPropagation();

                return $response;
            }
        }
    }

    /**
     * @return array
     */
    public static function getWhitelist()
    {
        return [
            '@^/(login|logout)$@',
            '@^/forgotten-password(?!/update$)(.*)@',
            '@^/account/register@',
            '@^/account/register/(.*)@',
            '@^/your-profile/(.*)/change-password@',
            '@^/survey/[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}@', // survey/<uuid>
            '@^/survey/[[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}/thanks@', // survey/<uuid>/thanks
        ];
    }
}
