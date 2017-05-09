<?php

namespace SiteApi\Service;

use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Entity;
use OrganisationApi\Service\Mapper\ContactMapper;
use SiteApi\Service\Mapper\SiteBusinessRoleMapMapper;
use DvsaEntities\Entity\SiteBusinessRoleMap;

/**
 * @deprecated we are aiming to remove this trait
 */
trait ExtractSiteTrait
{
    /** @var \Zend\Stdlib\Hydrator\HydratorInterface $objectHydrator */
    protected $objectHydrator;

    /**
     * @var SiteBusinessRoleMapMapper
     */
    protected $positionMapper;

    /**
     * Sorry jon but I need this in another class that as different hierarchy..
     *
     * @param $vehicleTestingStations
     *
     * @return array
     */
    protected function extractVehicleTestingStations($vehicleTestingStations)
    {
        $vtsData = [];
        if ($vehicleTestingStations) {
            foreach ($vehicleTestingStations as $vts) {
                $vtsData[] = $this->extractVehicleTestingStation($vts);
            }
        }

        return $vtsData;
    }

    protected function extractVehicleTestingStation(Entity\Site $vehicleTestingStation)
    {
        $vehicleTestingData = $this->objectHydrator->extract($vehicleTestingStation);
        unset($vehicleTestingData['siteTestingSchedule']);
        $vehicleTestingData['address'] = $this->extractAddress($vehicleTestingStation->getAddress());
        $vehicleTestingData['contacts'] = $this->extractContacts($vehicleTestingStation->getContacts());
        $authorisedExaminer = $vehicleTestingStation->getAuthorisedExaminer();

        if ($authorisedExaminer) {
            $vehicleTestingData['authorisedExaminer'] = $this->extractAuthorisedExaminer($authorisedExaminer);
            $aedm = $authorisedExaminer->getDesignatedManager();
            $vehicleTestingData['authorisedExaminer']['username'] = $aedm ? $aedm->getUsername() : '';
        }

        //  --  organisation  --
        if (!is_null($vehicleTestingStation->getOrganisation())) {
            $vehicleTestingData['organisation'] = $this->objectHydrator->extract(
                $vehicleTestingStation->getOrganisation()
            );
        }

        //  --  Site Assessment  --
        unset($vehicleTestingData['lastSiteAssessment']);
        if ($vehicleTestingStation->getLastSiteAssessment()) {
            $vehicleTestingData['lastSiteAssessment'] = $this->objectHydrator->extract(
                $vehicleTestingStation->getLastSiteAssessment()
            );
        }

        //  --  test classes  --
        $testClasses = [];

        /** @var Entity\AuthorisationForTestingMotAtSite $obj */
        foreach ($vehicleTestingStation->getAuthorisationForTestingMotAtSite() as $obj) {
            $testClasses[] = $obj->getVehicleClass()->getCode();
        }
        $vehicleTestingData['roles'] = $testClasses;

        $vehicleTestingData['facilities'] = $this->extractFacilitiesGroupedByType($vehicleTestingStation);

        //  --  positions  --

        $positions = ArrayUtils::filter($vehicleTestingStation->getPositions(),
            function (SiteBusinessRoleMap $position) {
                return $position->getBusinessRoleStatus()->getCode() == BusinessRoleStatusCode::ACTIVE
                    || $position->getBusinessRoleStatus()->getCode() == BusinessRoleStatusCode::PENDING;
            });

        $positions = $this->positionMapper->manyToArray($positions);
        $vehicleTestingData['positions'] = $positions;

        // -- comments (temporary, awaiting stable new world order)
        $comments = [];
        foreach ($vehicleTestingStation->getSiteComments() as $siteComment) {
            $comments[] = $this->objectHydrator->extract($siteComment->getComment());
        }
        if (count($comments)) {
            $vehicleTestingData['comments'] = $comments;
        }

        $vehicleTestingData['siteTestingDailySchedule'] = $this->extractSchedules(
            $vehicleTestingStation->getSiteTestingSchedule()
        );

        /** @var $vehicleTestingData ['defaultBrakeTestClass1And2'] BrakeTestTypeq */
        if ($vehicleTestingData['defaultBrakeTestClass1And2'] !== null) {
            $vehicleTestingData['defaultBrakeTestClass1And2']
                = $vehicleTestingData['defaultBrakeTestClass1And2']->getCode();
        }
        if ($vehicleTestingData['defaultServiceBrakeTestClass3AndAbove'] !== null) {
            $vehicleTestingData['defaultServiceBrakeTestClass3AndAbove']
                = $vehicleTestingData['defaultServiceBrakeTestClass3AndAbove']->getCode();
        }
        if ($vehicleTestingData['defaultParkingBrakeTestClass3AndAbove'] !== null) {
            $vehicleTestingData['defaultParkingBrakeTestClass3AndAbove'] =
                $vehicleTestingData['defaultParkingBrakeTestClass3AndAbove']->getCode();
        }

        return $vehicleTestingData;
    }

