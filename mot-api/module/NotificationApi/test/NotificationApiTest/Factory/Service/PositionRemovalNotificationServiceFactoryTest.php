<?php

namespace NotificationApiTest\Factory\Service;

use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;
use NotificationApi\Factory\Service\PositionRemovalNotificationServiceFactory;
use NotificationApi\Service\PositionRemovalNotificationService;

class PositionRemovalNotificationServiceFactoryTest extends AbstractServiceTestCase
{
    /** @var ServiceManager */
    private $serviceLocator;

    public function setUp()
    {
        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setAllowOverride(true);

        $authorisationService = XMock::of(AuthorisationService::class);
        $authorisationService->expects($this->any())
                             ->method('getAuthorizationDataAsArray')
                             ->willReturn($this->mockRoles());

        $this->serviceLocator->setService('DvsaAuthorisationService', $authorisationService);
    }

    // Factory returns instance of PositionRemovalNotifiationService
    public function testFactoryReturnsPositionRemovalNotificationService()
    {
        $factory = new PositionRemovalNotificationServiceFactory();
        $service = $factory->createService($this->serviceLocator);

        $this->assertInstanceOf(PositionRemovalNotificationService::class, $service);
    }

    // If roles are empty, throw exception
    public function testFactoryThrowsInvalidArgumentExceptionIfRolesEmpty()
    {
        $this->setExpectedException('InvalidArgumentException', 'Roles are not valid');

        $factory = new PositionRemovalNotificationServiceFactory();

        $authorisationService = XMock::of(AuthorisationService::class);
        $authorisationService->expects($this->any())
                             ->method('getAuthorizationDataAsArray')
                             ->willReturn([]);

        $this->serviceLocator->setService('DvsaAuthorisationService', $authorisationService);

        $service = $factory->createService($this->serviceLocator);

        $this->assertInstanceOf(PositionRemovalNotificationService::class, $service);
    }

    // If roles are not empty, but one or more of the sites/organisations/system keys not available throw error
    public function testFactoryThrowsInvalidArgumentExceptionIfNoDefinedRolesAvailable()
    {
        $this->setExpectedException('InvalidArgumentException', 'Site/Organisation/System roles must be defined');

        $factory = new PositionRemovalNotificationServiceFactory();

        $authorisationService = XMock::of(AuthorisationService::class);
        $authorisationService->expects($this->any())
                             ->method('getAuthorizationDataAsArray')
                             ->willReturn(
                                 [
                                     'test' => '1',
                                     'test2' => '2',
                                     'organisations' => '3',
                                 ]
                             );

        $this->serviceLocator->setService('DvsaAuthorisationService', $authorisationService);

        $service = $factory->createService($this->serviceLocator);

        $this->assertInstanceOf(PositionRemovalNotificationService::class, $service);
    }

    // mock array of roles
    private function mockRoles()
    {
        $roles = [
            'normal' => [
                'roles' => [
                    'AREA-OFFICE-1-USER',
                    'TESTER',
                    'SITE-ADMIN',
                    'SITE-MANAGER',
                ],
            ],
            'organisations' => [
                '9' => [
                    'roles' => [
                        'AUTHORISED-EXAMINER-DELEGATE',
                        'AUTHORISED-EXAMINER-DESIGNATED-MANAGER',
                    ],
                ],
                '10' => [
                    'roles' => [
                        'AUTHORISED-EXAMINER-DESIGNATED-MANAGER',
                    ],
                ],
                '12' => [
                    'roles' => [
                        'AUTHORISED-EXAMINER-DESIGNATED-MANAGER',
                    ],
                ],
            ],
            'sites' => [
                1 => [
                    'roles' => [
                        'TESTER',
                    ],
                ],
            ],
        ];

        return $roles;
    }
}
