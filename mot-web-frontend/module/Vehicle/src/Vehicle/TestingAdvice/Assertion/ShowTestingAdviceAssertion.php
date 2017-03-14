<?php

namespace Vehicle\TestingAdvice\Assertion;

use Dvsa\Mot\ApiClient\Exception\ResourceNotFoundException;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Domain\MotTestType;
use Zend\Http\Response;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\RequestInterface;

class ShowTestingAdviceAssertion implements AutoWireableInterface
{
    private $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    public function isGranted($vehicleId, $motTestTypeCode)
    {
        if (!MotTestType::isStandard($motTestTypeCode)) {
            return false;
        }

        try {
            $this->vehicleService->getTestingAdvice($vehicleId);
            return true;
        } catch (ClientException $e) {
            if ($e->getCode() == Response::STATUS_CODE_404) {
                return false;
            }

            throw $e;
        }
    }
}
