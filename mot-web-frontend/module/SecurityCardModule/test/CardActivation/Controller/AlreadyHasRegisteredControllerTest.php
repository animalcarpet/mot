<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\Controller;

use Application\Data\ApiPersonalDetails;
use Application\Service\CatalogService;
use CoreTest\Controller\AbstractLightWebControllerTest;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCard;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\AlreadyHasRegisteredCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use Zend\View\Model\ViewModel;

class AlreadyHasRegisteredControllerTest extends AbstractLightWebControllerTest
{
    const USER_ID = 999;
    const USERNAME = 'tester1';
    const DATE = '2012-11-22';

    /** @var ApiPersonalDetails */
    private $personalDetailsService;

    /** @var CatalogService */
    private $catalogService;

    /** @var ContextProvider */
    private $contextProvider;

    /** @var SecurityCardService */
    private $securityCardService;

    /** @var  MotIdentityProviderInterface */
    private $identityProvider;

    /** @var  SecurityCard */
    private $securityCard;

    /** @var  SecurityCardGuard */
    private $securityCardGuard;

    public function setUp()
    {
        parent::setUp();

        $this->personalDetailsService = XMock::of(ApiPersonalDetails::class);
        $this->catalogService = XMock::of(CatalogService::class);
        $this->contextProvider = XMock::of(ContextProvider::class);
        $this->securityCardService = XMock::of(SecurityCardService::class);
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $this->securityCard = XMock::of(SecurityCard::class);
        $this->securityCardGuard = XMock::of(SecurityCardGuard::class);
    }

    public function testWhenGet_noSecurityCard_shouldRedirectToHomePage()
    {
        $this->withIdentity();

        $controller = $this->buildController();

        $this->expectRedirect(UserHomeController::ROUTE);

        $controller->indexAction();
    }

    public function testWhenGet_withActiveSecurityCard_shouldLoadPage()
    {
        $activeSecurityCard = new SecurityCard((object) ['active' => true, 'activationDate' => self::DATE]);

        $identity = $this->withIdentity();
        $this->withSecurityCardForUser($activeSecurityCard);
        $this->withActiveTwoFaCard(true, $identity);

        $controller = $this->buildController()->indexAction();

        $this->assertInstanceOf(ViewModel::class, $controller);
        $this->assertSame(AlreadyHasRegisteredCardController::PAGE_TEMPLATE, $controller->getTemplate());
    }

    public function testWhenGet_withInactiveSecurityCard_shouldRedirectToHome()
    {
        $inactiveSecurityCard = new SecurityCard((object) ['active' => false]);

        $this->withIdentity();
        $this->withSecurityCardForUser($inactiveSecurityCard);

        $controller = $this->buildController();

        $this->expectRedirect(UserHomeController::ROUTE);

        $controller->indexAction();
    }

    private function withActiveTwoFaCard($value, $identity)
    {
        return $this->securityCardGuard
            ->expects($this->once())
            ->method('hasActiveTwoFaCard')
            ->with($identity)
            ->willReturn($value);
    }

    private function withIdentity()
    {
        $identity = new Identity();
        $identity->setUserId(self::USER_ID);
        $identity->setUsername(self::USERNAME);

        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn($identity);

        return $identity;
    }

    private function withSecurityCardForUser(SecurityCard $securityCard)
    {
        return $this->securityCardService
            ->expects($this->any())
            ->method('getSecurityCardForUser')
            ->with(self::USERNAME)
            ->willReturn($securityCard);
    }

    private function buildController()
    {
        $controller = new AlreadyHasRegisteredCardController(
            $this->securityCardService,
            $this->contextProvider,
            $this->personalDetailsService,
            $this->catalogService,
            $this->identityProvider,
            $this->securityCardGuard
        );

        $this->setController($controller);
        $this->setUpPluginMocks();

        return $controller;
    }
}