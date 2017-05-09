<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service;

use Application\Data\ApiPersonalDetails;
use Dashboard\Model\PersonalDetails;

class OrderSecurityCardAddressService
{
    /**
     * @var OrderNewSecurityCardSessionService
     */
    private $orderNewSecurityCardSessionService;

    /**
     * @var ApiPersonalDetails
     */
    private $apiPersonalDetails;

    /**
     * OrderSecurityCardAddressService constructor.
     *
     * @param OrderNewSecurityCardSessionService $orderNewSecurityCardSessionService
     * @param ApiPersonalDetails                 $apiPersonalDetails
     */
    public function __construct(
        OrderNewSecurityCardSessionService $orderNewSecurityCardSessionService,
        ApiPersonalDetails $apiPersonalDetails
    ) {
        $this->orderNewSecurityCardSessionService = $orderNewSecurityCardSessionService;
        $this->apiPersonalDetails = $apiPersonalDetails;
    }

    /**
     * Gets an array of addresses including home and VTS, if the addresses are already in session storage
     * they will be returned, if not call the API to get this list.
     *
     * @return array
     */
    public function getSecurityCardOrderAddresses($userId)
    {
        if (empty($this->orderNewSecurityCardSessionService->loadByGuid($userId)[OrderNewSecurityCardSessionService::ADDRESS_SESSION_STORE])) {
            $sessionArray = $this->orderNewSecurityCardSessionService->loadByGuid($userId);
            $personalDetailsData = $this->apiPersonalDetails->getPersonalDetailsData($userId);
            $personalDetails = new PersonalDetails($personalDetailsData);

            $homeAndSiteDetails = $this->processAddresses($personalDetails);
            $sessionArray[OrderNewSecurityCardSessionService::ADDRESS_SESSION_STORE] = $homeAndSiteDetails;
            $this->orderNewSecurityCardSessionService->saveToGuid($userId, $sessionArray);

            return $homeAndSiteDetails;
        } else {
            return $this->orderNewSecurityCardSessionService->loadByGuid($userId)[OrderNewSecurityCardSessionService::ADDRESS_SESSION_STORE];
        }
    }

    /**
     * Processes the home and site details into an array to be stored in session.
     *
     * @param PersonalDetails $personalDetails
     *
     * @return array
     */
    private function processAddresses(PersonalDetails $personalDetails)
    {
        $siteData = $personalDetails->getSitesRolesAndAssociations();

        $sites = [];

        $homeAddress = [
            'name' => 'Home',
            'addressLine1' => $personalDetails->getAddressLine1(),
            'addressLine2' => $personalDetails->getAddressLine2(),
            'addressLine3' => $personalDetails->getAddressLine3(),
            'town' => $personalDetails->getTown(),
            'postcode' => $personalDetails->getPostcode(),
            'addressString' => $this->formatAddressString($personalDetails),
        ];

        array_push($sites, $homeAddress);

        usort($siteData, $this->orderByValue('name'));

        foreach ($siteData as $site) {
            $siteTemp = [
                'name' => $site['name'],
                'addressString' => $site['address'],
                'addressLine1' => $site['addressParts']['addressLine1'],
                'addressLine2' => $site['addressParts']['addressLine2'],
                'addressLine3' => $site['addressParts']['addressLine3'],
                'town' => $site['addressParts']['town'],
                'postcode' => $site['addressParts']['postcode'],
            ];
            array_push($sites, $siteTemp);
        }

        return $sites;
    }

    /**
     * Gets an Address in the format for displaying on frontend.
     *
     * @param PersonalDetails $personalDetails
     *
     * @return string
     */
    private function formatAddressString(PersonalDetails $personalDetails)
    {
        return implode(
            ', ',
            array_filter(
                [
                    $personalDetails->getAddressLine1(),
                    $personalDetails->getAddressLine2(),
                    $personalDetails->getAddressLine3(),
                    $personalDetails->getTown(),
                    $personalDetails->getPostcode(),
                ]
            )
        );
    }

    /**
     * Function used by usort to order a multidemensional array by a key value.
     *
     * @param $key
     *
     * @return \Closure
     */
    private function orderByValue($key)
    {
        return function ($a, $b) use ($key) {
            return strnatcmp($a[$key], $b[$key]);
        };
    }
}
