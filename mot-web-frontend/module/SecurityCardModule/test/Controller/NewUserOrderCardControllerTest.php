<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Test\Controller;

use CoreTest\Controller\AbstractLightWebControllerTest;
use Dvsa\Mot\Frontend\SecurityCardModule\Controller\NewUserOrderCardController;
use Zend\Http\Request;
use Zend\Http\Response;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Core\Service\LazyMotFrontendAuthorisationService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Di\ServiceLocator;
use Zend\ServiceManager\ServiceManager;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\Http\HttpStatus;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;

class NewUserOrderCardControllerTest extends AbstractLightWebControllerTest
{
    const VALID_USER_ID = '105';
    const USER_ID_ROUTE_PARAM = 'userId';

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var MotIdentityProviderInterface $motIdentityProvider */
    private $motIdentityProvider;

    /** @var LazyMotFrontendAuthorisationService $authorisationService */
    private $authorisationService;

    /** @var  TwoFaFeatureToggle */
    private $twoFaFeatureToggle;

    public function setUp()
    {
        parent::setUp();
        $this->setController($this->buildController());
    }

    public function testUserIdFromParamsNullCauses404()
    {
        $this->setRouteParams([self::USER_ID_ROUTE_PARAM => null]);
        $this->controllerExpectsNotFound();
        $this->setUpAndMockIdentity(self::VALID_USER_ID, false);
        $this->getController()->orderCardNewUserAction();
    }

    public function testDvsaUserCauses404()
    {
        $this->setRouteParams([self::USER_ID_ROUTE_PARAM => self::VALID_USER_ID]);
        $this->controllerExpectsNotFound();
        $this->setUpAndMockIdentity(self::VALID_USER_ID, false);
        $this->setUpIsDvsaUser(true);
        $this->getController()->orderCardNewUserAction();
    }

    public function testActiveCardCauses404()
    {
        $this->setRouteParams([self::USER_ID_ROUTE_PARAM => self::VALID_USER_ID]);
        $this->controllerExpectsNotFound();
        $this->setUpAndMockIdentity(self::VALID_USER_ID, true);
        $this->setUpIsDvsaUser(false);
        $this->getController()->orderCardNewUserAction();
    }

    public function testUserIdFromParamsNotUsersId()
    {
        $this->setRouteParams([self::USER_ID_ROUTE_PARAM => self::VALID_USER_ID]);
        $this->controllerExpectsNotFound();
        $this->setUpAndMockIdentity('110', false);
        $this->getController()->orderCardNewUserAction();
    }

    private function setUpAndMockIdentity($userId, $hasActiveCard)
    {
        $identity = new Identity();
        $identity
            ->setUserId($userId)
            ->setSecondFactorRequired($hasActiveCard);

        $this->motIdentityProvider->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);
    }

    private function controllerExpectsNotFound()
    {
        $this->response
            ->expects($this->once())
            ->method('setStatusCode')
            ->with(HttpStatus::HTTP_NOT_FOUND);

        return $this;
    }

    private function setUpIsDvsaUser($isDvsa) {
        $this->authorisationService->expects($this->any())
            ->method('isDvsa')
            ->willReturn($isDvsa);
    }

    private function buildController()
    {
        $this->request = new Request();
        $this->response = XMock::of(Response::class);
        $this->motIdentityProvider = XMock::of(MotIdentityProviderInterface::class);
        $this->authorisationService = XMock::of(LazyMotFrontendAuthorisationService::class);
        $this->twoFaFeatureToggle = XMock::of(TwoFaFeatureToggle::class);
        $this->twoFaFeatureToggle->expects($this->any())->method('isEnabled')->willReturn(true);

        $controller = new NewUserOrderCardController(
            $this->request,
            $this->response,
            $this->motIdentityProvider,
            $this->authorisationService,
            $this->twoFaFeatureToggle
        );

        return $controller;
    }


}
