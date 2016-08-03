<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Controller;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\Frontend\MotTestModule\Controller\MotTestResultsController;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommon\Auth\MotIdentityProvider;
use DvsaCommon\Constants\MotTestNumberConstraint;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\Common\OdometerReadingDTO;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommonTest\Bootstrap;
use DvsaMotTestTest\Controller\AbstractDvsaMotTestTestCase;
use Gitonomy\Git\Tree;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Mvc\Router\SimpleRouteStack;

/**
 * Test clas for MotTestResultsController.
 */
class MotTestResultsControllerTest extends AbstractDvsaMotTestTestCase
{
    const DEFAULT_MOT_TEST_NUMBER = 656402615654;

    /**
     * @var MotFrontendAuthorisationServiceInterface
     */
    private $authorisationService;

    protected function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->authorisationService = $this->getMock(MotFrontendAuthorisationServiceInterface::class);
        $this->controller = new MotTestResultsController($this->authorisationService);
        $this->controller->setServiceLocator($serviceManager);

        parent::setUp();
    }

    public function testMotTestIndexCanBeAccessedForAuthenticatedRequest()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
        $motTestData = $this->getTestMotTestDataDto(self::DEFAULT_MOT_TEST_NUMBER);
        $this->setUpRestClient($motTestData, self::DEFAULT_MOT_TEST_NUMBER);

        $result = $this->getResultForAction('index', ['motTestNumber' => self::DEFAULT_MOT_TEST_NUMBER]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
        $this->assertEquals($motTestData, $result->motTest);
    }

    public function testMotTestIndexWithoutIdParameterFails()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
        
        $this->controller->dispatch($this->request);

        $this->assertResponseStatus(self::HTTP_ERR_404);
    }

    /**
     * @param int    $motTestNumber
     * @param string $status
     *
     * @throws \Exception
     *
     * @return MotTestDto
     */
    private function getTestMotTestDataDto($motTestNumber = 1, $status = MotTestStatusName::PASSED)
    {
        /** @var MotIdentityProvider $mockIdentityProvider */
        $mockIdentityProvider = $this->getServiceManager()->get('MotIdentityProvider');

        $motTest = (new MotTestDto())
            ->setMotTestNumber($motTestNumber)
            ->setTester((new PersonDto())->setId($mockIdentityProvider->getIdentity()->getUserId()))
            ->setTestType((new MotTestTypeDto())->setCode(MotTestTypeCode::NORMAL_TEST))
            ->setStatus($status)
            ->setOdometerReading(
                (new OdometerReadingDTO())
                    ->setValue(1234)
                    ->setUnit(OdometerUnit::KILOMETERS)
                    ->setResultType(OdometerReadingResultType::OK)
            )
            ->setVehicle(
                (new VehicleDto())
                    ->setId(1)
                    ->setRegistration('ELFA 1111')
                    ->setVin('1M2GDM9AXKP042725')
                    ->setYear(2011)
                    ->setVehicleClass(
                        (new VehicleClassDto())
                            ->setId(4)
                            ->setCode(4)
                    )
                    ->setMakeName('Volvo')
                    ->setModelName('S80 GTX')
                    ->setFirstUsedDate('2011-12-12')
            );

        return $motTest;
    }

    /**
     * @param MotTestDto $data
     * @param int   $motTestNumber
     *
     * @return HttpRestJsonClient
     */
    private function setUpRestClient(MotTestDto $data, $motTestNumber)
    {
        $restClient = $this->getRestClientMockForServiceManager();
        $restClient
            ->expects($this->at(0))
            ->method('get')
            ->with(sprintf('mot-test/%d', $motTestNumber))
            ->willReturn(['data' => $data]);
        $restClient
            ->expects($this->at(1))
            ->method('get')
            ->with($this->anything())
            ->willReturn(['data' => []]);

        return $restClient;
    }
}
