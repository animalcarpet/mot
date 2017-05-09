<?php

namespace DvsaMotTest\Service;

use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\HttpRestJson\Client;

/**
 * Class AuthorisedClassesService.
 */
class AuthorisedClassesService
{
    const KEY_FOR_PERSON_APPROVED_CLASSES = 'forPerson';
    const KEY_FOR_VTS_APPROVED_CLASSES = 'forVts';

    /** @var Client */
    private $restClient;

    public function __construct(Client $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @param int $userId
     * @param int $siteId
     *
     * @return array
     */
    public function getCombinedAuthorisedClassesForPersonAndVts($userId, $siteId)
    {
        return array_merge(
            $this->getAuthorisedClassesForPerson($userId),
            $this->getAuthorisedClassesForVTS($siteId)
        );
    }

    /**
     * Returns approved vehicle classes for the given person.
     *
     * @param int $userId
     *
     * @return array
     */
    public function getAuthorisedClassesForPerson($userId)
    {
        return [
            self::KEY_FOR_PERSON_APPROVED_CLASSES => $this->getAuthorisedClassesFromEndpoint(
                    UrlBuilder::person($userId)->getMotTesting()->toString()
                ),
        ];
    }

    /**
     * Returns approved vehicle classes for the given vehicle testing station.
     *
     * @param int $siteId
     *
     * @return array
     */
    public function getAuthorisedClassesForVTS($siteId)
    {
        $url = (new UrlBuilder())->vehicleTestingStation()
            ->routeParam('id', $siteId)
            ->getAuthorisedClasses();

        return [
            self::KEY_FOR_VTS_APPROVED_CLASSES => $this->getAuthorisedClassesFromEndpoint(
                    $url
                ),
        ];
    }

    /**
     * @param string $endPoint API's endpoint URI
     *
     * @return array
     */
    private function getAuthorisedClassesFromEndpoint($endPoint)
    {
        $response = $this->restClient->get($endPoint);
        $authorisedClasses = [];
        if (isset($response['data']) && is_array($response['data'])) {
            foreach ($response['data'] as $class => $status) {
                if ($status == AuthorisationForTestingMotStatusCode::QUALIFIED) {
                    $authorisedClasses[] = str_replace('class', '', $class);
                }
            }
        }

        return $authorisedClasses;
    }
}
