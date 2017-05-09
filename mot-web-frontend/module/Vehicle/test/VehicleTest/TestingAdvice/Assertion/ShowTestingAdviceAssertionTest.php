<?php

namespace VehicleTest\TestingAdvice\Assertion;

use Dvsa\Mot\ApiClient\Resource\Item\VehicleTestingData\TestingAdvice;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonTest\TestUtils\XMock;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Vehicle\TestingAdvice\Assertion\ShowTestingAdviceAssertion;
use Zend\Http\Response;

class ShowTestingAdviceAssertionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider standardMotTestTypeCode
     */
    public function test_isGranted_returnsTrue_forStandardMotTestTypeCode($motTestTypeCode)
    {
        $vehicleService = XMock::of(VehicleService::class);
        $vehicleService->method('getTestingAdvice')->willReturn(new TestingAdvice());

        $assertion = new ShowTestingAdviceAssertion($vehicleService);
        $this->assertTrue($assertion->isGranted(1, $motTestTypeCode));
    }

    public function standardMotTestTypeCode()
    {
        return [
            [MotTestTypeCode::NORMAL_TEST],
            [MotTestTypeCode::RE_TEST],
            [MotTestTypeCode::MYSTERY_SHOPPER],
        ];
    }

    /**
     * @dataProvider standardMotTestTypeCode
     */
    public function test_isGranted_returnFalse_whenTestingAdviceNotFound($motTestTypeCode)
    {
        $vehicleService = XMock::of(VehicleService::class);
        $vehicleService->method('getTestingAdvice')->willThrowException($this->getClientException(Response::STATUS_CODE_404));

        $assertion = new ShowTestingAdviceAssertion($vehicleService);
        $this->assertFalse($assertion->isGranted(1, $motTestTypeCode));
    }

    public function test_isGranted_throwsException_whenExceptionStatusCodeNotEquals404()
    {
        $this->setExpectedException(ClientException::class);

        $vehicleService = XMock::of(VehicleService::class);
        $vehicleService->method('getTestingAdvice')->willThrowException($this->getClientException(Response::STATUS_CODE_500));

        $assertion = new ShowTestingAdviceAssertion($vehicleService);
        $this->assertFalse($assertion->isGranted(1, MotTestTypeCode::NORMAL_TEST));
    }

    /**
     * @dataProvider nonStandardMotTestTypeCode
     */
    public function test_isGranted_returnFalse_forNonStandardMotTestTypeCode($motTestTypeCode)
    {
        $vehicleService = XMock::of(VehicleService::class);
        $vehicleService->expects($this->exactly(0))->method('getTestingAdvice');

        $assertion = new ShowTestingAdviceAssertion($vehicleService);
        $this->assertFalse($assertion->isGranted(1, $motTestTypeCode));
    }

    public function nonStandardMotTestTypeCode()
    {
        $motTestTypeCodes = [];
        foreach (MotTestTypeCode::getAll() as $code) {
            if (!MotTestType::isStandard($code)) {
                $motTestTypeCodes[] = [$code];
            }
        }

        return $motTestTypeCodes;
    }

    private function getClientException($statusCode)
    {
        $response = XMock::of(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);

        return new ClientException('SWW', XMock::of(RequestInterface::class), $response);
    }
}
