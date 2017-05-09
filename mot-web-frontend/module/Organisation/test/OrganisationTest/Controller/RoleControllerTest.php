<?php

namespace OrganisationTest\Controller;

use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaClient\Entity\Person;
use DvsaClient\Mapper\OrganisationMapper;
use DvsaClient\Mapper\OrganisationPositionMapper;
use DvsaClient\Mapper\OrganisationRoleMapper;
use DvsaClient\Mapper\PersonMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Organisation\OrganisationPositionDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Validator\UsernameValidator;
use DvsaCommonTest\Bootstrap;
use Organisation\Controller\RoleController;

/**
 * Testing frontend controller for assigning a role to a person at organisation (Authorised Examiner) level.
 */
class RoleControllerTest extends AbstractFrontendControllerTestCase
{
    const VIEW_MODEL_CLASS_PATH = 'Zend\View\Model\ViewModel';
    const MAX_USERNAME_LENGTH = 50;

    private $mapperFactoryMock;
    private $roleMapperMock;
    private $personMapperMock;
    private $authorisedExaminerMapperMock;
    private $organisationPositionMapperMock;
    private $organisationId = 1;
    private $personId = 1;
    private $roleId = 2;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->setServiceManager($serviceManager);
        $serviceManager->setAllowOverride(true);

        $usernameValidatorMock = $this->createUsernameValidatorMock(true);
        $htmlPurifier = $this->getMock('HTMLPurifier');
        $this->controller = new RoleController($usernameValidatorMock, $htmlPurifier);
        $this->controller->setServiceLocator($serviceManager);

        parent::setUp();

        $this->roleMapperMock = $this->getRoleMapperMock();
        $this->personMapperMock = $this->getPersonMapperMock();
        $this->authorisedExaminerMapperMock = $this->getAuthorisedExaminerMapperMock();
        $this->organisationPositionMapperMock = $this->getOrganisationPositionMapperMock();

        $this->mapperFactoryMock = $this->getMapperFactoryMock(
            $this->personMapperMock,
            $this->roleMapperMock,
            $this->authorisedExaminerMapperMock,
            $this->organisationPositionMapperMock
        );

        $serviceManager->setService(MapperFactory::class, $this->mapperFactoryMock);

        $this->controller->setServiceLocator($serviceManager);
        $this->controller->manager = $this->controller->getServiceLocator()->get(MapperFactory::class);

