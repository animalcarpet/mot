<?php
namespace SiteApiTest\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Repository\MotTestRepository;
use SiteApi\Service\MotTestInProgressService;
use Zend\Authentication\AuthenticationService;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class MotTestInProgressServiceTest
 *
 * @package SiteApiTest\Service
 */
class MotTestInProgressServiceTest extends AbstractServiceTestCase
{
    const SITE_ID = 999;

    /** @var MotTestRepository */
    private $mockMotTestRepo;
    /**
     * @var  AuthorisationServiceInterface|MockObj
     */
    private $mockAuthService;
    /** @var MotTestInProgressService */
    private $motTestInProgressService;

    public function setUp()
    {
        $this->setUpRepository();

        $this->mockAuthService = $this->getMockAuthorizationService();

        $this->motTestInProgressService = new MotTestInProgressService(
            $this->mockMotTestRepo,
            $this->mockAuthService
        );
    }

    public function testGetTestInProgressReturnsDto()
    {
        // This code doesn't assert anything, it checks if code compiles.
        $dtoArray = $this->motTestInProgressService->getAllForSite(1);

        $this->assertTrue(is_array($dtoArray));
        $motTestDto = $dtoArray[0];

        $this->assertEquals('1001', $motTestDto->getNumber());
        $this->assertEquals('John Johnson', $motTestDto->getTesterName());
        $this->assertEquals('LAMP101', $motTestDto->getVehicleRegisteredNumber());
        $this->assertEquals('Clio', $motTestDto->getVehicleModel());
        $this->assertEquals('Renault', $motTestDto->getVehicleMake());
    }

    public function setUpRepository()
    {
        $tester = new Person();
        $tester->setFirstName('John');
        $tester->setFamilyName('Johnson');

        $vehicleMake = new Make();
        $vehicleMake->setId(1);
        $vehicleMake->setCode('Renau');
        $vehicleMake->setName('Renault');

        $vehicleModel = new Model();
        $vehicleModel->setId(2);
        $vehicleModel->setCode('CLIO');
        $vehicleModel->setName('Clio');
        $vehicleModel->setMake($vehicleMake);

        $modelDetail = new ModelDetail();
        $modelDetail->setModel($vehicleModel);

        $vehicle = new Vehicle();
        $vehicle->setRegistration('LAMP101');
        $vehicle->setModelDetail($modelDetail);

        $motTest = new MotTest();
        $motTest->setTester($tester);
        $motTest->setNumber(1001);
        $motTest->setVehicle($vehicle);

        $this->mockMotTestRepo = $this->getMockWithDisabledConstructor(MotTestRepository::class);
        $this->mockMotTestRepo->expects($this->any())->method('findInProgressTestsForVts')->will(
            $this->returnValue([$motTest])
        );
    }


    /**
     * @dataProvider dataProviderTestMethodsPermissionsAndResults
     */
    public function testGetDataMethodsPermissionsAndResults($method, $params, $repoMocks, $permissions, $expect)
    {
        if ($repoMocks !== null) {
            foreach ($repoMocks as $repo) {
                $invocation = isset($repo['call']) ? $repo['call'] : $this->once();
                $result = isset($repo['result']) ? $repo['result'] : null;

                $this->mockMethod(
                    $this->{$repo['class']}, $repo['method'], $invocation, $result, $repo['params']
                );
            }
        }

        //  --  check permission    --
        if ($permissions !== null) {
            $this->assertGrantedAtSite($this->mockAuthService, $permissions, $params['siteId']);
        }

        //  --  set expected exception  --
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message'], $exception['code']);
        }

        //  --  call and check result --
        $actual = XMock::invokeMethod($this->motTestInProgressService, $method, $params);

        $this->assertSame($expect['result'], $actual);
    }

    public function dataProviderTestMethodsPermissionsAndResults()
    {
        $unauthException = [
            'class'   => UnauthorisedException::class,
            'message' => 'You not have permissions',
            'code'    => 0,
        ];

        return [
            //  logical block :: siteChangeStatus
            //  no permission
            [
                'method'      => 'getCountForSite',
                'params'      => [
                    'siteId' => self::SITE_ID,
                ],
                'repo'        => null,
                'permissions' => [],
                'expect'      => [
                    'exception' => $unauthException,
                ],
            ],
            //  success
            [
                'method'      => 'getCountForSite',
                'params'      => [
                    'siteId' => self::SITE_ID,
                ],
                'repo'        => [
                    [
                        'class'  => 'mockMotTestRepo',
                        'method' => 'countInProgressTestsForVts',
                        'params' => [self::SITE_ID],
                        'result' => 6666,
                    ],
                ],
                'permissions' => [PermissionAtSite::VIEW_TESTS_IN_PROGRESS_AT_VTS],
                'expect'      => [
                    'result' => 6666,
                ],
            ],
        ];
    }
}
