<?php

namespace Site\Controller;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Dto\Security\RolesMapDto;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilderWeb;
use Organisation\Form\SelectRoleForm;
use Site\Traits\SiteServicesTrait;
use Site\ViewModel\Role\RemoveRoleConfirmationViewModel;
use Zend\View\Model\ViewModel;
use DvsaCommon\Validator\UsernameValidator;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use HTMLPurifier;

/**
 * Class RoleController.
 */
class RoleController extends AbstractAuthActionController
{
    use SiteServicesTrait;

    const ROUTE_REMOVE_ROLE = 'site/remove-role';
    const ROUTE_LIST_USER_ROLES = 'vehicle-testing-station-list-user-roles';
    const ROUTE_SEARCH_FOR_PERSON = 'vehicle-testing-station-search-for-person';

    /**
     * @var \HTMLPurifier
     */
    protected $htmlPurifier;

    /**
     * @var \DvsaCommon\Validator\UsernameValidator
     */
    protected $usernameValidator;

    /**
     * @param UsernameValidator $usernameValidator
     * @param HTMLPurififer     $htmlPurifier
     */
    public function __construct(UsernameValidator $usernameValidator, HTMLPurifier $htmlPurifier)
    {
        $this->usernameValidator = $usernameValidator;
        $this->htmlPurifier = $htmlPurifier;
    }