        $this->createHttpRequestForController('role');
    }

    public function testIndexActionCanBeAccessed()
    {
        $response = $this->getResponseForAction('index', [
            'id' => $this->organisationId,
        ]);
        $this->assertEquals(self::HTTP_OK_CODE, $response->getStatusCode());
    }

    public function testIndexActionReturnsViewModel()
    {
        $this->routeMatch->setParam('action', 'index');
        $this->routeMatch->setParam('id', $this->organisationId);
        $this->request->setMethod('get');

        $viewModelArray = $this->controller->dispatch($this->request);

        $this->assertEquals($viewModelArray['id'], $this->organisationId);
        $this->assertEquals($viewModelArray['form'], []);
        $this->assertEquals($viewModelArray['personId'], '');
        $this->assertEquals($viewModelArray['userNotFound'], false);
        $this->assertInstanceOf(OrganisationDto::class, $viewModelArray['organisation']);
    }

    public function testListUserRolesActionCanBeAccessed()
    {
        $response = $this->getResponseForAction('listUserRoles', [
            'id' => $this->organisationId,
            'personId' => $this->personId,
        ]);
        $this->assertEquals(self::HTTP_OK_CODE, $response->getStatusCode());
    }

    /**
     * Test the structure of the view model returned in the list-user-roles action.
     */
    public function testListUserRolesActionReturnsViewModel()
    {
        $this->routeMatch->setParam('action', 'list-user-roles');
        $this->routeMatch->setParam('id', $this->organisationId);
        $this->routeMatch->setParam('personId', $this->personId);
        $this->request->setMethod('get');

        $viewModelArray = $this->controller->dispatch($this->request);

        $this->assertEquals($viewModelArray->id, $this->organisationId);
        $this->assertEquals($viewModelArray->personId, $this->personId);
        $this->assertEquals($viewModelArray->personUsername, 'jonsnow');
        $this->assertEquals($viewModelArray->personFullName, 'Jon Snow');
        $this->assertEquals($viewModelArray->userNotFound, false);
        $this->assertTrue($viewModelArray->hasRoleOption);
    }

    public function testAssignConfirmationActionReturnsViewModel()
    {
        $response = $this->getResponseForAction(
            'confirmNomination',
            [
                'id' => $this->organisationId,
                'nomineeId' => $this->personId,
                'roleId' => $this->roleId,
                'displayNotification' => true,
                'twoFactorEnabled' => true,
            ]
        );

        $this->assertEquals(self::HTTP_OK_CODE, $response->getStatusCode());
    }

    public function testRemoveActionReturnsViewModel()
    {
        $response = $this->getResponseForAction(
            'remove',
            [
                'id' => $this->organisationId,
                'personId' => $this->personId,
                'roleId' => $this->roleId,
            ]
        );
        $this->assertEquals(self::HTTP_OK_CODE, $response->getStatusCode());
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
     * @throws \Exception
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getRoleMapperMock()
    {
        $roleMapperMock = $this->roleMapperMock = \DvsaCommonTest\TestUtils\XMock::of(OrganisationRoleMapper::class);

        $roles = [OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE];

        $roleMapperMock->expects($this->any())
            ->method('fetchAllForPerson')
            ->with($this->organisationId, $this->personId)
            ->will($this->returnValue($roles));

        return $roleMapperMock;
    }

    /**
     * @throws \Exception
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getOrganisationPositionMapperMock()
    {
        $organisationPositionMapperMock = \DvsaCommonTest\TestUtils\XMock::of(OrganisationPositionMapper::class);

        $position = (new OrganisationPositionDto())
            ->setPerson(new PersonDto())
            ->setRole(OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE)
            ->setId($this->roleId);

        $organisationPositionMapperMock->expects($this->any())
            ->method('fetchAllPositionsForOrganisation')
            ->with($this->organisationId)
            ->will($this->returnValue([$position]));

        return $organisationPositionMapperMock;
    }

    /**
     * @throws \Exception
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPersonMapperMock()
    {
        $personMapperMock = \DvsaCommonTest\TestUtils\XMock::of(PersonMapper::class);

        $person = new Person();
        $person->setFirstName('Jon');
        $person->setFamilyName('Snow');
        $person->setUsername('jonsnow');

        $personMapperMock->expects($this->any())
            ->method('getById')
            ->with($this->personId)
            ->will($this->returnValue($person));

        return $personMapperMock;
    }

    /**
     * @throws \Exception
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getAuthorisedExaminerMapperMock()
    {
        $authorisedExaminerMapperMock = $this->authorisedExaminerMapperMock
            = \DvsaCommonTest\TestUtils\XMock::of(OrganisationMapper::class);

        $authorisedExaminer = new OrganisationDto();

        $authorisedExaminerMapperMock->expects($this->any())
            ->method('getAuthorisedExaminer')
            ->with($this->organisationId)
            ->will($this->returnValue($authorisedExaminer));

        return $authorisedExaminerMapperMock;
    }

    /**
     * @param $personMapperMock
     * @param $roleMapperMock
     * @param $authorisedExaminerMapperMock
     * @param $organisationPositionMapperMock
     *
     * @throws \Exception
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMapperFactoryMock(
        $personMapperMock,
        $roleMapperMock,
        $authorisedExaminerMapperMock,
        $organisationPositionMapperMock
    ) {
        $mapperFactoryMock = \DvsaCommonTest\TestUtils\XMock::of(MapperFactory::class);

        $map = [
            [MapperFactory::PERSON, $personMapperMock],
            [MapperFactory::ORGANISATION_ROLE, $roleMapperMock],
            [MapperFactory::ORGANISATION, $authorisedExaminerMapperMock],
            [MapperFactory::ORGANISATION_POSITION, $organisationPositionMapperMock],
        ];

        $mapperFactoryMock->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mapperFactoryMock;
    }

    /**
     * @return array
     */
    private function getViewModelForIndexAction()
    {
        return [
            'form' => [],
            'id' => null,
            'personId' => '',
            'userNotFound' => false,
        ];
    }
}
