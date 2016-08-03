<?php

namespace DvsaAuthentication\Authentication\Listener;

use DvsaAuthentication\Identity;
use Dvsa\Mot\AuditApi\Service\HistoryAuditService;
use Zend\Authentication\Adapter\AbstractAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventManager;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\Response;
use Zend\Log\LoggerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Console\RouteMatch as ConsoleRouteMatch;
use Zend\Mvc\Router\RouteMatch;
use Zend\View\Model\JsonModel;

/**
 * Class ApiAuthenticationListener. This listener will be applied to every http
 * request. It will check for authentication and will force a 401 to be
 * returned when an unauthenticated request is made.
 *
 * @package DvsaAuthentication\Authentication\Listener
 */
class ApiAuthenticationListener
{
    /**
     * @var AuthenticationService
     */
    private $authService;

    /**
     * @var array
     */
    private $whitelist = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var HistoryAuditService
     */
    private $historyAuditService;

    /**
     * @param \Zend\Authentication\AuthenticationService $authService
     * @param \Zend\Log\LoggerInterface                  $logger
     * @param                                            $whitelist
     */
    public function __construct(
        AuthenticationService $authService,
        LoggerInterface $logger,
        $whitelist = [],
        HistoryAuditService $historyAuditService
    ) {
        $this->authService = $authService;
        $this->whitelist = $whitelist;
        $this->logger = $logger;
        $this->historyAuditService = $historyAuditService;
    }

    /**
     * Check to see if a controller is in a whitelist.
     *
     * @param string $controller the controller name
     *
     * @return bool
     */
    private function isControllerOnWhitelist($controller)
    {
        return in_array($controller, $this->whitelist);
    }

    /**
     * Makes this class callable.
     *
     * @param MvcEvent $event
     *
     * @return \Zend\Http\PhpEnvironment\Response|bool
     */
    public function __invoke(MvcEvent $event)
    {
        // check we have a matching route before continuing
        $matches = $event->getRouteMatch();

        if ((!$matches instanceof RouteMatch) || ($matches instanceof ConsoleRouteMatch)) {
            return false;
        }

        // check controller is not whitelisted
        $controller = $matches->getParam('controller');
        if ($this->isControllerOnWhitelist($controller)) {
            $this->logger->debug(
                sprintf('%s controller is whitelisted', $controller)
            );
            return false;
        }

        $authenticateRes = $this->authService->authenticate();

        if (!$authenticateRes->isValid()) {

            /** @var \Zend\Http\PhpEnvironment\Response $response */
            $response = $event->getResponse();
            $response->setStatusCode(Response::STATUS_CODE_401);
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            $response->setContent($this->createJsonModel()->serialize());
            return $response;
        }

        $this->setKDD069SessionVars($authenticateRes->getIdentity());

        // we reach here if authentication is passed successfully or they
        // already have an identity
        $this->logger->debug('HTTP request authenticated successfully');

        return true;
    }

    /**
     * Creates a new JsonModel response.
     *
     * @param int    $code
     * @param string $message
     *
     * @return JsonModel
     */
    private function createJsonModel($code = 401, $message = 'Unauthorised')
    {
        return new JsonModel(
            [
                'errors' => [
                    'message' => $message,
                    'code'    => $code,
                ],
            ]
        );
    }

    protected function setKDD069SessionVars(Identity $identity)
    {
        $this->historyAuditService->setUser($identity->getPerson());
        $this->historyAuditService->execute();
    }
}
