<?php

namespace UserAdminTest\Controller;

use CoreTest\Controller\AbstractLightWebControllerTest;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use UserAdmin\Controller\EmailAddressController;
use UserAdmin\Form\ChangeEmailForm;
use UserAdmin\Service\HelpdeskAccountAdminService;
use UserAdmin\Service\IsEmailDuplicateService;
use Zend\Http\Request;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Model\ViewModel;

class EmailAddressControllerTest extends AbstractLightWebControllerTest
{
    const PERSON_ID = 107;
    const USER_NAME = 'username';

    /*** @var MotAuthorisationServiceInterface */
    private $authorisationService;

    /*** @var HelpdeskAccountAdminService */
    private $userAccountAdminService;

    /*** @var TesterGroupAuthorisationMapper */
    private $testerGroupAuthorisationMapper;

    /*** @var MapperFactory */
    private $mapperFactory;

    /** @var  PersonProfileUrlGenerator */
    private $personProfileUrlGenerator;

    /*** @var ContextProvider */
    private $contextProvider;

    /** @var  IsEmailDuplicateService */
    private $duplicateEmailService;

    /** @var  FeatureToggles */
    private $featureToggles;

    /** @var  TesterAuthorisation */
    private $testerAuthorisation;

    /** @var PersonHelpDeskProfileDto */
    private $personHelpDeskProfileDto;

    /** @var  Request */
    protected $request;

    /** @var  MotIdentityProviderInterface */
    private $identityProvider;

    public function setUp()
    {
        parent::setUp();
        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->userAccountAdminService = XMock::of(HelpdeskAccountAdminService::class);
        $this->testerGroupAuthorisationMapper = XMock::of(TesterGroupAuthorisationMapper::class);
        $this->mapperFactory = XMock::of(MapperFactory::class);
        $this->personProfileUrlGenerator = XMock::of(PersonProfileUrlGenerator::class);
        $this->contextProvider = XMock::of(ContextProvider::class);
        $this->duplicateEmailService = XMock::of(IsEmailDuplicateService::class);
        $this->featureToggles = XMock::of(FeatureToggles::class);
        $this->testerGroupAuthorisationMapper = XMock::of(TesterGroupAuthorisationMapper::class);
        $this->testerAuthorisation = XMock::of(TesterAuthorisation::class);
        $this->personHelpDeskProfileDto = XMock::of(PersonHelpDeskProfileDto::class);
        $this->request = XMock::of(Request::class);
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);
    }

    public function testWhenPost_formIsValid_emailIsNotDuplicated()
    {
        $this->withFeatureToggles(true);

        $this->withContext(ContextProvider::YOUR_PROFILE_CONTEXT);

        $this->withIdentity();

        $this->personProfileUrlGenerator
            ->expects($this->once())
            ->method('toPersonProfile')
            ->willReturn('your-profile');

        $this->getAuthorisationMock();

        $this->getUserProfileMock();

        $this->mockIsPost(true, $this->mockValidPostData());

        $this->mockIsDuplicate(false, 'valid@email.com');

        $this->expectRedirectToUrl('your-profile');

        $this->flashMessengerPluginMock
            ->expects($this->once())
            ->method('addSuccessMessage')
            ->with(EmailAddressController::MSG_EMAIL_CHANGED_SUCCESS);

        $this->buildController()->indexAction();
    }

    public function testWhenGet_newProfileFeatureNotEnabled()
    {
        $this->getAuthorisationMock();

        $this->getUserProfileMock();

        $this->request
            ->expects($this->any())
            ->method('getQuery')
            ->willReturn(new Parameters());

        $this->buildController()->indexAction();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Person with ID '0' is not allowed to change email with context 'user-search'
     */
    public function testWhenGet_notAllowedToChangeEmailError()
    {
        $this->withFeatureToggles(true);

        $this->withContext(ContextProvider::USER_SEARCH_CONTEXT);

        $this->buildController()->indexAction();
    }

    public function testWhenPost_formIsValid_emailIsDuplicated()
    {
        $this->withFeatureToggles(true);

        $this->withContext(ContextProvider::YOUR_PROFILE_CONTEXT);

        $this->withIdentity();

        $this->getAuthorisationMock();

        $this->getUserProfileMock();

        $this->mockIsPost(true, $this->mockValidPostData());

        $this->mockIsDuplicate(true, 'valid@email.com');

        $this->flashMessengerPluginMock
            ->expects($this->once())
            ->method('addErrorMessage')
            ->with(['duplicateEmailValidation' => EmailAddressController::MSG_DUPLICATE_EMAIL_ERROR]);

        $this->buildController()->indexAction();
    }

    public function testWhenGet_shouldDisplayChangeEmailPage()
    {
        $this->withFeatureToggles(true);

        $this->getAuthorisationMock();

        $this->getUserProfileMock();

        $this->withContext(ContextProvider::YOUR_PROFILE_CONTEXT);

        $this->withIdentity();

        $this->mockIsPost(false, []);

        $actual = $this->buildController()->indexAction();

        $this->assertInstanceOf(ViewModel::class, $actual);
        $this->assertSame('user-admin/email-address/form.phtml', $actual->getTemplate());
        $this->assertInstanceOf(ChangeEmailForm::class, $actual->getVariables()['viewModel']->getForm());
    }

    private function mockIsDuplicate($isDuplicate, $email)
    {
        return $this->duplicateEmailService
            ->expects($this->once())
            ->method('isEmailDuplicate')
            ->with($email)
            ->willReturn($isDuplicate);
    }

    private function mockValidPostData()
    {
        return [
            'email' => 'valid@email.com',
            'emailConfirm' => 'valid@email.com',
        ];
    }

    private function mockIsPost($isPost, $postData)
    {
        if ($isPost) {
            $params = XMock::of(ParametersInterface::class);
            $params->expects($this->once())
                ->method('toArray')
                ->willReturn($postData);

            $this->request->expects($this->once())->method('isPost')->willReturn($isPost);
            $this->request->expects($this->once())->method('getPost')->willReturn($params);
        } else {
            $this->request->expects($this->once())->method('isPost')->willReturn($isPost);
        }
    }

    private function getAuthorisationMock()
    {
        return $this->testerGroupAuthorisationMapper
            ->expects($this->once())
            ->method('getAuthorisation')
            ->willReturn($this->testerAuthorisation);
    }

    private function getUserProfileMock()
    {
        return $this->userAccountAdminService
            ->expects($this->once())
            ->method('getUserProfile')
            ->willReturn($this->personHelpDeskProfileDto);
    }

    private function buildController()
    {
        $controller = new EmailAddressController(
            $this->authorisationService,
            $this->userAccountAdminService,
            $this->testerGroupAuthorisationMapper,
            $this->mapperFactory,
            $this->personProfileUrlGenerator,
            $this->contextProvider,
            $this->duplicateEmailService,
            $this->featureToggles,
            $this->request,
            $this->identityProvider
        );

        $this->setController($controller);

        return $controller;
    }

    private function withFeatureToggles($isEnabled)
    {
        $this->featureToggles
            ->expects($this->any())
            ->method('isEnabled')
            ->with(FeatureToggle::NEW_PERSON_PROFILE)
            ->willReturn($isEnabled);
    }

    private function withIdentity()
    {
        $identity = new Identity();
        $identity->setUserId(self::PERSON_ID);

        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn($identity);
    }

    private function withContext($context)
    {
        $this->contextProvider
            ->method('getContext')
            ->willReturn($context);
    }
}
