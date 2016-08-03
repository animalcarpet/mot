<?php
namespace PersonApiTest\Service\Validator;

use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\Dto\MotTestingAnnualCertificateDto;
use DvsaCommonApi\Service\Exception\BadRequestException;
use PersonApi\Service\Validator\MotTestingAnnualCertificateValidator;

class MotTestingAnnualCertificateValidatorTest extends \PHPUnit_Framework_TestCase
{
    const CERTIFICATE_NUMBER = "CERTIFICATE NUMBER";
    /** @var  MotTestingAnnualCertificateValidator */
    private $sut;

    public function setUp()
    {
        $this->sut = new MotTestingAnnualCertificateValidator();
    }

    /**
     * @dataProvider dataProviderTestValidate
     */
    public function testValidate($certificateNumber, $examDate, $score, $expectedException)
    {
        $dto = new MotTestingAnnualCertificateDto();
        $dto->setCertificateNumber($certificateNumber)
            ->setExamDate($examDate)
            ->setScore($score);

        if($expectedException == true) {
            $this->setExpectedException(BadRequestException::class);
        }
        $this->sut->validate($dto);
    }

    public function dataProviderTestValidate()
    {
        $tomorrow = new \DateTime("tomorrow");
        $today = new \DateTime();

        return [
            //correct data - [OK]
            [self::CERTIFICATE_NUMBER, $today, 10, false],
            //missing certificate number - [EXCEPTION]
            [null, $today, 10, true],
            //date in past - [EXCEPTION]
            [self::CERTIFICATE_NUMBER, $tomorrow, 10, true],
            //score below 0 - [EXCEPTION]
            [self::CERTIFICATE_NUMBER, $today, -1, true],
            //score above 100 - [EXCEPTION]
            [self::CERTIFICATE_NUMBER, $today, 101, true],
            //score is not integer - [EXCEPTION]
            [self::CERTIFICATE_NUMBER, $today, "score", true],
            [self::CERTIFICATE_NUMBER, $today, "", true],
            [self::CERTIFICATE_NUMBER, $today, [10], true],
            [self::CERTIFICATE_NUMBER, $today, "010", true],
        ];
    }
}