    /**
     * @param Entity\Site $vehicleTestingStation
     *
     * @return array
     */
    private function extractFacilitiesGroupedByType(Entity\Site $vehicleTestingStation)
    {
        $facilitiesGroupedByType = [];

        foreach ($vehicleTestingStation->getFacilities() as $facility) {
            $type = $facility->getFacilityType()->getCode();
            if (!array_key_exists($type, $facilitiesGroupedByType)) {
                $facilitiesGroupedByType [$type] = [];
            }

            $facilityData['name'] = $facility->getName();
            $facilityData['id'] = $facility->getId();

            $facilityType = $facility->getFacilityType();
            $facilityData['facilityType'] = [
                'name' => $facilityType->getName(),
                'id' => $facilityType->getId(),
                'code' => $facilityType->getCode(),
            ];

            $facilitiesGroupedByType[$type][] = $facilityData;
        }

        return $facilitiesGroupedByType;
    }

    /**
     * TODO: Refactor... this should actually supersede the existing functionality!
     *
     * @param Entity\Site $site
     *
     * @return mixed
     */
    protected function extractSite(Entity\Site $site)
    {
        $vehicleTestingData = $this->objectHydrator->extract($site);
        $vehicleTestingData['address'] = $this->extractAddress($site->getAddress());
        $vehicleTestingData['authorisedExaminer'] = $this->extractAuthorisedExaminer($site->getAuthorisedExaminer());

        //  --  Site Assessment  --
        unset($vehicleTestingData['lastSiteAssessment']);
        if ($site->getLastSiteAssessment()) {
            $vehicleTestingData['lastSiteAssessment'] = $this->objectHydrator->extract(
                $site->getLastSiteAssessment()
            );
        }

        //  --  test classes  --
        $testClasses = [];

        $vehicleTestingData['roles'] = $testClasses;

        //  --  personal roles  --
        $personalRoles = [];

        $vehicleTestingData['personalRoles'] = $personalRoles;

        // -- comments (temporary, awaiting stable new world order)
        $comments = [];
        foreach ($site->getSiteComments() as $siteComment) {
            $comments[] = $this->objectHydrator->extract($siteComment->getComment());
        }
        if (count($comments)) {
            $vehicleTestingData['comments'] = $comments;
        }

        return $vehicleTestingData;
    }

    /**
     * @param Entity\Address $address
     *
     * @return array
     */
    protected function extractAddress(Entity\Address $address = null)
    {
        $addressData = [];

        if ($address) {
            $addressData = $this->objectHydrator->extract($address);
        }

        return $addressData;
    }

    /**
     * @param Entity\SiteContact[] $contacts
     *
     * @return array
     */
    protected function extractContacts($contacts)
    {
        $contactsData = [];

        $contactMapper = new ContactMapper();

        foreach ($contacts as $contact) {
            $contactData = $contactMapper->toArray($contact->getDetails());

            $contactData['type'] = $contact->getType()->getCode();
            $contactData['_clazz'] = 'SiteContact';

            $contactsData[] = $contactData;
        }

        return $contactsData;
    }

    /**
     * @param Entity\AuthorisationForAuthorisedExaminer $authorisedExaminer
     *
     * @return array
     */
    protected function extractAuthorisedExaminer(Entity\AuthorisationForAuthorisedExaminer $authorisedExaminer)
    {
        return $authorisedExaminer ? $this->objectHydrator->extract($authorisedExaminer) : [];
    }

    protected function extractSchedule(Entity\SiteTestingDailySchedule $schedule)
    {
        return $schedule ? $this->objectHydrator->extract($schedule) : [];
    }

    /**
     * @param Entity\SiteTestingDailySchedule[] $schedules
     *
     * @return array
     */
    protected function extractSchedules($schedules)
    {
        $extractedSchedules = [];

        if (!is_null($schedules)) {
            foreach ($schedules as $schedule) {
                $extractedSchedule['weekday'] = $schedule->getWeekday();
                $extractedSchedule['openTime']
                    = $schedule->getOpenTime() === null ? null : $schedule->getOpenTime()->toIso8601();
                $extractedSchedule['closeTime']
                    = $schedule->getCloseTime() === null ? null : $schedule->getCloseTime()->toIso8601();

                $extractedSchedules[] = $extractedSchedule;
            }
        }

        if (empty($extractedSchedules)) {
            $extractedSchedules = [
                ['weekday' => 1, 'openTime' => null, 'closeTime' => null],
                ['weekday' => 2, 'openTime' => null, 'closeTime' => null],
                ['weekday' => 3, 'openTime' => null, 'closeTime' => null],
                ['weekday' => 4, 'openTime' => null, 'closeTime' => null],
                ['weekday' => 5, 'openTime' => null, 'closeTime' => null],
                ['weekday' => 6, 'openTime' => null, 'closeTime' => null],
                ['weekday' => 7, 'openTime' => null, 'closeTime' => null],
            ];
        }

        return $extractedSchedules;
    }
}
