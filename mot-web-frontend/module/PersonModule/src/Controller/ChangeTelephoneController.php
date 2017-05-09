<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModule\Controller;

use Application\Data\ApiPersonalDetails;
use Core\Controller\AbstractAuthActionController;
use Core\Service\SessionService;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;
use DvsaCommon\Validator\TelephoneNumberValidator;
use UserAdmin\Service\HelpdeskAccountAdminService;
use UserAdmin\Service\PersonRoleManagementService;
use Zend\View\Model\ViewModel;

/**
 * Controller for handling the update of Person's telephone number.
 */
class ChangeTelephoneController extends AbstractAuthActionController
{
    const PHONE_NUMBER_KEY = 'personTelephone';
    const PHONE_NUMBER_FORM_ID = 'personTelephone';
    const SESSION_STORAGE_KEY_PREFIX = 'CHANGE_PERSON_TELEPHONE_NUMBER';
    const MSG_PHONE_NUMBER_CHANGED_SUCCESSFULLY = 'Telephone number has been changed successfully.';
    const MSG_PHONE_NUMBER_CHANGED_FAILURE = 'Telephone number could not be changed. Please try again.';

    /**
     * @var HelpdeskAccountAdminService
     */
    private $accountAdminService;

    /**
     * @var TesterGroupAuthorisationMapper
     */
    private $testerGroupAuthorisationMapper;

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    /**
     * @var SessionService
     */
    private $sessionService;

    /**
     * @var PersonRoleManagementService
     */
    private $personRoleManagementService;

    /** @var PersonProfileUrlGenerator */
    private $personProfileUrl;

    /** @var ApiPersonalDetails */
    private $personalDetailsService;

    /** @var PersonProfileGuardBuilder */
    private $personProfileGuardBuilder;

    /** @var MapperFactory */
    private $mapperFactory;

    /**
     * @var null|array
     */
    private $validationErrors;

    public function __construct(
        HelpdeskAccountAdminService $accountAdminService,
        TesterGroupAuthorisationMapper $authMapper,
        ContextProvider $contextProvider,
        SessionService $sessionService,
        PersonRoleManagementService $personRoleManagementService,
        PersonProfileUrlGenerator $personProfileUrl,
        ApiPersonalDetails $personalDetailsService,
        PersonProfileGuardBuilder $personProfileGuardBuilder,
        MapperFactory $mapperFactory
    ) {
        $this->accountAdminService = $accountAdminService;
        $this->testerGroupAuthorisationMapper = $authMapper;
        $this->contextProvider = $contextProvider;
        $this->sessionService = $sessionService;
        $this->personRoleManagementService = $personRoleManagementService;
        $this->personProfileUrl = $personProfileUrl;
        $this->personalDetailsService = $personalDetailsService;
        $this->personProfileGuardBuilder = $personProfileGuardBuilder;
        $this->mapperFactory = $mapperFactory;
    }

    public function indexAction()
    {
        $personId = $this->getPersonId();

        /** @var PersonHelpDeskProfileDto $profile */
        $profile = $this->accountAdminService->getUserProfile($personId);
        $context = $this->contextProvider->getContext();
        $personalDetails = new PersonalDetails($this
            ->personalDetailsService
            ->getPersonalDetailsData($personId));

        $personProfileGuard = $this->personProfileGuardBuilder->createPersonProfileGuard(
            $personalDetails,
            $context
        );

        if (!$personProfileGuard->canChangeTelephoneNumber()) {
            throw new UnauthorisedException('Permission denied for editing telephone number');
        }

        $viewingOwnProfile = $personProfileGuard->isViewingOwnProfile();
        $subtitle = ($viewingOwnProfile ? 'Your' : 'User').' profile';

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageTitle', 'Change telephone number');
        $this->layout()->setVariable('pageSubTitle', $subtitle);
        $this->setHeadTitle('Change telephone number');

        if ($data = $this->sessionService->load(self::SESSION_STORAGE_KEY_PREFIX.$personId)) {
            // If we've come from the summary window, we want to load the phone data stored in the session
            $phoneNumber = $data[self::PHONE_NUMBER_KEY];
        } else {
            // Otherwise load the phone data from the user profile
            $phoneNumber = $profile->getTelephone();
        }

        if ($this->getRequest()->isPost()) {
            $params = [
                self::PHONE_NUMBER_KEY => $this->getRequest()->getPost(self::PHONE_NUMBER_FORM_ID),
            ];

            if ($this->validate($params)) {
                try {
                    $this->accountAdminService->editTelephoneNumber(
                        $personId,
                        $params[self::PHONE_NUMBER_KEY]
                    );

                    $this->flashMessenger()->addSuccessMessage(self::MSG_PHONE_NUMBER_CHANGED_SUCCESSFULLY);
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage(self::MSG_PHONE_NUMBER_CHANGED_FAILURE);
                }

                return $this->redirect()->toUrl($this->personProfileUrl->toPersonProfile());
            } else {
                $phoneNumber = $this->getRequest()->getPost(self::PHONE_NUMBER_KEY);
            }
        }

        $this->layout()->setVariable('breadcrumbs', [
            'breadcrumbs' => $this->getBreadcrumbs($personalDetails, $personId, $viewingOwnProfile),
        ]);

        $viewModel = new ViewModel();
        $viewModel->setTemplate('profile/change-telephone/index.phtml');
        $viewModel->setVariable('phoneNumber', $phoneNumber);
        $viewModel->setVariable('viewingOwnProfile', $viewingOwnProfile);
        $viewModel->setVariable('errors', $this->validationErrors);

        return $viewModel;
    }

