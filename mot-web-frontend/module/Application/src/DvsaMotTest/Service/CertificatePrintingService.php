<?php

namespace DvsaMotTest\Service;

use DvsaCommon\HttpRestJson\Client as RestClient;

class CertificatePrintingService
{
    /**
     * @var RestClient
     */
    protected $restClient;

    /**
     * @param RestClient $client
     */
    public function __construct(RestClient $client)
    {
        $this->restClient = $client;
    }

    /**
     * @param string $certificateUrl
     */
    public function getPdf($certificateUrl)
    {
        $result = $this->restClient->getPdf($certificateUrl); // @todo - add some pdf parsing checks in client
        return $result;
    }
}
