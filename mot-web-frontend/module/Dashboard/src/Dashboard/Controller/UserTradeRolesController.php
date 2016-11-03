<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dashboard\Controller;

use Core\Catalog\BusinessRole\BusinessRole;
use Core\Catalog\EnumCatalog;
use Core\Controller\AbstractAuthActionController;
use Core\Service\MotAuthorizationRefresherInterface;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dashboard\Authorisation\ViewTradeRolesAssertion;
use Dashboard\Service\PersonTradeRoleSorterService;
use Dashboard\Service\TradeRolesAssociationsService;
use Dashboard\ViewModel\RemoveRoleViewModel;
use Dashboard\ViewModel\UserTradeRolesViewModel;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\Mapper\BusinessPositionMapperInterface;
use DvsaClient\Mapper\OrganisationPositionMapper;
use DvsaClient\Mapper\SitePositionMapper;
use DvsaCommon\ApiClient\Person\PersonTradeRoles\Dto\PersonTradeRoleDto;
use DvsaCommon\ApiClient\Person\PersonTradeRoles\PersonTradeRolesApiResource;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Utility\ArrayUtils;
use Zend\View\Model\ViewModel;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;

/**
 * UserTradeRoles Controller.
 */
class UserTradeRolesController extends AbstractAuthActionController
{
    const ROUTE_REMOVE_AE_ROLE = 'user-home/profile/byId/remove-ae-role';
    const ROUTE_REMOVE_VTS_ROLE = 'user-home/profile/byId/remove-vts-role';
    const ROUTE_TRADE_ROLES = 'user-home/profile/byId/trade-roles';

    /**
     * @var MotFrontendIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var TradeRolesAssociationsService
     */
    private $tradeRolesAssociationsService;

    /**
     * @var ViewTradeRolesAssertion
     */
    private $viewTradeRolesAssertion;

    /**
     * @var MotFrontendAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var OrganisationPositionMapper
     */
    private $organisationPositionMapper;

    /**
     * @var SitePositionMapper
     */
    private $sitePositionMapper;

    /**
     * @var PersonTradeRolesApiResource
     */
    private $personTradeRolesApiResource;

    /**
     * @var EnumCatalog
     */
    private $catalog;

    /**
     * @var MotAuthorizationRefresherInterface
     */
    private $authorisationRefresher;

    /**
     * @var PersonTradeRoleSorterService
     */
    private $personTradeRoleSorter;

    /**
     * @var PersonProfileUrlGenerator
     */
    private $personProfileUrlGenerator;

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    /**
     * UserTradeRolesController constructor.
     *
     * @param MotFrontendIdentityProviderInterface     $identityProvider
     * @param TradeRolesAssociationsService            $tradeRolesAssociationsService
     * @param ViewTradeRolesAssertion                  $viewTradeRolesAssertion
     * @param MotFrontendAuthorisationServiceInterface $authorisationService
     * @param OrganisationPositionMapper               $organisationPositionMapper
     * @param SitePositionMapper                       $sitePositionMapper
     * @param PersonTradeRolesApiResource              $personTradeRolesApiResource
     * @param EnumCatalog                              $catalog
     * @param MotAuthorizationRefresherInterface       $authorisationRefresher
     * @param PersonTradeRoleSorterService             $personTradeRoleSorter
     * @param PersonProfileUrlGenerator                $personProfileUrlGenerator
     * @param ContextProvider                          $contextProvider
     */
    public function __construct(
        MotFrontendIdentityProviderInterface $identityProvider,
        TradeRolesAssociationsService $tradeRolesAssociationsService,
        ViewTradeRolesAssertion $viewTradeRolesAssertion,
        MotFrontendAuthorisationServiceInterface $authorisationService,
        OrganisationPositionMapper $organisationPositionMapper,
        SitePositionMapper $sitePositionMapper,
        PersonTradeRolesApiResource $personTradeRolesApiResource,
        EnumCatalog $catalog,
        MotAuthorizationRefresherInterface $authorisationRefresher,
        PersonTradeRoleSorterService $personTradeRoleSorter,
        PersonProfileUrlGenerator $personProfileUrlGenerator,
        ContextProvider $contextProvider
    ) {
        $this->identityProvider = $identityProvider;
        $this->tradeRolesAssociationsService = $tradeRolesAssociationsService;
        $this->viewTradeRolesAssertion = $viewTradeRolesAssertion;
        $this->authorisationService = $authorisationService;
        $this->organisationPositionMapper = $organisationPositionMapper;
        $this->sitePositionMapper = $sitePositionMapper;
        $this->personTradeRolesApiResource = $personTradeRolesApiResource;
        $this->catalog = $catalog;
        $this->authorisationRefresher = $authorisationRefresher;
        $this->personTradeRoleSorter = $personTradeRoleSorter;
        $this->personProfileUrlGenerator = $personProfileUrlGenerator;
        $this->contextProvider = $contextProvider;
    }

