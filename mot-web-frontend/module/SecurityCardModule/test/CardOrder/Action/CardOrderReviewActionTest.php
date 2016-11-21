<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardOrder\Action;

use Core\Action\ViewActionResult;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderReviewAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Application\Data\ApiPersonalDetails;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardNotificationService;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\Request;
use Core\Action\RedirectToRoute;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderProtection;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardEventService;

class CardOrderReviewActionTest extends \PHPUnit_Framework_TestCase
{
    const USER_ID = 999;
    const ID = 1;
    const FIRST_NAME = 'John';
    const MIDDLE_NAME = 'Steven';
    const SURNAME = 'Smith';
    const DOB = '1980-10-10';
    const TITLE = 'Mr';
    const GENDER = 'Male';
    const ADDR_1 = 'London';
    const ADDR_2 = 'Abc';
    const ADDR_3 = '213';
    const TOWN = 'Dublin';
    const POSTCODE = 'LON 123';
    const EMAIL = 'personaldetailstest@dvsa.test';
    const PHONE = '123456765432';
    const DRIVING_LICENCE_BUMBER = '2343213';
    const REGION = 'Other';
    const ROLE_TESTER = 'TESTER';
    const ROLE_USER = 'USER';
    const ROLE_AEDM = 'aedm';
    const POSITIONS = 'test';
    const USERNAME = 'tester1';
    const SITE_ID = 1;
    const SITE_NAME = "Garage";
    const SITE_NUMBER = "V1234";
    const SITE_ADDRESS = "Elm Street";
    const ORGANISATION_ID = 13;
    const ORGANISATION_NAME = "Venture Industries AE";
    const ORGANISATION_NUMBER = "AEVNTR";
    const ORGANISATION_ADDRESS = "1 Providence, Nashville, 72-123";

    /** @var OrderNewSecurityCardSessionService $sessionService */
    private $sessionService;

    /** @var ApiPersonalDetails $apiPersonalDetails */
    private $apiPersonalDetails;

    /** @var SecurityCardService $securityCardService */
    private $securityCardService;

    /** @var OrderSecurityCardStepService $stepService */
    private $stepService;

    /** @var Request $request */
    private $request;

    /** @var CardOrderProtection $cardOrderProtection */
    private $cardOrderProtection;

    private $orderSecurityCardEventService;

    /** @var OrderSecurityCardNotificationService */
    private $notificationService;

    public function setUp()
    {
        parent::setUp();
        $this->sessionService = XMock::of(OrderNewSecurityCardSessionService::class);
        $this->apiPersonalDetails = XMock::of(ApiPersonalDetails::class);
        $this->securityCardService = XMock::of(SecurityCardService::class);
        $this->stepService = XMock::of(OrderSecurityCardStepService::class);
        $this->cardOrderProtection = XMock::of(CardOrderProtection::class);
        $this->orderSecurityCardEventService = XMock::of(OrderSecurityCardEventService::class);
        $this->notificationService = XMock::of(OrderSecurityCardNotificationService::class);
        $this->request = XMock::of(Request::class);
    }

    public function testRedirectedToAddress_WhenNotAllowedOnReviewStep() {
        $this->setUpProtection();
        $this->stepService
            ->expects($this->once())
            ->method('isAllowedOnStep')
            ->with(self::USER_ID, OrderSecurityCardStepService::REVIEW_STEP)
            ->willReturn(false);

        /** @var RedirectToRoute $actionResult */
        $actionResult = $this->buildAction()->execute($this->request, self::USER_ID);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertEquals('security-card-order/address', $actionResult->getRouteName());
        $this->assertEquals(self::USER_ID, $actionResult->getRouteParams()['userId']);
    }

    public function testRedirectedToConfirmation_EventCreated_WhenPostAndNotAlreadyOrdered() {
        $this->setUpProtection();
        $this->stepService
            ->expects($this->once())
            ->method('isAllowedOnStep')
            ->with(self::USER_ID, OrderSecurityCardStepService::REVIEW_STEP)
            ->willReturn(true);

        $this->orderSecurityCardEventService
            ->expects($this->once())
            ->method('createEvent')
            ->with(self::USER_ID, 'test_address_data');

        $this->setupPostMocks();

        /** @var RedirectToRoute $actionResult */
        $actionResult = $this->buildAction()->execute($this->request, self::USER_ID);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertEquals('security-card-order/confirmation', $actionResult->getRouteName());
        $this->assertEquals(self::USER_ID, $actionResult->getRouteParams()['userId']);
    }

