<?php

namespace DvsaAuthentication\Service;

use DvsaApplicationLogger\TokenService\TokenServiceInterface;
use DvsaCommon\Auth\Http\AuthorizationBearer;
use InvalidArgumentException;
use Zend\Console\Request as ConsoleRequest;
use Zend\Http\Request as HttpRequest;
use Zend\Stdlib\RequestInterface;

class ApiTokenService implements TokenServiceInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * ApiTokenService constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->parseToken();
    }

    /**
     * @return null|string
     */
    public function parseToken()
    {
        $token = null;
        if ($this->request instanceof ConsoleRequest) {
            return $token;
        }

        /** @var HttpRequest $header */
        $header = $this->request->getHeader(AuthorizationBearer::FIELD_NAME);
        if ($header) {
            try {
                $authHeader = AuthorizationBearer::fromString($header->toString());
                $token = $authHeader->getToken();
            } catch (InvalidArgumentException $e) {
            }
        }

        return $token;
    }
}
