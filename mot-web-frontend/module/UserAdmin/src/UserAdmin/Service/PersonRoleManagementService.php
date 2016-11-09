<?php

namespace UserAdmin\Service;

use Application\Helper\DataMappingHelper;
use Application\Service\CatalogService;
use DvsaClient\Mapper\UserAdminMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;

class PersonRoleManagementService
{
    const ROLES_ASSIGNED = 'assigned';

    const ROLES_MANAGEABLE = 'manageable';

    const TRADE_ROLE_TYPE = 'trade';

    const INTERNAL_ROLE_TYPE = 'internal';

    /** @var MotAuthorisationServiceInterface */
    private $authorisationService;

    /**
     * @var MotIdentityProviderInterface
     */
    private $motIdentityProvider;

    /** @var HttpRestJsonClient */
    private $client;

    /** @var UserAdminMapper */
    private $userAdminMapper;

    /** @var CatalogService */
    private $catalogService;

    /** @var  array containing person's both internal and trade roles*/
    private $personInternalRoles;

    /**
     * @param MotIdentityProviderInterface     $motIdentityProvider
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param HttpRestJsonClient               $client
     * @param CatalogService                   $catalogService
     */
    public function __construct(
        MotIdentityProviderInterface $motIdentityProvider,
        MotAuthorisationServiceInterface $authorisationService,
        HttpRestJsonClient $client,
        CatalogService $catalogService,
        UserAdminMapper $userAdminMapper
    ) {
        $this->motIdentityProvider = $motIdentityProvider;
        $this->authorisationService = $authorisationService;
        $this->client = $client;
        $this->userAdminMapper = $userAdminMapper;
        $this->catalogService = $catalogService;
    }