    /**
     * @throws \DvsaCommon\Exception\UnauthorisedException
     *
     * @return array
     */
    public function indexAction()
    {
        $personId = $this->getPersonId();

        $this->viewTradeRolesAssertion->assertGratedViewProfileTradeRolesPage($personId);

        $tradeRoles = $this->personTradeRolesApiResource->getRoles($personId);

        $this->setPageTitle('Roles and associations');

        $personIsViewingOwnProfile = false;
        if ($personId == $this->identityProvider->getIdentity()->getUserId()) {
            $this->setPageSubTitle('Your profile');
            $personIsViewingOwnProfile = true;
        } else {
            $this->setPageSubTitle('User profile');
        }

        if (!$tradeRoles) {
            if ($personId == $this->identityProvider->getIdentity()->getUserId()) {
                $this->setPageLede("You don't have any roles and associations.");
            } else {
                $this->setPageLede("User doesn't have any active role associations");
            }
        }

        $this->layout('layout/layout-govuk.phtml');

        $urlHelper = $this->getServiceLocator()->get('ViewHelperManager')->get('url');

        $previousUrl = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ?
            $this->personProfileUrlGenerator->toPersonProfile() : '';

        $vm = new UserTradeRolesViewModel($this->authorisationService,
            $this->personTradeRoleSorter->sortTradeRoles($tradeRoles),
            $this->catalog->businessRole(),
            $urlHelper,
            $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE),
            $this->personProfileUrlGenerator,
            $previousUrl
        );
        $vm->setPersonId((int) $this->params()->fromRoute('id'));
        $vm->setPersonIsViewingOwnProfile($personIsViewingOwnProfile);

        return [
            'userTradeRolesViewModel' => $vm,
        ];
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
     * @param BusinessPositionMapperInterface $positionMapper
     * @param $roleType
     *
     * @throws \DvsaCommon\Exception\UnauthorisedException
     *
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    protected function removeRole(BusinessPositionMapperInterface $positionMapper, $roleType)
    {
        $positionId = $this->params()->fromRoute('positionId');
        $entityId = $this->params()->fromRoute('entityId');
        $personId = $this->getPersonId();

        $this->viewTradeRolesAssertion->assertGrantedViewRemoveRolePage($personId);

        $this->layout('layout/layout-govuk.phtml');
        $this->setPageTitle('Remove role');
        $this->setPageSubTitle('Your profile');

        $personTradeRole = $this->getPersonTradeRole($positionId, $personId);

        if ($this->getRequest()->isPost()) {
            try {
                $positionMapper->deletePosition($entityId, $positionId);
                $this->addSuccessMessage('Role removed successfully.');

                $this->clearCurrentVtsIfNeeded($personTradeRole);
                $this->authorisationRefresher->refreshAuthorization();
            } catch (ValidationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }

            return true === $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE)
                ? $this->redirect()->toUrl($this->personProfileUrlGenerator->fromPersonProfile('trade-roles'))
                : $this->redirect()->toRoute(self::ROUTE_TRADE_ROLES, ['id' => $personId]);
        }

        if (!$personTradeRole) {
            return true === $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE)
                ? $this->redirect()->toUrl($this->personProfileUrlGenerator->fromPersonProfile('trade-roles'))
                : $this->redirect()->toRoute(self::ROUTE_TRADE_ROLES, ['id' => $personId]);
        }

        $removeRoleViewModel = (new RemoveRoleViewModel())
            ->setEntityId($personTradeRole->getWorkplaceId())
            ->setPositionId($positionId)
            ->setPersonId($personId)
            ->setRoleName($this->catalog->businessRole()->getByCode($personTradeRole->getRoleCode())->getName())
            ->setEntityName($personTradeRole->getWorkplaceName());

        $viewModel = new ViewModel([
            'viewModel' => $removeRoleViewModel,
        ]);
        $viewModel->setTemplate('/dashboard/user-trade-roles/remove-role.phtml');

        return $viewModel;
    }

    /**
     * @param PersonTradeRoleDto $personTradeRole
     */
    private function clearCurrentVtsIfNeeded(PersonTradeRoleDto $personTradeRole)
    {
        $roleType = $this->catalog->businessRole()->getByCode($personTradeRole->getRoleCode())->getType();

        if ($roleType === BusinessRole::SITE_TYPE) {
            /** @var Identity $identity */
            $identity = $this->getIdentity();
            $personId = $this->params()->fromRoute('id');
            if ($personId == $identity->getUserId()) {
                $currentVts = $identity->getCurrentVts();
                if ($currentVts) {
                    if ($currentVts->getVtsId() == $personTradeRole->getWorkplaceId()) {
                        $identity->clearCurrentVts();
                    }
                }
            }
        }
    }

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function removeVtsRoleAction()
    {
        return $this->removeRole($this->sitePositionMapper, BusinessRole::SITE_TYPE);
    }

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function removeAeRoleAction()
    {
        return $this->removeRole($this->organisationPositionMapper, BusinessRole::ORGANISATION_TYPE);
    }

    /**
     * @param $tradeRoleId
     * @param $personId
     *
     * @return PersonTradeRoleDto
     */
    public function getPersonTradeRole($tradeRoleId, $personId)
    {
        $personTradeRoles = $this->personTradeRolesApiResource->getRoles($personId);

        return ArrayUtils::firstOrNull(
            $personTradeRoles,
            function (PersonTradeRoleDto $tradeRole) use ($tradeRoleId) {
                return $tradeRole->getPositionId() == $tradeRoleId;
            });
    }
}