    public function testRedirectedToConfirmation_WhenPostAndHaveAlreadyOrdered() {
        $this->setUpProtection();
        $this->stepService
            ->expects($this->once())
            ->method('isAllowedOnStep')
            ->with(self::USER_ID, OrderSecurityCardStepService::REVIEW_STEP)
            ->willReturn(true);

        $this->request
            ->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $this->apiPersonalDetails
            ->expects($this->once())
            ->method('getPersonalDetailsData')
            ->with(self::USER_ID)
            ->willReturn($this->buildPersonalDetailsData());

        $this->sessionService
            ->expects($this->at(0))
            ->method('loadByGuid')
            ->with(self::USER_ID)
            ->willReturn([OrderNewSecurityCardSessionService::HAS_ORDERED_STORE => true]);

        /** @var RedirectToRoute $actionResult */
        $actionResult = $this->buildAction()->execute($this->request, self::USER_ID);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertEquals('security-card-order/confirmation', $actionResult->getRouteName());
        $this->assertEquals(self::USER_ID, $actionResult->getRouteParams()['userId']);
    }

    public function testViewModelCreatedCorrectly_WhenNotPost() {
        $this->setUpProtection();
        $this->stepService
            ->expects($this->once())
            ->method('isAllowedOnStep')
            ->with(self::USER_ID, OrderSecurityCardStepService::REVIEW_STEP)
            ->willReturn(true);

        $this->request
            ->expects($this->once())
            ->method('isPost')
            ->willReturn(false);

        $this->apiPersonalDetails
            ->expects($this->once())
            ->method('getPersonalDetailsData')
            ->with(self::USER_ID)
            ->willReturn($this->buildPersonalDetailsData());

        /** @var ViewActionResult $actionResult */
        $actionResult = $this->buildAction()->execute($this->request, self::USER_ID);

        $this->assertInstanceOf(ViewActionResult::class, $actionResult);
        $this->assertEquals('2fa/card-order/review', $actionResult->getTemplate());
        $this->assertEquals(CardOrderReviewAction::REVIEW_PAGE_TITLE, $actionResult->layout()->getPageTitle());
        $this->assertEquals(CardOrderReviewAction::REVIEW_PAGE_SUBTITLE, $actionResult->layout()->getPageSubTitle());

    }

    private function buildAction()
    {
        $action = new CardOrderReviewAction(
            $this->sessionService,
            $this->apiPersonalDetails,
            $this->securityCardService,
            $this->stepService,
            $this->cardOrderProtection,
            $this->notificationService,
            $this->orderSecurityCardEventService
        );
        return $action;
    }

    private function setUpProtection()
    {
        $this->cardOrderProtection
            ->expects($this->once())
            ->method('checkAuthorisation')
            ->willReturn(null);
    }

    private function setupPostMocks() {
        $this->request
            ->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $this->apiPersonalDetails
            ->expects($this->once())
            ->method('getPersonalDetailsData')
            ->with(self::USER_ID)
            ->willReturn($this->buildPersonalDetailsData());

        $this->sessionService
            ->expects($this->at(0))
            ->method('loadByGuid')
            ->with(self::USER_ID)
            ->willReturn([OrderNewSecurityCardSessionService::HAS_ORDERED_STORE => false]);

        $addressStepData = ['test' => 'test_address_data'];
        $this->sessionService
            ->expects($this->at(1))
            ->method('loadByGuid')
            ->with(self::USER_ID)
            ->willReturn([OrderNewSecurityCardSessionService::ADDRESS_STEP_STORE => $addressStepData]);

        $this->securityCardService
            ->expects($this->once())
            ->method('orderNewCard')
            ->with(self::USERNAME, self::USER_ID, $addressStepData)
            ->willReturn(true);
    }

    private function buildPersonalDetailsData() {
        return [
            'id'                   => self::ID,
            'firstName'            => self::FIRST_NAME,
            'middleName'           => self::MIDDLE_NAME,
            'surname'              => self::SURNAME,
            'dateOfBirth'          => self::DOB,
            'username'             => self::USERNAME,
            'title'                => self::TITLE,
            'gender'               => self::GENDER,
            'addressLine1'         => self::ADDR_1,
            'addressLine2'         => self::ADDR_2,
            'addressLine3'         => self::ADDR_3,
            'town'                 => self::TOWN,
            'postcode'             => self::POSTCODE,
            'email'                => self::EMAIL,
            'phone'                => self::PHONE,
            'drivingLicenceNumber' => self::DRIVING_LICENCE_BUMBER,
            'drivingLicenceRegion' => self::REGION,
            'roles'                => [
                "system" => [
                    "roles" => [self::ROLE_USER]
                ],
                "organisations" => [
                    self::ORGANISATION_ID => [
                        "name" => self::ORGANISATION_NAME,
                        "number" => self::ORGANISATION_NUMBER,
                        "address" => self::ORGANISATION_ADDRESS,
                        "roles" => [self::ROLE_AEDM]
                    ]
                ],
                "sites" => [
                    self::SITE_ID => [
                        "name" => self::SITE_NAME,
                        "number" => self::SITE_NUMBER,
                        "address" => self::SITE_ADDRESS,
                        "roles" => [self::ROLE_TESTER]
                    ]
                ],
            ],
            'positions'            => [
                'test'
            ]
        ];
    }
}