    /**
     * @param PersonalDetails $personalDetails
     * @param int|string      $personId
     *
     * @return array
     */
    private function getBreadcrumbs($personalDetails, $personId, $isProfile = false)
    {
        $breadcrumbs = [];
        $personName = $personalDetails->getFullName();
        $context = $this->contextProvider->getContext();

        if (ContextProvider::YOUR_PROFILE_CONTEXT === $context) {
            /*
             * Your Profile context.
             */
            $profileUrl = $isProfile === false ?
                $this->url()->fromRoute('newProfile', ['id' => $personId]) : '';
            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__YOUR_PROFILE => $profileUrl];
        } elseif (ContextProvider::USER_SEARCH_CONTEXT === $context) {
            /*
             * User search context.
             */
            $userSearchUrl = $this->url()->fromRoute('user_admin/user-search');
            $profileUrl = $isProfile === false ?
                $this->url()->fromRoute(ContextProvider::USER_SEARCH_PARENT_ROUTE, ['id' => $personId]) : '';

            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__USER_SEARCH => $userSearchUrl];
            $breadcrumbs += [$personName => $profileUrl];
        } elseif (ContextProvider::AE_CONTEXT === $context) {
            /*
             * AE context.
             */
            $aeId = $this->params()->fromRoute('authorisedExaminerId');
            $ae = $this->mapperFactory->Organisation->getAuthorisedExaminer($aeId);
            $aeUrl = $this->url()->fromRoute('authorised-examiner', ['id' => $aeId]);
            $breadcrumbs += [$ae->getName() => $aeUrl];

            $profileUrl = $isProfile === false ?
                $this->url()->fromRoute(ContextProvider::AE_PARENT_ROUTE, [
                    'authorisedExaminerId' => $aeId,
                    'id' => $personId,
                ]) : '';
            $breadcrumbs += [$personName => $profileUrl];
        } elseif (ContextProvider::VTS_CONTEXT === $context) {
            /*
             * VTS context.
             */
            $vtsId = $this->params()->fromRoute('vehicleTestingStationId');
            $vts = $this->mapperFactory->Site->getById($vtsId);
            $ae = $vts->getOrganisation();

            if ($ae) {
                $aeUrl = $this->url()->fromRoute('authorised-examiner', ['id' => $ae->getId()]);
                $breadcrumbs += [$ae->getName() => $aeUrl];
            }

            $vtsUrl = $this->url()->fromRoute('vehicle-testing-station', ['id' => $vtsId]);
            $breadcrumbs += [$vts->getName() => $vtsUrl];
            $profileUrl = $isProfile === false ?
                $this->url()->fromRoute(ContextProvider::VTS_PARENT_ROUTE, [
                    'vehicleTestingStationId' => $vtsId,
                    'id' => $personId,
                ]) : '';
            $breadcrumbs += [$personName => $profileUrl];
        } else {
            $userSearchUrl = $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->userSearch());
            $profileUrl = $isProfile === false ?
                $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->UserProfile($personId)) : '';
            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__USER_SEARCH => $userSearchUrl, $personName => $profileUrl];
        }

        $breadcrumbs += ['Change telephone number' => ''];

        return $breadcrumbs;
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    private function validate($params)
    {
        $validator = new TelephoneNumberValidator();
        $phoneNumber = $params[self::PHONE_NUMBER_KEY];

        if ($validator->isValid($phoneNumber)) {
            return true;
        }

        $this->validationErrors = $validator->getMessages();

        foreach ($this->validationErrors as $field => $errorMessage) {
            $message = $validator->getFieldLabel($field).' - '.$errorMessage;
            $this->flashMessenger()->addErrorMessage([$message]);
        }

        return false;
    }

    /**
     * @return int
     */
    private function getPersonId()
    {
        $context = $this->contextProvider->getContext();

        return $context === ContextProvider::YOUR_PROFILE_CONTEXT ?
            $this->getIdentity()->getUserId() : (int) $this->params()->fromRoute('id', null);
    }

    /**
     * Build a url with the query params.
     *
     * @param string $url
     *
     * @return string
     */
    private function buildUrlWithCurrentSearchQuery($url)
    {
        $params = $this->getRequest()->getQuery()->toArray();
        if (empty($params)) {
            return $url;
        }

        return $url.'?'.http_build_query($params);
    }
}
