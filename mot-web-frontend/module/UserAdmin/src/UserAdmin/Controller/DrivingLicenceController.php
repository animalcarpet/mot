<?php

namespace UserAdmin\Controller;

use DvsaCommon\Validator\DrivingLicenceValidator;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use UserAdmin\Presenter\DrivingLicenceSummaryPresenter;
use UserAdmin\Service\HelpdeskAccountAdminService;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;
use UserAdmin\Service\PersonRoleManagementService;
use Zend\View\Model\ViewModel;
use UserAdmin\Presenter\UserProfilePresenter;
use UserAdmin\ViewModel\UserProfile\TesterAuthorisationViewModel;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\Role;
use Core\Service\SessionService;
use DvsaCommon\Exception\UnauthorisedException;
use Exception;
use Dvsa\Mot\Frontend\PersonModule\Controller\PersonProfileController;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;

/**
 * Class DrivingLicenceController.
 */
class DrivingLicenceController extends AbstractDvsaMotTestController
{
    const PAGE_SUBTITLE = 'User profile';
    const MSG_DRIVING_LICENCE_CHANGED_SUCCESSFULLY = 'Driving licence has been changed successfully.';
    const MSG_DRIVING_LICENCE_CHANGED_FAILURE = 'Driving licence could not be changed. Please try again.';
    const MSG_DRIVING_LICENCE_REMOVE_SUCCESSFUL = 'Driving licence has been successfully removed.';
    const MSG_DRIVING_LICENCE_REMOVE_FAILURE = 'Driving licence could not be removed. Please try again.';
    const SESSION_STORAGE_KEY_PREFIX = 'CHANGE_PERSON_DRIVING_LICENCE';

    const DRIVING_LICENCE_DELETE_TEMPLATE = 'user-admin/driving-licence/delete.phtml';

    const NEW_PROFILE_URL = 'user-admin/user/{:id}';
    const NEW_PROFILE_ROUTE = 'newProfileUserAdmin';
    const NEW_PROFILE_LICENCE_SUMMARY_ROUTE = 'newProfileUserAdmin/driving-licence-change/summary';

    /**
     * @var HelpdeskAccountAdminService
     */
    private $accountAdminService;

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var TesterGroupAuthorisationMapper
     */
    private $testerGroupAuthorisationMapper;

    /**
     * @var SessionService
     */
    private $sessionService;

    /**
     * @var PersonRoleManagementService
     */
    private $personRoleManagementService;

    /**
     * @var null|array
     */
    private $validationErrors;

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    /**
     * @param HelpdeskAccountAdminService      $accountAdminService
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param TesterGroupAuthorisationMapper   $testerGroupAuthorisationMapper
     * @param SessionService                   $sessionService
     * @param PersonRoleManagementService      $personRoleManagementService
     * @param ContextProvider                  $contextProvider
     */
    public function __construct(
        HelpdeskAccountAdminService $accountAdminService,
        MotAuthorisationServiceInterface $authorisationService,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        SessionService $sessionService,
        PersonRoleManagementService $personRoleManagementService,
        ContextProvider $contextProvider
    ) {
        $this->accountAdminService = $accountAdminService;
        $this->authorisationService = $authorisationService;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->sessionService = $sessionService;
        $this->personRoleManagementService = $personRoleManagementService;
        $this->contextProvider = $contextProvider;
    }

