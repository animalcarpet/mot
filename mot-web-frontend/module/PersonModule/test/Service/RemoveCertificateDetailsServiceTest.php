<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\Service;

use Dvsa\Mot\Frontend\PersonModule\Service\RemoveCertificateDetailsService;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaClient\Mapper\QualificationDetailsMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\CertificatesBreadcrumbs;
use DvsaCommonTest\TestUtils\XMock;
use Core\Action\ViewActionResult;
use Core\Action\RedirectToRoute;
use Application\Data\ApiPersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuard;
use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;

class RemoveCertificateDetailsServiceTest extends \PHPUnit_Framework_TestCase
{
    const BACK_URL = '/back-to-prev-page';

    /** @var QualificationDetailsMapper */
    private $qualificationDetailsMapper;
    /** @var MotAuthorisationServiceInterface */
    private $authorisationService;
    /** @var CertificatesBreadcrumbs */
    private $breadcrumbs;
    /** @var ApiPersonalDetails */
    private $apiPersonalDetails;
    /** @var ContextProvider */
    private $contextProvider;
    /** @var PersonProfileGuardBuilder */
    private $personProfileGuardBuilder;
    /** @var PersonProfileGuard */
    private $personProfileGuard;

    protected function setUp()
    {
        $this->qualificationDetailsMapper = XMock::of(QualificationDetailsMapper::class);
        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->apiPersonalDetails = XMock::of(ApiPersonalDetails::class);
        $this->contextProvider = XMock::of(ContextProvider::class);
        $this->personProfileGuardBuilder = XMock::of(PersonProfileGuardBuilder::class);
    }

    public function testProcessReturnsActionResultForGetMethodIfUserHasCorrectPermissions()
    {
        $this->mockPermissions($this->getCertificateData(), true);
        $result = $this->createService()->process(1, VehicleClassGroupCode::BIKES, self::BACK_URL, false);

        $this->assertInstanceOf(ViewActionResult::class, $result);
    }

    public function testProcessRemoveCertificateIfUserHasCorrectPermissions()
    {
        $this->mockPermissions($this->getCertificateData(), true);
        $result = $this->createService()->process(1, VehicleClassGroupCode::BIKES, self::BACK_URL, true);

        $this->assertInstanceOf(RedirectToRoute::class, $result);
    }

    /** @expectedException \DvsaCommon\Exception\UnauthorisedException */
    public function testProcessThrowExceptionForGetMethodIfUserHasIncorrectPermissions()
    {
        $this->mockPermissions($this->getCertificateData(), false);

        $result = $this->createService()->process(1, VehicleClassGroupCode::BIKES, self::BACK_URL, false);

        $this->assertInstanceOf(ViewActionResult::class, $result);
    }

    /** @expectedException \DvsaCommon\Exception\UnauthorisedException */
    public function testProcessThrowExceptionForGetMethodIfUserHasCorrectPermissionsButCertificateDoesNotExist()
    {
        $this->mockPermissions(null, true);

        $result = $this->createService()->process(1, VehicleClassGroupCode::BIKES, self::BACK_URL, false);

        $this->assertInstanceOf(ViewActionResult::class, $result);
    }

    private function createService()
    {
        return new RemoveCertificateDetailsService(
            $this->authorisationService,
            $this->qualificationDetailsMapper,
            $this->apiPersonalDetails,
            $this->contextProvider,
            $this->personProfileGuardBuilder
        );
    }

    private function getCertificateData()
    {
        $dto = new MotTestingCertificateDto();
        $dto
            ->setId(1)
            ->setCertificateNumber('num1223')
            ->setDateOfQualification('2012-02-03');

        return $dto;
    }

    private function mockPermissions(MotTestingCertificateDto $certData = null, $canRemoveQualificationDetails)
    {
        $this
            ->qualificationDetailsMapper
            ->expects($this->any())
            ->method('getQualificationDetails')
            ->willReturn($certData)
        ;

        $this
            ->apiPersonalDetails
            ->expects($this->any())
            ->method('getPersonalDetailsData')
            ->willReturn([]);

        $this->personProfileGuard = XMock::of(PersonProfileGuard::class);
        $this->personProfileGuard
            ->expects($this->any())
            ->method('canRemoveQualificationDetails')
            ->willReturn($canRemoveQualificationDetails);

        $this
            ->personProfileGuardBuilder
            ->expects($this->any())
            ->method('createPersonProfileGuard')
            ->willReturn($this->personProfileGuard);
    }
}
