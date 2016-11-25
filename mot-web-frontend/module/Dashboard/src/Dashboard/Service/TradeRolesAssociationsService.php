<?php

namespace Dashboard\Service;

use Application\Helper\DataMappingHelper;
use Application\Data\ApiPersonalDetails;
use Application\Service\CatalogService;
use Dashboard\Model\PersonalDetails;

class TradeRolesAssociationsService
{
    private $personalDetailsApi;

    private $catalogService;

    const ROLETYPE_ORGANISATIONS = 'organisations';

    const ROLETYPE_SITES = 'sites';

    /**
     * @param ApiPersonalDetails $personalDetailsApi
     * @param CatalogService $catalogService
     */
    public function __construct(
        ApiPersonalDetails $personalDetailsApi,
        CatalogService $catalogService
    )
    {
        $this->catalogService = $catalogService;
        $this->personalDetailsApi = $personalDetailsApi;
    }

    public function getRolesAndAssociations($personId)
    {
        $personalDetailsData = $this->personalDetailsApi->getPersonalDetailsData($personId);

        $personalDetails = new PersonalDetails($personalDetailsData);

        return $this->prepareRolesAndAssociations($personalDetails);
    }

    public function prepareRolesAndAssociations(PersonalDetails $personalDetails)
    {
        $siteAndOrganisationRoles = $personalDetails->getSiteAndOrganisationRoles();
        $personSiteAndOrganisationRoles = $this->catalogService->getBusinessRoles();

        $viewOrganisationRoles = $this->viewRolesAndAssociations(
            $siteAndOrganisationRoles['organisations'],
            $personSiteAndOrganisationRoles,
            self::ROLETYPE_ORGANISATIONS
        );
        $viewSiteRoles = $this->viewRolesAndAssociations(
            $siteAndOrganisationRoles['sites'],
            $personSiteAndOrganisationRoles,
            self::ROLETYPE_SITES
        );

        return array_merge($viewOrganisationRoles, $viewSiteRoles);
    }

    private function createRoleData($role, $nicename, $roleType, $id = "", $name = "", $address = "")
    {
        return [
            'id'       => $id,
            'role'     => $role,
            'nicename' => $nicename,
            'name'     => $name,
            'address'  => $address,
            'roletype' => $roleType,
        ];
    }

    /**
     * @param $siteAndOrganisationRoles
     * @param $personSiteAndOrganisationRoles
     * @param int $roleType
     * @return array
     * @throws \Exception
     * @internal param $rolesAndAssociations
     */
    protected function viewRolesAndAssociations($siteAndOrganisationRoles, $personSiteAndOrganisationRoles, $roleType)
    {
        $rolesAndAssociations = [];
        foreach ($siteAndOrganisationRoles as $id => $siteAndOrganisation) {
            foreach ($siteAndOrganisation['roles'] as $role) {
                $niceName = (new DataMappingHelper($personSiteAndOrganisationRoles, 'code', $role))
                    ->setReturnKeys(['name'])
                    ->getValue();

                $rolesAndAssociations[] = $this->createRoleData(
                    $role,
                    $niceName['name'],
                    $roleType,
                    $id,
                    $siteAndOrganisation["name"],
                    $siteAndOrganisation["address"]
                );
            }
        }

        return $rolesAndAssociations;
    }
}