    /**
     * Handle DVSA user adding and editing of a persons driving licence.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::ADD_EDIT_DRIVING_LICENCE);

        $personId = $this->getPersonId();

        $profile = $this->accountAdminService->getUserProfile($personId);
        $presenter = $this->createPresenter($personId);

        if ($presenter->hasDvsaRoles()) {
            throw new UnauthorisedException('DVSA cannot modify a DVSA user\'s driving licence');
        }

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageTitle', 'Change driving licence');
        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE);

        if ($data = $this->sessionService->load(self::SESSION_STORAGE_KEY_PREFIX.$personId)) {
            // If we've come from the summary window, we want to load the licence data stored in the session
            $drivingLicenceNumber = $data['drivingLicenceNumber'];
            $drivingLicenceRegion = $data['drivingLicenceRegion'];
        } else {
            // Otherwise load the licence data from the user profile
            $drivingLicenceNumber = $profile->getDrivingLicenceNumber();
            $drivingLicenceRegion = $profile->getDrivingLicenceRegion();
        }

        if ($this->getRequest()->isPost()) {
            $params = [
                'drivingLicenceNumber' => $this->getRequest()->getPost('drivingLicenceNumber'),
                'drivingLicenceRegion' => $this->getRequest()->getPost('drivingLicenceRegion'),
            ];

            if ($this->validate($params) && $this->saveToSession($personId, $params)) {
                return $this->redirect()->toRoute(self::NEW_PROFILE_LICENCE_SUMMARY_ROUTE, ['id' => $personId]);
            } else {
                // Set the driving licence number and region to the one that was submitted to re-display
                $drivingLicenceNumber = $this->getRequest()->getPost('drivingLicenceNumber');
                $drivingLicenceRegion = $this->getRequest()->getPost('drivingLicenceRegion');
            }
        }

        $this->layout()->setVariable('breadcrumbs', [
            'breadcrumbs' => $this->getBreadcrumbBase($presenter, $personId),
        ]);

        return new ViewModel([
            'presenter' => $presenter,
            'drivingLicenceNumber' => $drivingLicenceNumber,
            'drivingLicenceRegion' => $drivingLicenceRegion,
            'errors' => $this->validationErrors,
        ]);
    }

    /**
     * Summary screen for changing user's driving licence.
     *
     * @return ViewModel
     */
    public function summaryAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::ADD_EDIT_DRIVING_LICENCE);

        $personId = $this->params()->fromRoute('id');

        $request = $this->getRequest();

        // If there is no summary data stored in the session for this user, redirect to their profile
        if (!$data = $this->sessionService->load(self::SESSION_STORAGE_KEY_PREFIX.$personId)) {
            return $this->redirect()->toUrl(UserAdminUrlBuilderWeb::of()->userProfile($personId));
        }

        if ($request->isPost()) {
            if ($this->validate($data)) {
                try {
                    $this->accountAdminService->updateDrivingLicence(
                        $personId,
                        $data['drivingLicenceNumber'],
                        $data['drivingLicenceRegion']
                    );

                    $this->sessionService->save(self::SESSION_STORAGE_KEY_PREFIX.$personId, null);
                    $this->flashMessenger()->addSuccessMessage(self::MSG_DRIVING_LICENCE_CHANGED_SUCCESSFULLY);
                } catch (Exception $e) {
                    $this->flashMessenger()->addErrorMessage(self::MSG_DRIVING_LICENCE_CHANGED_FAILURE);
                }
            }

            // Redirect to the user profile on success or failure and display a relevant message
            return $this->redirect()->toRoute(self::NEW_PROFILE_ROUTE, ['id' => $personId]);
        }

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageTitle', 'Review driving licence');
        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE);

        $presenter = $this->createPresenter($personId);
        $breadcrumbs = $breadcrumbs = $this->getBreadcrumbBase($presenter, $personId);

        $this->layout()->setVariable('breadcrumbs', [
            'breadcrumbs' => $breadcrumbs,
        ]);

        return new ViewModel([
            'presenter' => (new DrivingLicenceSummaryPresenter())->setPersonId($personId),
            'drivingLicenceNumber' => $data['drivingLicenceNumber'],
            'drivingLicenceRegion' => $data['drivingLicenceRegion'],
        ]);
    }

    public function deleteAction()
    {
        $personId = $this->params()->fromRoute('id');

        $profile = $this->accountAdminService->getUserProfile($personId);
        $presenter = $this->createPresenter($personId);

        if ($presenter->hasDvsaRoles()) {
            throw new UnauthorisedException('DVSA cannot modify a DVSA user\'s driving licence');
        }

        if ($profile->getDrivingLicenceNumber() === '') {
            // Redirect to the user profile if no licence is associated with account
            $redirectUrl = UserAdminUrlBuilderWeb::of()->userProfile($personId);

            return $this->redirect()->toUrl($redirectUrl);
        }

        $personId = $this->params()->fromRoute('id');
        $request = $this->getRequest();
        $profile = $this->accountAdminService->getUserProfile($personId);

        if ($data = $this->sessionService->load(self::SESSION_STORAGE_KEY_PREFIX.$personId)) {
            // If we've come from the summary window, we want to load the licence data stored in the session
            $drivingLicenceNumber = $data['drivingLicenceNumber'];
            $drivingLicenceRegion = $data['drivingLicenceRegion'];
        } else {
            // Otherwise load the licence data from the user profile
            $drivingLicenceNumber = $profile->getDrivingLicenceNumber();
            $drivingLicenceRegion = $profile->getDrivingLicenceRegionCode();
        }

        if ($request->isPost()) {
            try {
                $this->accountAdminService->deleteDrivingLicence($personId);
                $this->sessionService->save(self::SESSION_STORAGE_KEY_PREFIX.$personId, null);
                $this->flashMessenger()->addSuccessMessage(self::MSG_DRIVING_LICENCE_REMOVE_SUCCESSFUL);
            } catch (Exception $e) {
                $this->flashMessenger()->addErrorMessage(self::MSG_DRIVING_LICENCE_REMOVE_FAILURE);
            }

            // Redirect to the user profile on success or failure and display a relevant message
            return $this->redirect()->toRoute(self::NEW_PROFILE_ROUTE, ['id' => $personId]);
        }

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageTitle', 'Remove driving licence');
        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE);

        $presenter = $this->createPresenter($personId);

        $breadcrumbs = $this->getBreadcrumbBase($presenter, $personId);

        $this->layout()->setVariable(
            'breadcrumbs',
            [
                'breadcrumbs' => $breadcrumbs,
            ]
        );

        $backButtonUrl = $this->url()->fromRoute('newProfileUserAdmin/driving-licence-change', ['id' => $personId]);

        return new ViewModel(
            [
                'presenter' => new DrivingLicenceSummaryPresenter(),
                'drivingLicenceNumber' => $drivingLicenceNumber,
                'drivingLicenceRegion' => $drivingLicenceRegion,
                'backButtonUrl' => $backButtonUrl,
                'fullName' => $profile->getFirstName().' '.$profile->getMiddleName().' '.$profile->getLastName(),
                'title' => $profile->getTitle(),
            ],
            ['template' => self::DRIVING_LICENCE_DELETE_TEMPLATE]
        );
    }

    /**
     * Validates a driving licence is correct format for the region.
     *
     * This method expects that the fields `drivingLicenceNumber` and `drivingLicenceRegion`
     * will be in the POST data of the request. Not providing either of these values will
     * results in validation failing. if the values do not exist in POST, when retrieved they
     * will be null, and also fail validation
     *
     * @param array $params
     *
     * @return bool
     */
    private function validate(array $params)
    {
        $validator = new DrivingLicenceValidator();
        if (!$validator->isValid($params)) {
            $this->validationErrors = $validator->getMessages();
            foreach ($this->validationErrors as $field => $errorMessage) {
                $message = $validator->getFieldLabel($field).' - '.$errorMessage;
                $this->flashMessenger()->addErrorMessage([$message]);
            }

            return false;
        }

        return true;
    }

    /**
     * Save the driving licence number and region to the session.
     *
     * @param int   $personId
     * @param array $params
     *
     * @return bool
     */
    private function saveToSession($personId, array $params)
    {
        $this->sessionService->save(self::SESSION_STORAGE_KEY_PREFIX.$personId, $params);

        return true;
    }

    /**
     * @param $personId
     *
     * @return UserProfilePresenter
     */
    private function createPresenter($personId)
    {
        $presenter = new UserProfilePresenter(
            $this->accountAdminService->getUserProfile($personId),
            $this->getTesterAuthorisationViewModel($personId),
            null,
            $this->authorisationService->isGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE_DVSA_USER) &&
            !$this->authorisationService->hasRole(Role::CUSTOMER_SERVICE_CENTRE_OPERATIVE),
            $this->personRoleManagementService
        );

        $presenter->setPersonId($personId);

        return $presenter;
    }

    /**
     * @param $personId
     *
     * @return TesterAuthorisationViewModel
     */
    private function getTesterAuthorisationViewModel($personId)
    {
        return new TesterAuthorisationViewModel(
            $personId,
            $this->testerGroupAuthorisationMapper->getAuthorisation($personId),
            $this->authorisationService
        );
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

    private function getBreadcrumbBase($presenter, $personId)
    {
        return [
            PersonProfileController::CONTENT_HEADER_TYPE__USER_SEARCH => $this->url()->fromRoute('user_admin/user-search'),
            $presenter->displayTitleAndFullName() => $this->url()->fromRoute('newProfileUserAdmin', ['id' => $personId]),
            'Change driving licence' => '',
        ];
    }
}
