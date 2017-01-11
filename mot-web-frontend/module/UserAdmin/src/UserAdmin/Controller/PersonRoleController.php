<?php

namespace UserAdmin\Controller;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
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
     * @param PersonRoleManagementService $personRoleManagementService
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
        $roleName = $this->getCatalogService()->getPersonSystemRoles()[$this->getPersonSystemRoleIdFromRoute()]['name'];

        if($this->hasBeenConfirmed()) {
            $return = $this->personRoleManagementService->addRole(
               $this->getPersonIdFromRoute(),
               $this->getPersonSystemRoleIdFromRoute()
            );

            if($return === true) {
                $this->addSuccessMessage(sprintf("%s role has been added", $roleName));
            } else {
                $this->addErrorMessage(sprintf("There has been an error trying to add role %s", $roleName));
            }
            $this->redirect()->toUrl(UserAdminUrlBuilderWeb::personInternalRoleManagement($this->getPersonIdFromRoute()));
        } else {
            $this->layout()->setVariables(
                [
                    'pageSubTitle' => 'User profile',
                    'pageTitle' => 'Manage roles',
                    'breadcrumbs' => $this->getBreadcrumbs('Manage roles'),
                ]
            )->setTemplate('layout/layout-govuk.phtml');

            $viewModel = new ViewModel(
                [
                    'roleName' => $roleName,
                    'personName' => $this->getPersonNameForBreadcrumbs(),
                    'urlManageInternalRoles' => UserAdminUrlBuilderWeb::personInternalRoleManagement($this->getPersonIdFromRoute()),
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

        if ($this->hasBeenConfirmed()) {
            $this->personRoleManagementService->removeRole(
                $this->getPersonIdFromRoute(),
                $this->getPersonSystemRoleIdFromRoute()
            );

            $roleName = $this->getCatalogService()->getPersonSystemRoles()[$this->getPersonSystemRoleIdFromRoute()]['name'];

            $this->addSuccessMessage(sprintf("%s has been removed", $roleName));
            $this->redirect()->toUrl(
                UserAdminUrlBuilderWeb::personInternalRoleManagement($this->getPersonIdFromRoute())
            );
        } else {
            $this->layout()->setVariables(
                [
                    'pageSubTitle' => 'User profile',
                    'pageTitle' => 'Manage roles',
                    'progressBar' => ['breadcrumbs' => $this->getBreadcrumbs('Manage roles')],
                ]
            )->setTemplate('layout/layout-govuk.phtml');

            $viewModel = new ViewModel(
                [
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

        $assignedInternalRoles = $this->personRoleManagementService->getPersonAssignedInternalRoles(
            $this->getPersonIdFromRoute()
        );

        $manageableInternalRoles = $this->personRoleManagementService->getPersonManageableInternalRoles(
            $this->getPersonIdFromRoute()
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
     * Checks to make sure that the form has been posted
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
        return $this->params()->fromRoute('personId');
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
        return UserAdminUrlBuilderWeb::of()->userProfile($this->getPersonIdFromRoute())->toString();
    }

    /**
     * Prepare required array for the breadcrumbs based on the given page name
     *
     * @param string $pageName
     * @return array
     */
    private function getBreadcrumbs($pageName)
    {
        return [
            'breadcrumbs' =>
                [
                    'User search' => UserAdminUrlBuilderWeb::of()->userSearch(),
                    $this->getPersonNameForBreadcrumbs() => $this->getPersonProfileUrl(),
                    $pageName => '',
                ]
        ];
    }

    /**
     * Concatenate person title, first, middle and last name
     *
     * @return string
     */
    private function getPersonNameForBreadcrumbs()
    {
        $person = $this->personRoleManagementService->getUserProfile($this->getPersonIdFromRoute());

        return join(
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