    /**
     * @return array|\Zend\Http\Response
     */
    public function searchForPersonAction()
    {
        $this->layout('layout/layout-govuk.phtml');
        $form = [];
        $mapperFactory = $this->getMapperFactory();
        $vehicleTestingStationId = $this->params('vehicleTestingStationId');
        $vehicleTestingStation = $mapperFactory->Site->getById($vehicleTestingStationId);

        $personLogin = '';
        $userNotFound = false;

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
                                'vehicleTestingStationId' => $vehicleTestingStationId,
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

        return
            [
                'form' => $form,
                'vehicleTestingStationId' => $vehicleTestingStationId,
                'vehicleTestingStation' => $vehicleTestingStation,
                'personId' => $personLogin,
                'userNotFound' => $userNotFound,
            ];
    }

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function listUserRolesAction()
    {
        $vehicleTestingStationId = $this->params('vehicleTestingStationId');

        $personId = $this->params('personId');

        $mapperFactory = $this->getMapperFactory();
        $person = $mapperFactory->Person->getById($personId);
        $personUsername = $person->getUsername();
        $personFullName = $person->getFullName();
        $roleCodes = $mapperFactory->SiteRole->fetchAllForPerson($vehicleTestingStationId, $personId);

        $form = $this->getSelectRoleForm($roleCodes);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                return $this->redirect()->toRoute(
                    'vehicle-testing-station-confirm-nomination',
                    [
                        'nomineeId' => $personId,
                        'vehicleTestingStationId' => $vehicleTestingStationId,
                        'roleCode' => $form->getRoleId()->getValue(),
                    ]
                );
            }
        }

        $this->layout('layout/layout-govuk.phtml');

        return new ViewModel(
            [
                'form' => $form,
                'vehicleTestingStationId' => $vehicleTestingStationId,
                'personId' => $personId,
                'personUsername' => $personUsername,
                'personFullName' => $personFullName,
                'hasRoleOption' => !empty($roleCodes),
            ]
        );
    }

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function confirmNominationAction()
    {
        $vehicleTestingStationId = $this->params('vehicleTestingStationId');
        $nomineeId = $this->params('nomineeId');
        $roleCode = $this->params('roleCode');
        $mapperFactory = $this->getMapperFactory();
        $nominee = $mapperFactory->Person->getById($nomineeId);
        $roleCodeNameMap = $this->getCatalogService()->getSiteBusinessRoles();
        $featureToggles = $this->getFeatureToggles();

        if ($this->getRequest()->isPost()) {
            try {
                $mapperFactory->SitePosition->post($vehicleTestingStationId, $nomineeId, $roleCode);

                $this->addSuccessMessage(
                    sprintf(
                        'You have assigned a role to %s, %s. They have been sent a notification.',
                        $nominee->getFullName(),
                        $nominee->getUsername()
                    )
                );

                return $this->redirect()->toRoute('vehicle-testing-station', ['id' => $vehicleTestingStationId]);
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }
        }

        $twoFactorOn = $featureToggles->isEnabled(FeatureToggle::TWO_FA);
        $this->layout('layout/layout-govuk.phtml');

        return new ViewModel(
            [
                'nominee' => $nominee,
                'vehicleTestingStationId' => $vehicleTestingStationId,
                'roleName' => $roleCodeNameMap[$roleCode],
                'twoFactorEnabled' => $twoFactorOn,
            ]
        );
    }

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function removeAction()
    {
        $mapperFactory = $this->getMapperFactory();

        $siteId = $this->params()->fromRoute('siteId', null);
        $positionId = $this->params()->fromRoute('positionId', null);

        $site = $mapperFactory->Site->getById($siteId);

        $position = $this->findPositionInListById($site->getPositions(), (int) $positionId);
        $roleCodeNameMap = $this->getCatalogService()->getSiteBusinessRoles();

        if (null === $position) {
            $this->addErrorMessages('Position at site does not exist');

            return $this->redirect()->toUrl(VehicleTestingStationUrlBuilderWeb::byId($siteId));
        }

        $activeMotTestApiCall = UrlBuilder::person($position->getPerson()->getId())->currentMotTest();
        $activeMotTest = $this->getRestClient()->get(
            $activeMotTestApiCall
        )['data'];

        if ($this->getRequest()->isPost()) {
            try {
                $mapperFactory->SitePosition->deletePosition($siteId, $positionId);
                $this->addSuccessMessage(
                    'You have removed the role of '.$roleCodeNameMap[$position->getRole()->getCode()]
                    .' from '.$position->getPerson()->getFullName()
                );
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }

            return $this->redirect()->toUrl(VehicleTestingStationUrlBuilderWeb::byId($siteId));
        }

        $model = (new RemoveRoleConfirmationViewModel())
            ->setEmployeeName($position->getPerson()->getFullName())
            ->setEmployeeId($position->getPerson()->getId())
            ->setSiteId($siteId)
            ->setPositionId($positionId)
            ->setRoleName($roleCodeNameMap[$position->getRole()->getCode()])
            ->setSiteName($site->getName())
            ->setActiveMotTestNumber($activeMotTest['inProgressTestNumber']);

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable(
            'pageSubTitle',
            'Vehicle testing station'
        );

        $this->layout()->setVariable('pageTitle', 'Remove a role');
        $this->setHeadTitle('Remove a role');

        return new ViewModel(['viewModel' => $model]);
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
     * @param array $roles array of roles
     *
     * @return \Organisation\Form\SelectRoleForm
     */
    private function getSelectRoleForm($roles)
    {
        $roleCodeNameMap = $this->getCatalogService()->getSiteBusinessRoles();
        $rolesArray = [];
        foreach ($roles as $roleCode) {
            $rolesArray[$roleCode] = [
                'value' => $roleCode,
                'label' => $roleCodeNameMap[$roleCode],
                'label_attributes' => [
                    'id' => 'site-role-label-'.$roleCode,
                ],
                'attributes' => [
                    'id' => 'site-role-'.$roleCode,
                ],
            ];
        }
        $form = new SelectRoleForm('site', $rolesArray);

        return $form;
    }

    /**
     * @param RolesMapDto[] $list
     * @param int           $positionId
     *
     * @return RolesMapDto|null
     */
    private function findPositionInListById($list, $positionId)
    {
        /** @var $item \DvsaClient\Entity\SitePosition */
        foreach ($list as $item) {
            if ($item->getId() === $positionId) {
                return $item;
            }
        }
        $this->addErrorMessages('Position at site was not found');

        return null;
    }
}
