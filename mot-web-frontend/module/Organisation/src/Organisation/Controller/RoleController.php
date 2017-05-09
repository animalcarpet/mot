<?php

namespace Organisation\Controller;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Dto\Organisation\OrganisationPositionDto;
use DvsaCommon\Enum\OrganisationBusinessRoleName;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\Validator\UsernameValidator;
use Organisation\Form\SelectRoleForm;
use Organisation\Traits\OrganisationServicesTrait;
use Organisation\ViewModel\View\Role\RemoveRoleConfirmationViewModel;
use Zend\View\Model\ViewModel;
use HTMLPurifier;
use DvsaCommon\Auth\PermissionAtOrganisation;

/**
 * Class RoleController.
 */
class RoleController extends AbstractAuthActionController
{
    use OrganisationServicesTrait;

    const ROUTE_LIST_USER_ROLES = 'authorised-examiner/list-user-roles';
    const ROUTE_REMOVE_ROLE = 'authorised-examiner/remove-role';
    const ROUTE_ROLES = 'authorised-examiner/roles';

    /**
     * @var \HTMLPurifier
     */
    protected $htmlPurifier;

    /**
     * @var UsernameValidator
     */
    protected $usernameValidator;

    /**
     * @param UsernameValidator $usernameValidator
     * @param HTMLPurifier      $htmlPurifier
     */
    public function __construct(UsernameValidator $usernameValidator, HTMLPurifier $htmlPurifier)
    {
        $this->usernameValidator = $usernameValidator;
        $this->htmlPurifier = $htmlPurifier;
    }

    /**
     * @return array|\Zend\Http\Response
     */
    public function indexAction()
    {
        $this->layout('layout/layout-govuk.phtml');

        $form = [];
        $mapperFactory = $this->getMapperFactory();
        $organisationId = $this->params('id');
        $organisation = $mapperFactory->Organisation->getAuthorisedExaminer($organisationId);
        $personLogin = '';
        $userNotFound = false;

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();
            $personLogin = $this->htmlPurifier->purify($postData['userSearchBox']);

            /*
             * Early validate our search box right here in the Frontend.
             *
             * This solves getting a "414 Request-URI Too Long" response from the API when the search box is overloaded
             * and not being able to inform the user about the maximum allowed length for the username.
             */
            if (true === $this->usernameValidator->isValid($personLogin)) {
                try {
                    $person = $mapperFactory->Person->getByIdentifier($personLogin);
                    if ($person) {
                        return $this->redirect()->toRoute(
                            self::ROUTE_LIST_USER_ROLES,
                            [
                                'id' => $organisationId,
                                'personId' => $person->getId(),
                            ]
                        );
                    }
                } catch (ValidationException $e) {
                    $errors = $e->getErrors();
                    $this->addFlashValidationErrors($errors['problem']['validation_messages']);
                } catch (UnauthorisedException $e) {
                    $userNotFound = true;
                } catch (GeneralRestException $e) {
                    $userNotFound = true;
                }
            } else {
                $this->addFlashValidationErrors($this->usernameValidator->getMessages());
            }
        }

