<?php

namespace Account\Service;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\Http\Header\SetCookie;
use Zend\Http\PhpEnvironment\Response;
use Zend\Http\PhpEnvironment\Request;;

class LockedAccountCookieService implements AutoWireableInterface
{
    const COOKIE_NAME = "locked_account";
    const COOKIE_EXPIRE_TIME_IN_MIN = 30;
    const COOKIE_EXPIRE_TIME_IN_SEC = self::COOKIE_EXPIRE_TIME_IN_MIN * 60;

    private $response;
    private $request;

    public function __construct(Response $response, Request $request)
    {
        $this->response = $response;
        $this->request = $request;
    }

    public function setCookie()
    {
        $time = DateUtils::nowAsUserDateTime()->getTimestamp();

        $expireTime = $time + self::COOKIE_EXPIRE_TIME_IN_SEC;

        $cookie = new SetCookie();
        $cookie
            ->setPath("/")
            ->setExpires($expireTime)
            ->setName(self::COOKIE_NAME)
            ->setValue($expireTime);

        $this->response->getHeaders()->addHeader($cookie);
    }

    public function getTimeLeftInMin(): int
    {
        $cookie = $this->request->getCookie()[self::COOKIE_NAME];

        if ($cookie === null) {
            return 0;
        }

        $cookieDateTime = (new \DateTime())->setTimestamp($cookie);

        $seconds = DateUtils::getTimeDifferenceInSeconds(
            DateUtils::toUserTz($cookieDateTime),
            DateUtils::nowAsUserDateTime()
        );

        $timeLeft = (int) ceil($seconds / 60);
        return min($timeLeft, self::COOKIE_EXPIRE_TIME_IN_MIN);
    }
}
