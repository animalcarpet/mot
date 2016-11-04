<?php

namespace UserAdmin\Controller;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;
use UserAdmin\Service\PersonRoleManagementService;
use Zend\View\Model\ViewModel;

class PersonRoleController extends AbstractAuthActionController
{
    const URL_MANAGE_INTERNAL_ROLE = 'user_admin/user-profile/manage-user-internal-role';

    const ERR_MSG_TRADE_ROLE_OWNER = 'Its not possible to assign an "internal" role to a "trade" role owner';

    /** @var MotAuthorisationServiceInterface */
    private $authorisationService;

    /** @var PersonRoleManagementService */
    private $personRoleManagementService;

    /**
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param PersonRoleManagementService      $personRoleManagementService
     */
    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        PersonRoleManagementService $personRoleManagementService
    ) {
        $this->authorisationService = $authorisationService;
        $this->personRoleManagementService = $personRoleManagementService;
    }

    public function addInternalRoleAction()
    {
        $this->personRoleManagementService->forbidManagementOfSelf($this->getPersonIdFromRoute());
        $roleName = $this->getCatalogService()->getPersonSystemRoles()[$this->getPersonSystemRoleIdFromRoute()]['name'];

        if ($this->hasBeenConfirmed()) {
            $return = $this->personRoleManagementService->addRole(
                $this->getPersonIdFromRoute(),
                $this->getPersonSystemRoleIdFromRoute()
            );

            if ($return === true) {
                $this->addSuccessMessage(sprintf("%s role has been added", $roleName));
            } else {
                $this->addErrorMessage(sprintf("There has been an error trying to add role %s", $roleName));
            }

            $redirectUrl = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ?
                $this->url()->fromRoute('newProfileUserAdmin/manage-user-internal-role', ['id' => $this->getPersonIdFromRoute()]) :
                UserAdminUrlBuilderWeb::personInternalRoleManagement($this->getPersonIdFromRoute());

            $this->redirect()->toUrl($redirectUrl);
        } else {
            $this->layout()->setVariables(
                [
                    'pageSubTitle' => 'User profile',
                    'pageTitle' => 'Add role',
                    'breadcrumbs' => $this->getBreadcrumbs('Manage roles'),
                ]
            )->setTemplate('layout/layout-govuk.phtml');

            $viewModel = new ViewModel(
                [
                    'personId' => $this->getPersonIdFromRoute(),
                    'roleName' => $roleName,
                    'personName' => $this->getPersonNameForBreadcrumbs(),
                    'urlManageInternalRoles' => UserAdminUrlBuilderWeb::personInternalRoleManagement(
                        $this->getPersonIdFromRoute()
                    ),
                ]
            );

            return $viewModel;
        }
    }

    /**
     * @return ViewModel
     */
    public function removeInternalRoleAction()
    {
        $this->personRoleManagementService->forbidManagementOfSelf($this->getPersonIdFromRoute());

        if ($this->hasBeenConfirmed()) {
            $this->personRoleManagementService->removeRole(
                $this->getPersonIdFromRoute(),
                $this->getPersonSystemRoleIdFromRoute()
            );

            $roleName = $this->getCatalogService()
                            ->getPersonSystemRoles()[$this->getPersonSystemRoleIdFromRoute()]['name'];

            $this->addSuccessMessage(sprintf("%s has been removed", $roleName));

            $redirectUrl = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ?
                $this->url()->fromRoute('newProfileUserAdmin/manage-user-internal-role', ['id' => $this->getPersonIdFromRoute()]) :
                UserAdminUrlBuilderWeb::personInternalRoleManagement($this->getPersonIdFromRoute());

            $this->redirect()->toUrl($redirectUrl);
        } else {
            $this->layout()->setVariables(
                [
                    'pageSubTitle' => 'User profile',
                    'pageTitle' => 'Remove role',
                    'progressBar' => ['breadcrumbs' => $this->getBreadcrumbs('Manage roles')],
                ]
            )->setTemplate('layout/layout-govuk.phtml');

            $viewModel = new ViewModel(
                [
                    'personId' => $this->getPersonIdFromRoute(),
                    'roleName' => $this->getCatalogService()
                        ->getPersonSystemRoles()[$this->getPersonSystemRoleIdFromRoute()]['name'],
                    'personName' => $this->getPersonNameForBreadcrumbs(),
                    'urlManageInternalRoles' => UserAdminUrlBuilderWeb::personInternalRoleManagement(
                        $this->getPersonIdFromRoute()
                    ),
                ]
            );

            return $viewModel;
        }
    }

    public function manageInternalRoleAction()
    {
        $this->layout()->setVariables(
            [
                'pageSubTitle' => 'User profile',
                'pageTitle' => 'Manage roles',
                'breadcrumbs' => $this->getBreadcrumbs('Manage roles'),
            ]
        )->setTemplate('layout/layout-govuk.phtml');

        $this->personRoleManagementService->forbidManagementOfSelf($this->getPersonIdFromRoute());

        $assignedInternalRoles = $this->personRoleManagementService->getPersonAssignedInternalRoles(
            $this->getPersonIdFromRoute(), $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE)
        );

        $manageableInternalRoles = $this->personRoleManagementService->getPersonManageableInternalRoles(
            $this->getPersonIdFromRoute(), $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE)
        );

        $viewModel = new ViewModel(
            [
                'currentInternalRoles' => $assignedInternalRoles,
                'manageableInternalRoles' => $manageableInternalRoles,
                'personProfileUrl' => $this->getPersonProfileUrl(),
            ]
        );

        return $viewModel;
    }

    /**
     * Checks to make sure that the form has been posted.
     *
     * @return bool
     */
    private function hasBeenConfirmed()
    {
        return ($this->request->isPost() === true);
    }

    /**
     * @return int
     */
    private function getPersonIdFromRoute()
    {
        return $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ?
            $this->params()->fromRoute('id') :
            $this->params()->fromRoute('personId');
    }

    /**
     * @return int
     */
    private function getPersonSystemRoleIdFromRoute()
    {
        return $this->params()->fromRoute('personSystemRoleId');
    }

    /**
     * @return string
     */
    private function getPersonProfileUrl()
    {
        return $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ?
            $this->url()->fromRoute('newProfileUserAdmin', ['id' => $this->getPersonIdFromRoute()]) :
            UserAdminUrlBuilderWeb::of()->userProfile($this->getPersonIdFromRoute())->toString();
    }

    /**
     * Prepare required array for the breadcrumbs based on the given page name.
     *
     * @param string $pageName
     *
     * @return array
     */
    private function getBreadcrumbs($pageName)
    {
        return [
            'breadcrumbs' => [
                    'User search' => UserAdminUrlBuilderWeb::of()->userSearch(),
                    $this->getPersonNameForBreadcrumbs() => $this->getPersonProfileUrl(),
                    $pageName => '',
                ],
        ];
    }

    /**
     * Concatenate person title, first, middle and last name.
     *
     * @return string
     */
    private function getPersonNameForBreadcrumbs()
    {
        $isNewUserProfileEnabled = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE);
        $person = $this->personRoleManagementService->getUserProfile($this->getPersonIdFromRoute(), $isNewUserProfileEnabled);

        return implode(
            ' ',
            array_filter(
                [
                    $person->getTitle(),
                    $person->getFirstName(),
                    $person->getMiddleName(),
                    $person->getLastName(),
                ]
            )
        );
    }
}