    /**
     * Checks to see if the user has the relevant permission.
     *
     * @return bool
     */
    public function userHasPermissionToManagePersonDvsaRoles()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::MANAGE_DVSA_ROLES);
    }

    /**
     * @return bool
     */
    public function userHasPermissionToReadPersonDvsaRoles()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::READ_DVSA_ROLES);
    }

    /**
     * @return bool
     */
    public function userHasPermissionToResetPassword()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::USER_PASSWORD_RESET);
    }

    /**
     * @return bool
     */
    public function userHasPermissionToRecoveryUsername()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::USERNAME_RECOVERY);
    }

    /**
     * @return bool
     */
    public function userHasPermissionToReclaimUserAccount()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::USER_ACCOUNT_RECLAIM);
    }

    /**
     * Throws an exception if the user attempts to manage themselves.
     *
     * @param int $personToBeManagedId
     *
     * @throws UnauthorisedException if the user attempts to manage themselves
     */
    public function forbidManagementOfSelf($personToBeManagedId)
    {
        if (true === $this->personToManageIsSelf($personToBeManagedId)) {
            throw new UnauthorisedException('You are not allowed to manage yourself');
        }
    }

    /**
     * Returns true if the logged in user is the same as the user being managed.
     *
     * @param int $personToBeManagedId
     *
     * @return bool
     */
    public function personToManageIsSelf($personToBeManagedId)
    {
        return ($this->motIdentityProvider->getIdentity()->getUserId() == $personToBeManagedId);
    }

    /**
     * @param int $personId
     * @param int $personSystemRoleId
     *
     * @return boolean
     */
    public function addRole($personId, $personSystemRoleId)
    {
        // Use CatalogService to translate between personSystemRoleId and code
        $array = $this->catalogService->getPersonSystemRoles();
        $foundItem = (new DataMappingHelper($array, 'id', (int) $personSystemRoleId))
            ->setReturnKeys(['code'])
            ->getValue();

        $url = PersonUrlBuilder::manageInternalRoles($personId)->toString();
        // API throws an exception if the post fails, catch that here and return
        try {
            $this->client->post($url, [
                'personSystemRoleCode' => $foundItem['code'],
            ]);

            return true;
        } catch (GeneralRestException $e) {
            return false;
        }
    }

    /**
     * @param $personId
     * @param $personSystemRoleId
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function removeRole($personId, $personSystemRoleId)
    {
        // Use CatalogService to translate between personSystemRoleId and code
        $array = $this->catalogService->getPersonSystemRoles();
        $foundItem = (new DataMappingHelper($array, 'id', (int) $personSystemRoleId))
            ->setReturnKeys(['code'])
            ->getValue();

        $url = PersonUrlBuilder::removeInternalRoles($personId, $foundItem['code'])->toString();

        try {
            $this->client->delete($url);

            return true;
        } catch (GeneralRestException $e) {
            return false;
        }
    }

    /**
     * @param int  $personId
     *
     * @return \DvsaCommon\Dto\Person\PersonHelpDeskProfileDto
     */
    public function getUserProfile($personId)
    {
        return $this->userAdminMapper->getUserProfile($personId);
    }

    /**
     * @param int  $personId
     * @param bool $isNewUserProfileEnabled
     *
     * @return array
     */
    public function getPersonManageableInternalRoles($personId)
    {
        $personSystemRoles = $this->catalogService->getPersonSystemRoles();

        $manageableRoles = [];

        foreach ($this->retrievePersonManageableInternalRoles($personId) as $roleCode) {
            $manageableRoles[$roleCode] = (new DataMappingHelper($personSystemRoles, 'code', $roleCode))
                ->setReturnKeys(['id', 'name'])
                ->getValue();
        }

        $manageableRolesAndUrl = array_map(
            function ($element) use ($personId) {
                $route = 'newProfileUserAdmin/manage-user-internal-role/add-internal-role';
                $params = [
                    'id' => $personId,
                    'personSystemRoleId' => $element['id'],
                ];

                $element['url'] = [
                    'route' => $route,
                    'params' => $params,
                ];

                return $element;
            },
            $manageableRoles
        );

        return $this->sortRolesByName($manageableRolesAndUrl);
    }

    /**
     * @param int  $personId
     * @param bool $isNewUserProfileEnabled
     *
     * @return array
     */
    public function getPersonAssignedInternalRoles($personId)
    {
        if (false === $this->userHasPermissionToReadPersonDvsaRoles()) {
            return [];
        }

        $personSystemRoles = $this->catalogService->getPersonSystemRoles();

        $manageableRoles = [];

        foreach ($this->retrievePersonAssignedInternalRoles($personId) as $roleCode) {
            $manageableRoles[$roleCode] = (new DataMappingHelper($personSystemRoles, 'code', $roleCode))
                ->setReturnKeys(['id', 'name'])
                ->getValue();

            $manageableRoles[$roleCode]['canManageThisRole'] = $this->canManageThisRole($roleCode);
        }

        $manageableRolesAndUrl = array_map(
            function ($element) use ($personId) {
                $route = 'newProfileUserAdmin/manage-user-internal-role/remove-internal-role';
                $params = [
                    'id' => $personId,
                    'personSystemRoleId' => $element['id'],
                ];

                $element['url'] = [
                    'route' => $route,
                    'params' => $params,
                ];

                return $element;
            },
            $manageableRoles
        );

        return $this->sortRolesByName($manageableRolesAndUrl);
    }

    public function personHasPendingRole($personId)
    {
        $path = UrlBuilder::pendingRoles($personId)->toString();

        $data = $this->client->get($path)['data'];

        $roles = $this->flattenRoleData($data);

        return !empty($roles);
    }

    private function flattenRoleData($data)
    {
        $roles = $data['system']['roles'];

        $siteAndOrganisationRoles = $data;
        unset($siteAndOrganisationRoles['system']);

        foreach ($siteAndOrganisationRoles as $roleType => $organisationOrSiteRoles) {
            foreach ($organisationOrSiteRoles as $siteAndOrganisation) {
                foreach ($siteAndOrganisation['roles'] as $role) {
                    if (!in_array($role, $roles)) {
                        $roles[] = $role;
                    }
                }
            }
        }

        return $roles;
    }

    /**
     * @param string $roleCode
     *
     * @return bool
     */
    private function canManageThisRole($roleCode)
    {
        $manageRoleCode = 'MANAGE-ROLE-' . $roleCode;

        return $this->authorisationService->isGranted($manageRoleCode);
    }

    /**
     * @param $personId
     *
     * @return array
     */
    private function retrievePersonAssignedAndInternalRoles($personId)
    {
        if (is_null($this->personInternalRoles)) {
            $url = PersonUrlBuilder::manageInternalRoles($personId)->toString();
            $response = $this->client->get($url);

            $this->personInternalRoles = $response['data'];
        }

        return $this->personInternalRoles;
    }

    /**
     * @param $personId
     *
     * @return mixed
     */
    private function retrievePersonAssignedInternalRoles($personId)
    {
        return $this->retrievePersonAssignedAndInternalRoles($personId)[self::ROLES_ASSIGNED];
    }

    /**
     * @param $personId
     *
     * @return mixed
     */
    private function retrievePersonManageableInternalRoles($personId)
    {
        return $this->retrievePersonAssignedAndInternalRoles($personId)[self::ROLES_MANAGEABLE];
    }

    /**
     * @param array $roles
     *
     * @return array
     */
    private function sortRolesByName($roles)
    {
        uasort(
            $roles,
            function ($a, $b) {
                if ($a['name'] === $b['name']) {
                    return 0;
                }

                return ($a['name'] < $b['name']) ? -1 : 1;
            }
        );

        return $roles;
    }
}