        return [
            'form' => $form,
            'id' => $organisationId,
            'organisation' => $organisation,
            'personId' => $personLogin,
            'userNotFound' => $userNotFound,
        ];
    }

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function listUserRolesAction()
    {
        $organisationId = $this->params('id');
        $personId = $this->params('personId');

        $mapperFactory = $this->getMapperFactory();
        $person = $mapperFactory->Person->getById($personId);
        $personUsername = $person->getUsername();
        $personFullName = $person->getFullName();
        $roles = $mapperFactory->OrganisationRole->fetchAllForPerson($organisationId, $personId);

        $form = $this->getSelectRoleForm($roles);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                return $this->redirect()->toRoute(
                    'authorised-examiner/confirm-nomination',
                    [
                        'nomineeId' => $personId,
                        'id' => $organisationId,
                        'roleId' => $form->getRoleId()->getValue(),
                    ]
                );
            }
        }

        $this->layout('layout/layout-govuk.phtml');

        return new ViewModel(
            [
                'form' => $form,
                'id' => $organisationId,
                'personId' => $personId,
                'personUsername' => $personUsername,
                'personFullName' => $personFullName,
                'hasRoleOption' => !empty($roles),
            ]
        );
    }

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function removeAction()
    {
        $mapperFactory = $this->getMapperFactory();

        $organisationId = $this->params()->fromRoute('id');
        $positionId = $this->params()->fromRoute('roleId');

        $organisation = $mapperFactory->Organisation->getAuthorisedExaminer($organisationId);
        $positions = $mapperFactory->OrganisationPosition->fetchAllPositionsForOrganisation($organisationId);

        $position = $this->findPositionInListById($positions, (int) $positionId);

        if ($this->getRequest()->isPost()) {
            try {
                $organisationPosition = $mapperFactory->OrganisationPosition;

                $organisationPosition->deletePosition($organisationId, $positionId);
                $this->addSuccessMessage(
                    'You have removed the role of '.$position->getRole().' from '.$position->getPerson()->getFullName()
                );
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }

            if ($this->getIdentity()->getUserId() == $position->getPerson()->getId()) {
                $this->getAuthorizationRefresher()->refreshAuthorization();

                $authorisationService = $this->getAuthorizationService();
                $viewAePermission = PermissionAtOrganisation::AUTHORISED_EXAMINER_READ;

                if (!$authorisationService->isGrantedAtOrganisation($viewAePermission, $organisationId)) {
                    return $this->redirect()->toRoute('user-home');
                }
            }

            return $this->redirect()->toUrl(AuthorisedExaminerUrlBuilderWeb::of($organisationId));
        }

        $model = (new RemoveRoleConfirmationViewModel())
            ->setEmployeeName($position->getPerson()->getFullName())
            ->setEmployeeId($position->getPerson()->getId())
            ->setOrganisationId($organisationId)
            ->setRoleId($positionId)
            ->setRoleName($position->getRole())
            ->setOrganisationName($organisation->getName());

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable(
            'pageSubTitle',
            'Authorised Examiner'
        );
        $this->layout()->setVariable('pageTitle', 'Remove a role');
        $this->setHeadTitle('Remove a role');

        return new ViewModel(['viewModel' => $model]);
    }

    /**
     * @return \Core\Service\MotAuthorizationRefresherInterface
     */
    public function getAuthorizationRefresher()
    {
        return $this->getAuthorizationService();
    }

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function confirmNominationAction()
    {
        $organisationId = $this->params('id');
        $nomineeId = $this->params('nomineeId');
        $roleId = $this->params('roleId');
        $featureToggles = $this->getFeatureToggles();

        $roleName = $this->getRoleName($roleId);

        $mapperFactory = $this->getMapperFactory();
        $nominee = $mapperFactory->Person->getById($nomineeId);
        $ae = $mapperFactory->Organisation->getAuthorisedExaminer($organisationId);

        if ($this->getRequest()->isPost()) {
            try {
                $mapperFactory->OrganisationPosition->createPosition($organisationId, $nomineeId, $roleId);

                $this->addSuccessMessage(
                    sprintf(
                        "A role notification has been sent to %s '%s'.",
                        $nominee->getFullName(),
                        $nominee->getUsername()
                    )
                );

                return $this->redirect()->toUrl(AuthorisedExaminerUrlBuilderWeb::of($organisationId));
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }
        }

        $displayNotification = true;
        $twoFactorOn = $featureToggles->isEnabled(FeatureToggle::TWO_FA);

        if ($roleName == OrganisationBusinessRoleName::AUTHORISED_EXAMINER_DESIGNATED_MANAGER) {
            $displayNotification = false;
        }

        $this->layout('layout/layout-govuk.phtml');

        return new ViewModel(
            [
                'nominee' => $nominee,
                'roleName' => $roleName,
                'authorisedExaminer' => $ae,
                'displayNotification' => $displayNotification,
                'twoFactorEnabled' => $twoFactorOn,
            ]
        );
    }

    /**
     * @param array $validationMessages
     */
    protected function addFlashValidationErrors(array $validationMessages)
    {
        // NOTE: RecursiveIteratorIterator::LEAVES_ONLY is enabled by default.
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($validationMessages));
        foreach ($iterator as $validationMessage) {
            $this->flashMessenger()->addErrorMessage($validationMessage);
        }
    }

    /**
     * @param array $roles list of roles
     *
     * @return \Organisation\Form\SelectRoleForm
     */
    private function getSelectRoleForm($roles)
    {
        $rolesArray = [];
        foreach ($roles as $roleId => $roleName) {
            $rolesArray[$roleId] = [
                'value' => $roleId,
                'label' => $roleName,
                'label_attributes' => [
                    'id' => 'organisationRoleLabel-'.$roleId,
                ],
                'attributes' => [
                    'id' => 'organisationRole-'.$roleId,
                ],
            ];
        }

        return new SelectRoleForm('organisation', $rolesArray);
    }

    /**
     * @param $list OrganisationPositionDto[]
     * @param $positionId
     *
     * @return OrganisationPositionDto|null
     */
    private function findPositionInListById($list, $positionId)
    {
        foreach ($list as $item) {
            if ($item->getId() === $positionId) {
                return $item;
            }
        }
        $this->addErrorMessages('Position in organisation was not found');

        return null;
    }

    /**
     * @param int $roleId
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    private function getRoleName($roleId)
    {
        $roleArray = $this->getCatalogByName('organisationBusinessRole');
        foreach ($roleArray as $roleElement) {
            if ($roleElement['id'] === intval($roleId)) {
                return $roleElement['name'];
            }
        }

        throw new \InvalidArgumentException('Organisation business role with id '.$roleId.' was not found');
    }
}
