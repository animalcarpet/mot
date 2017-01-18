<?php

namespace DvsaMotApiTest\Service\Validator;

use Api\Check\CheckMessage;
use Api\Check\Severity;
use CensorApi\Service\CensorService;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Dto\ReplacementCertificateDraftChangeDTO;
use DvsaMotApi\Service\Validator\ReplacementCertificateDraftChangeValidator;
use DvsaMotApiTest\Factory\MotTestObjectsFactory;
use PHPUnit_Framework_TestCase;

/**
 * Class ReplacementCertificateDraftChangeValidatorTest
 */
class ReplacementCertificateDraftChangeValidatorTest extends PHPUnit_Framework_TestCase
{

    private $censorService;

    private function getValidator()
    {
        $this->censorService = XMock::of(CensorService::class);

        $serviceLocator = Bootstrap::getServiceManager();
        $serviceLocator->setAllowOverride(true);
        $serviceLocator->setService('CensorService', $this->censorService);

        return new ReplacementCertificateDraftChangeValidator(
            $serviceLocator->get('CensorService')
        );
    }

    /**
     * @param bool $bool
     * @throws \Exception
     */
    private function setCensorServiceReturnValue($bool = true)
    {
        $this->censorService = XMock::of(CensorService::class);
        $this->censorService->expects($this->any())
                            ->method('containsProfanity')
                            ->will($this->returnValue($bool));
    }

    public function testValidateGivenValidChangeShouldReturnNoErrors()
    {
        $this->setCensorServiceReturnValue(true);

        $currentDate = date('Y-m-d', strtotime('+1 day'));

        $change = (new ReplacementCertificateDraftChangeDTO())
            ->setPrimaryColour(4)->setSecondaryColour(5)
            ->setVin("2343434")->setVrm("VRM")
            ->setVtsSiteNumber("gegreg")
            ->setReasonForDifferentTester("24344")
            ->setOdometerReading(123, OdometerUnit::MILES, OdometerReadingResultType::OK)
            ->setReasonForReplacement("fwegreg")
            ->setMake(4)->setModel(44)
            ->setExpiryDate($currentDate)
            ->setCountryOfRegistration(5);

        $result = $this->getValidator()->validate($change);

        $this->assertTrue($result->isEmpty(), "Validation result contains unexpected errors: $result");
    }

    public function testValidateGivenEmptyChangeShouldReturnError()
    {
        $draft = new ReplacementCertificateDraftChangeDTO();

        $result = $this->getValidator()->validate($draft);

        $this->assertNotEmpty($result->getMessagesOfSeverity(Severity::ERROR));
    }

    public function testValidateGivenInvalidChangeShouldReturnAdequateErrors()
    {
        // message filter predicate to find messages for a given field
        $forField = function ($field) {
            return function (CheckMessage $m) use (&$field) {
                return $m->getField() === $field && $m->getSeverity() === Severity::ERROR;
            };
        };
        $change = (new ReplacementCertificateDraftChangeDTO())
            ->setPrimaryColour("gerg")
            ->setSecondaryColour("gerge")
            ->setVin("")
            ->setVrm("")
            ->setVtsSiteNumber("")
            ->setReasonForDifferentTester("")
            ->setOdometerReading("INVALID", OdometerUnit::MILES, OdometerReadingResultType::OK)
            ->setReasonForReplacement("")
            ->setMake("gerg")
            ->setModel("gerg")
            ->setExpiryDate("2014-13-01")
            ->setCountryOfRegistration("5");

        $result = $this->getValidator()->validate($change);

        $errorFields = [
            "vin", "vrm", "odometerReading.value", "expiryDate", "countryOfRegistration",
            "reasonForReplacement", "reasonForDifferentTester"
        ];
        foreach ($errorFields as $field) {
            $this->assertCount(
                1, $result->filterMessages($forField($field)), "No error messages for $field though expected"
            );
        }
    }
}
