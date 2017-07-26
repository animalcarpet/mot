<?php

namespace SiteApiTest\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\SiteBusinessRoleMapRepository;
use SiteApi\Mapper\TestersAnnualAssessmentMapper;
use SiteApi\Service\TestersAnnualAssessmentService;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class TestersAnnualAssessmentServiceTest.
 */
class TestersAnnualAssessmentServiceTest extends AbstractServiceTestCase
{
    const SITE_ID = 123;
    const UNAUTHORISED_SITE_ID = 10;

    /** @var AuthorisationServiceInterface|MockObj */
    private $authorisationService;
    /** @var SiteBusinessRoleMapRepository|MockObj */
    private $siteBusinessRoleMapRepository;
    /** @var TestersAnnualAssessmentService */
    private $testersAnnualAssessmentService;

    private function setService()
    {
        $this->testersAnnualAssessmentService = new TestersAnnualAssessmentService(
            $this->siteBusinessRoleMapRepository,
            new TestersAnnualAssessmentMapper(),
            $this->authorisationService
        );
    }

    private function mockServiceWithAuthorisationService($siteId, $permissions = [], $testersWithAnnualAssessmentsGroupA = [], $testersWithAnnualAssessmentsGroupB = [])
    {
        $this->authorisationService = $this->getMockAuthorizationService();
        $this->assertGrantedAtSite($this->authorisationService, $permissions, $siteId);
        $this->mockSiteBusinessRoleMapRepository($testersWithAnnualAssessmentsGroupA, $testersWithAnnualAssessmentsGroupB);

        $this->setService();
    }

    private function mockSiteBusinessRoleMapRepository($testersWithAnnualAssessmentsGroupA, $testersWithAnnualAssessmentsGroupB)
    {
        $this->siteBusinessRoleMapRepository = XMock::of(SiteBusinessRoleMapRepository::class);

        $this->siteBusinessRoleMapRepository
            ->method('getTestersWithTheirAnnualAssessmentsForGroupA')
            ->willReturn($testersWithAnnualAssessmentsGroupA);

        $this->siteBusinessRoleMapRepository
            ->method('getTestersWithTheirAnnualAssessmentsForGroupB')
            ->willReturn($testersWithAnnualAssessmentsGroupB);
    }

    /**
     * @dataProvider getTestForGroupDataProvider
     * @param $testForGroup
     */
    public function testGetTestersAnnualAssessmentForGroup($testForGroup)
    {
        $dateAwarded = '17-02-2015';
        $id = '42';
        $username = 'userA';
        $firstName = 'Bob';
        $middleName = 'Alan';
        $familyName = 'Tester';
        $testersWithAnnualAssessmentsGroup = $this->createTestersWithAnnualAssessmentsForGroup(
            $dateAwarded, $id, $username, $firstName, $middleName, $familyName
            );

        if ($testForGroup == VehicleClassGroupCode::BIKES) {
            $this->mockServiceWithAuthorisationService(self::SITE_ID, [PermissionAtSite::TESTERS_ANNUAL_ASSESSMENT_VIEW], $testersWithAnnualAssessmentsGroup);
        } else {
            $this->mockServiceWithAuthorisationService(self::SITE_ID, [PermissionAtSite::TESTERS_ANNUAL_ASSESSMENT_VIEW], [], $testersWithAnnualAssessmentsGroup);
        }

        $testersAnnualAssessmentDto = $this->testersAnnualAssessmentService->getTestersAnnualAssessment(self::SITE_ID);

        if ($testForGroup == VehicleClassGroupCode::BIKES) {
            $this->assertEquals(count($testersAnnualAssessmentDto->getGroupAAssessments()), 1);
            $this->assertEquals(count($testersAnnualAssessmentDto->getGroupBAssessments()), 0);
            $testerAnnualAssessmentRow = $testersAnnualAssessmentDto->getGroupAAssessments()[0];
        } else {
            $this->assertEquals(count($testersAnnualAssessmentDto->getGroupAAssessments()), 0);
            $this->assertEquals(count($testersAnnualAssessmentDto->getGroupBAssessments()), 1);
            $testerAnnualAssessmentRow = $testersAnnualAssessmentDto->getGroupBAssessments()[0];
        }

        $this->assertEquals($testerAnnualAssessmentRow->getDateAwarded()->format("d-m-Y"), $dateAwarded);
        $this->assertEquals($testerAnnualAssessmentRow->getUserId(), $id);
        $this->assertEquals($testerAnnualAssessmentRow->getUsername(), $username);
        $this->assertEquals($testerAnnualAssessmentRow->getUserFirstName(), $firstName);
        $this->assertEquals($testerAnnualAssessmentRow->getUserMiddleName(), $middleName);
        $this->assertEquals($testerAnnualAssessmentRow->getUserFamilyName(), $familyName);
    }

    public function testAssertGrantedAtSite_throwException_whenUserIsNotAssignedToSite()
    {
        $this->expectException(UnauthorisedException::class);
        $this->mockServiceWithAuthorisationService(self::UNAUTHORISED_SITE_ID);

        $this->testersAnnualAssessmentService->getTestersAnnualAssessment(self::UNAUTHORISED_SITE_ID);

    }

    private function createTestersWithAnnualAssessmentsForGroup($dateAwarded, $id, $username, $firstName, $middleName, $familyName)
    {
        return [
             [
                 'dateAwarded' => $dateAwarded,
                 'id' => $id,
                 'username' => $username,
                 'firstName' => $firstName,
                 'middleName' => $middleName,
                 'familyName' => $familyName,
             ],
        ];
    }

    public function getTestForGroupDataProvider()
    {
        return [
             [
                 VehicleClassGroupCode::BIKES
             ],
             [
                 VehicleClassGroupCode::CARS_ETC
             ],
        ];
    }
}
