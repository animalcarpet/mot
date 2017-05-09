<?php

namespace Core\Service;

use Zend\Http\PhpEnvironment\RemoteAddress as ZendRemoteAddress;
use DvsaCommon\Constants\Network;

class RemoteAddress
{
    /**
     * @var ZendRemoteAddress
     */
    private static $service;

    /**
     * @return string
     */
    public static function getIp()
    {
        $remoteAddress = self::getRemoteAddressService();
        $ip = $remoteAddress->getIpAddress();

        if (!$ip) {
            $ip = Network::DEFAULT_CLIENT_IP;
        }

        return $ip;
    }

    private static function getRemoteAddressService()
    {
        if (self::$service) {
            return self::$service;
        }

        $remoteAddress = new ZendRemoteAddress();
        $remoteAddress->setUseProxy(true);

        self::$service = $remoteAddress;

        return self::$service;
    }
}
