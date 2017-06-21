<?php

namespace SiteTest\Controller;

use Application\Service\CatalogService;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaClient\Entity\Person;
use DvsaClient\Entity\VehicleTestingStation;
use DvsaClient\Mapper\PersonMapper;
use DvsaClient\Mapper\SiteMapper;
use DvsaClient\Mapper\SitePositionMapper;
use DvsaClient\Mapper\SiteRoleMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Validator\UsernameValidator;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use Site\Controller\RoleController;

/**
 * Class RoleControllerTest.
 *
 * Testing frontend controller for assigning a role to a person at site level.
 */
class RoleControllerTest extends AbstractFrontendControllerTestCase
{
    const VIEW_MODEL_CLASS_PATH = 'Zend\View\Model\ViewModel';

    private $mapperFactoryMock;
    private $roleMapperMock;
    private $personMapperMock;
    private $positionMapperMock;
    private $vehicleTestingStationMapperMock;
    private $siteId = 1;
    private $personId = 1;
    private $nomineeId = 1;
    private $vehicleTestingStationId = 1;
    private $roleCode = SiteBusinessRoleCode::TESTER;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->setServiceManager($serviceManager);
        $serviceManager->setAllowOverride(true);

        $usernameValidatorMock = $this->createUsernameValidatorMock(true);
        $htmlPurifier = $this->getMockBuilder('HTMLPurifier')->disableOriginalConstructor()->getMock();

        $this->controller = new RoleController($usernameValidatorMock, $htmlPurifier);
        $this->controller->setServiceLocator($serviceManager);

        $this->roleMapperMock = $this->getRoleMapperMock();
        $this->personMapperMock = $this->getPersonMapperMock();
        $this->positionMapperMock = $this->getPositionMapperMock();
        $this->vehicleTestingStationMapperMock = $this->getVehicleTestingStationMapperMock();

        $this->mapperFactoryMock = $this->getMapperFactoryMock(
            $this->personMapperMock,
            $this->roleMapperMock,
            $this->positionMapperMock,
            $this->vehicleTestingStationMapperMock
        );

        $serviceManager->setService(MapperFactory::class, $this->mapperFactoryMock);
        $serviceManager->setService('CatalogService', XMock::of(CatalogService::class));

        $this->controller->setServiceLocator($serviceManager);

        $this->createHttpRequestForController('role');
    }

    public function testSearchForPersonActionCanBeAccessed()
    {
        $this->getResponseForAction(
            'searchForPerson',
            [
                'vehicleTestingStationId' => $this->siteId,
            ]
        );
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testSearchForPersonReturnsViewModel()
    {
        $result = $this->getResultForAction(
            'searchForPerson', ['vehicleTestingStationId' => $this->vehicleTestingStationId]
        );

        $this->assertEquals($result['vehicleTestingStationId'], $this->vehicleTestingStationId);
        $this->assertEquals($result['form'], []);
        $this->assertEquals($result['personId'], '');
        $this->assertEquals($result['userNotFound'], false);
        $this->assertInstanceOf(VehicleTestingStation::class, $result['vehicleTestingStation']);
    }

    public function testListUserRolesActionCanBeAccessed()
    {
        $this->getResponseForAction(
            'listUserRoles',
            [
                'vehicleTestingStationId' => $this->siteId,
                'personId' => $this->personId,
            ]
        );
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testAssignConfirmationActionCanBeAccessed()
    {
        $this->getResponseForAction(
            'confirmNomination',
            [
                'nomineeId' => $this->nomineeId,
                'vehicleTestingStationId' => $this->siteId,
                'roleCode' => $this->roleCode,
            ]
        );

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * @param bool $isValid The value returned by UsernameValidator::isValid()
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createUsernameValidatorMock($isValid = true)
    {
        $usernameValidatorMock = $this
            ->getMockBuilder(UsernameValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $usernameValidatorMock
            ->expects($this->any())
            ->method('isValid')
            ->willReturn($isValid);

        if (!$isValid) {
            $messages = ['stringLengthTooLong' => sprintf('Username must be less than %s characters long.',
                self::MAX_USERNAME_LENGTH)];

            $usernameValidatorMock
                ->expects($this->any())
                ->method('getMessages')
                ->willReturn($messages);
        }

        return $usernameValidatorMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     *
     * @throws \Exception
     */
    private function getRoleMapperMock()
    {
        $roleMapperMock = $this->roleMapperMock = XMock::of(SiteRoleMapper::class);

        $roles = [
            SiteBusinessRoleCode::SITE_MANAGER,
            SiteBusinessRoleCode::SITE_ADMIN,
            SiteBusinessRoleCode::TESTER,
        ];

        $roleMapperMock->expects($this->any())
            ->method('fetchAllForPerson')
            ->with($this->siteId, $this->personId)
            ->will($this->returnValue($roles));

        return $roleMapperMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     *
     * @throws \Exception
     */
    private function getPositionMapperMock()
    {
        $positionMapperMock = $this->positionMapperMock = XMock::of(SitePositionMapper::class);

        return $positionMapperMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     *
     * @throws \Exception
     */
    private function getPersonMapperMock()
    {
        $personMapperMock = XMock::of(PersonMapper::class);

        $person = new Person();

        $personMapperMock->expects($this->any())
            ->method('getById')
            ->with($this->personId)
            ->will($this->returnValue($person));

        $personMapperMock->expects($this->any())
            ->method('getByIdentifier')
            ->with($this->personId)
            ->will($this->returnValue($person));

        return $personMapperMock;
    }

    private function getVehicleTestingStationMapperMock()
    {
        $vehicleTestingStationMapperMock = XMock::of(SiteMapper::class);

        $vehicleTestingStation = new VehicleTestingStation();

        $vehicleTestingStationMapperMock->expects($this->any())
            ->method('getById')
            ->with($this->vehicleTestingStationId)
            ->will($this->returnValue($vehicleTestingStation));

        return $vehicleTestingStationMapperMock;
    }

    /**
     * @param $personMapperMock
     * @param $roleMapperMock
     * @param $positionMapperMock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     *
     * @throws \Exception
     */
    private function getMapperFactoryMock(
        $personMapperMock,
        $roleMapperMock,
        $positionMapperMock,
        $vehicleTestingStationMock
    ) {
        $mapperFactoryMock = XMock::of(MapperFactory::class);

        $map = [
            ['Person', $personMapperMock],
            ['SiteRole', $roleMapperMock],
            ['SitePosition', $positionMapperMock],
            ['Site', $vehicleTestingStationMock],
        ];

        $mapperFactoryMock->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mapperFactoryMock;
    }
}
