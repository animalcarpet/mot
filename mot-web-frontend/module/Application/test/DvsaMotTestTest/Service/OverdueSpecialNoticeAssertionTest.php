<?php

namespace DvsaMotTestTest\Service;

use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaMotTest\Service\OverdueSpecialNoticeAssertion;

class OverdueSpecialNoticeAssertionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $overdueSpecialNotices
     * @param array $authorisationsForTestingMot
     * @param bool  $expectedResult
     *
     * @dataProvider dataProvider
     */
    public function testCanPerformTest(array $overdueSpecialNotices, array $authorisationsForTestingMot, $expectedResult)
    {
        $overdueSpecialNotice = new OverdueSpecialNoticeAssertion($overdueSpecialNotices, $authorisationsForTestingMot);
        $this->assertEquals($expectedResult, $overdueSpecialNotice->canPerformTest());
    }

    /**
     * @param array $overdueSpecialNotices
     * @param array $authorisationsForTestingMot
     * @param bool  $notThrowException
     *
     * @dataProvider dataProvider
     */
    public function testAssertPerformTest(array $overdueSpecialNotices, array $authorisationsForTestingMot, $notThrowException)
    {
        if ($notThrowException === false) {
            $this->setExpectedException(UnauthorisedException::class, OverdueSpecialNoticeAssertion::OVERDUE_SPECIAL_NOTICES_ERROR);
        }

        $overdueSpecialNotice = new OverdueSpecialNoticeAssertion($overdueSpecialNotices, $authorisationsForTestingMot);
        $overdueSpecialNotice->assertPerformTest();
    }

    public function dataProvider()
    {
        return [
          [$this->getSpecialNotices([0, 0, 0, 0, 0, 0]), $this->getAuthorisationsForTestingMot(AuthorisationForTestingMotStatusCode::QUALIFIED), true],
          [$this->getSpecialNotices([1, 1, 1, 1, 1, 1]), $this->getAuthorisationsForTestingMot(AuthorisationForTestingMotStatusCode::QUALIFIED), false],
          [$this->getSpecialNotices([0, 1, 1, 1, 1, 1]), $this->getAuthorisationsForTestingMot(AuthorisationForTestingMotStatusCode::QUALIFIED), true],
          [$this->getSpecialNotices([1, 0, 0, 0, 0, 0]), $this->getAuthorisationsForTestingMot(AuthorisationForTestingMotStatusCode::QUALIFIED), true],
          [$this->getSpecialNotices([0, 0, 0, 0, 0, 0]), $this->getAuthorisationsForTestingMot(AuthorisationForTestingMotStatusCode::SUSPENDED), false],
          [$this->getSpecialNotices([1, 1, 1, 1, 1, 1]), $this->getAuthorisationsForTestingMot(AuthorisationForTestingMotStatusCode::SUSPENDED), false],
          [$this->getSpecialNotices([0, 1, 1, 1, 1, 1]), $this->getAuthorisationsForTestingMot(AuthorisationForTestingMotStatusCode::SUSPENDED), false],
          [$this->getSpecialNotices([1, 0, 0, 0, 0, 0]), $this->getAuthorisationsForTestingMot(AuthorisationForTestingMotStatusCode::SUSPENDED), false],
          [$this->getSpecialNotices([0, 0, 0, 0, 0, 0]), [], false],
        ];
    }

    private function getSpecialNotices(array $values)
    {
        return array_combine(VehicleClassCode::getAll(), $values);
    }

    private function getAuthorisationsForTestingMot($statusCode)
    {
        $authorisationsForTestingMot = [];
        foreach (VehicleClassCode::getAll() as $code) {
            $authorisationsForTestingMot[] = [
                'vehicleClassCode' => $code,
                'statusCode' => $statusCode,
            ];
        }

        return $authorisationsForTestingMot;
    }
}
