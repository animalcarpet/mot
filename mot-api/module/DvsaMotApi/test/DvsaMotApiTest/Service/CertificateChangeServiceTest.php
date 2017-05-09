<?php

namespace DvsaMotApiTest\Service;

use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\CertificateChangeDifferentTesterReason;
use DvsaEntities\Repository\CertificateChangeReasonRepository;
use DvsaMotApi\Service\CertificateChangeService;
use DvsaMotApi\Service\MotTestSecurityService;

/**
 * Class CertificateChangeServiceTest.
 */
class CertificateChangeServiceTest extends AbstractServiceTestCase
{
    private $reasonRepository;
    private $authService;
    private $motTestSecurityService;

    public function setUp()
    {
        $this->reasonRepository = XMock::of(CertificateChangeReasonRepository::class);
        $this->authService = XMock::of(\DvsaAuthorisation\Service\AuthorisationServiceInterface::class);
        $this->motTestSecurityService = XMock::of(MotTestSecurityService::class);
    }

    public function testGetAllAsGivenValidDataShouldReturnCorrectData()
    {
        //given
        $reasonsFound = [
            self::reasonOf('CODE1', 'description1'),
            self::reasonOf('CODE2', 'description2'),
        ];
        $this->reasonRepository->expects($this->any())->method('findAll')->will($this->returnValue($reasonsFound));

        //when
        $result = $this->createService()->getDifferentTesterReasonsAsArray();

        //then
        $expectedDescriptionData = [['code' => 'CODE1', 'description' => 'description1'],
                                    ['code' => 'CODE2', 'description' => 'description2'], ];
        $this->assertEquals($expectedDescriptionData, $result, 'Actual data does not match expectations');
    }

    private function createService()
    {
        return new CertificateChangeService(
            $this->reasonRepository,
            $this->authService,
            $this->motTestSecurityService
        );
    }

    private static function reasonOf($code, $description)
    {
        return (new CertificateChangeDifferentTesterReason())->setCode($code)->setDescription($description);
    }
}
