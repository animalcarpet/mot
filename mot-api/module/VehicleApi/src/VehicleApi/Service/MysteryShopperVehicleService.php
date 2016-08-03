<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace VehicleApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\IncognitoVehicle;
use DvsaEntities\Repository\IncognitoVehicleRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\VehicleRepository;
use VehicleApi\InputFilter\MysteryShopperInputFilter;
use VehicleApi\MysteryShopper\CampaignDates;

/**
 * MysteryShopperVehicle Service.
 */
class MysteryShopperVehicleService
{
    /**
     * @var AuthorisationServiceInterface
     */
    private $authService;

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var SiteRepository
     */
    private $siteRepository;

    /**
     * @var IncognitoVehicleRepository
     */
    private $incognitoVehicleRepository;

    /**
     * @var VehicleRepository
     */
    private $vehicleRepository;

    /**
     * @var PersonRepository
     */
    private $personRepository;

    /**
     * @var MysteryShopperInputFilter
     */
    private $mysteryShopperInputFilter;

    /**
     * @param AuthorisationServiceInterface $authService
     * @param MotIdentityProviderInterface  $identityProvider
     * @param MysteryShopperInputFilter     $mysteryShopperInputFilter
     * @param SiteRepository                $siteRepository
     * @param IncognitoVehicleRepository    $incognitoVehicleRepository
     * @param VehicleRepository             $vehicleRepository
     * @param PersonRepository              $personRepository
     */
    public function __construct(
        AuthorisationServiceInterface $authService,
        MotIdentityProviderInterface $identityProvider,
        MysteryShopperInputFilter $mysteryShopperInputFilter,
        SiteRepository $siteRepository,
        IncognitoVehicleRepository $incognitoVehicleRepository,
        VehicleRepository $vehicleRepository,
        PersonRepository $personRepository
    ) {
        $this->authService = $authService;
        $this->identityProvider = $identityProvider;
        $this->mysteryShopperInputFilter = $mysteryShopperInputFilter;
        $this->siteRepository = $siteRepository;
        $this->incognitoVehicleRepository = $incognitoVehicleRepository;
        $this->vehicleRepository = $vehicleRepository;
        $this->personRepository = $personRepository;
    }

    /**
     * To flag the given vehicle as a "mystery shopper" vehicle for the given period of time.
     *
     * @param $data
     *
     * @throws NotFoundException
     *
     * @return IncognitoVehicle
     */
    public function optIn($data)
    {
        $this->authService->assertGranted(PermissionInSystem::MANAGE_MYSTERY_SHOPPER_CAMPAIGN);

        $vehicleId = $data[MysteryShopperInputFilter::FIELD_VEHICLE_ID];
        $vehicle = $this->vehicleRepository->find($vehicleId);

        if (!$vehicle) {
            throw new NotFoundException('Vehicle ' . $vehicleId);
        }

        $data = $this->prepareCampaignDatesField($data);

        $context = $this->prepareContext(
            $this->incognitoVehicleRepository->findAllCampaignsForVehicle($vehicle)
        );

        $this->prepareInputFilter($data);

        if (false === $this->mysteryShopperInputFilter->isValid($context)) {
            return false;
        }

        // if site not found a not found exception will be thrown
        $site = $this->siteRepository->getBySiteNumber($data[MysteryShopperInputFilter::FIELD_SITE_NUMBER]);

        $incognitoVehicle = new IncognitoVehicle();

        $incognitoVehicle->setVehicle($vehicle)
            ->setPerson($this->getPerson())
            ->setSite($site)
            ->setStartDate(new \DateTime($data[MysteryShopperInputFilter::FIELD_START_DATE]))
            ->setEndDate(new \DateTime($data[MysteryShopperInputFilter::FIELD_END_DATE]))
            ->setTestDate(new \DateTime($data[MysteryShopperInputFilter::FIELD_TEST_DATE]));

        if (array_key_exists(MysteryShopperInputFilter::FIELD_EXPIRY_DATE, $data)) {
            $incognitoVehicle->setExpiryDate(new \DateTime($data[MysteryShopperInputFilter::FIELD_EXPIRY_DATE]));
        }

        $this->incognitoVehicleRepository->save($incognitoVehicle);

        return $incognitoVehicle;
    }

    /**
     * @param $incognitoVehicleId
     * @return bool
     * @throws NotFoundException
     */
    public function optOut($incognitoVehicleId)
    {
        $this->authService->assertGranted(PermissionInSystem::MANAGE_MYSTERY_SHOPPER_CAMPAIGN);

        $incognitoVehicle = $this->incognitoVehicleRepository->find($incognitoVehicleId);

        if (!$incognitoVehicle) {
            throw new NotFoundException('IncognitoVehicle ' . $incognitoVehicleId);
        }

        $incognitoVehicle->setEndDate(new \DateTime('NOW'));
        $this->incognitoVehicleRepository->save($incognitoVehicle);
        return true;
    }


    /**
     * @param int   $campaignId
     * @param array $data
     * @return bool|IncognitoVehicle
     * @throws NotFoundException
     */
    public function edit($campaignId, $data)
    {
        $this->authService->assertGranted(PermissionInSystem::MANAGE_MYSTERY_SHOPPER_CAMPAIGN);

        /** @var IncognitoVehicle  $campaign */
        $campaign = $this->incognitoVehicleRepository->find($campaignId);

        if (!$campaign) {
            throw new NotFoundException(sprintf('There is no campaign with id "%s" in the system ' , $campaignId));
        }

        $data = $this->prepareCampaignDatesField($data, $campaign);

        $context = $this->prepareContext(
            $this->incognitoVehicleRepository->findAllCampaignsForVehicle($campaign->getVehicle()),
            $campaignId
        );

        $this->prepareInputFilter($data, true);

        if (false === $this->mysteryShopperInputFilter->isValid($context)) {
            return false;
        }

        $campaign->setPerson($this->getPerson());

        if (array_key_exists(MysteryShopperInputFilter::FIELD_START_DATE, $data)) {
            $campaign->setStartDate(new \DateTime($data[MysteryShopperInputFilter::FIELD_START_DATE]));
        }

        if (array_key_exists(MysteryShopperInputFilter::FIELD_END_DATE, $data)) {
            $campaign->setEndDate(new \DateTime($data[MysteryShopperInputFilter::FIELD_END_DATE]));
        }

        if (array_key_exists(MysteryShopperInputFilter::FIELD_TEST_DATE, $data)) {
            $campaign->setTestDate(new \DateTime($data[MysteryShopperInputFilter::FIELD_TEST_DATE]));
        }

        if (array_key_exists(MysteryShopperInputFilter::FIELD_EXPIRY_DATE, $data)) {
            $campaign->setExpiryDate(new \DateTime($data[MysteryShopperInputFilter::FIELD_EXPIRY_DATE]));
        }

        if (array_key_exists(MysteryShopperInputFilter::FIELD_SITE_NUMBER, $data)) {
            $site = $this->siteRepository->getBySiteNumber($data[MysteryShopperInputFilter::FIELD_SITE_NUMBER]);
            $campaign->setSite($site);
        }

        if (array_key_exists(MysteryShopperInputFilter::FIELD_VEHICLE_ID, $data)) {

            $vehicle = $this->vehicleRepository->find($data[MysteryShopperInputFilter::FIELD_VEHICLE_ID]);

            if (!$vehicle) {
                throw new NotFoundException('Vehicle ' . (string) $data[MysteryShopperInputFilter::FIELD_VEHICLE_ID]);
            }

            $campaign->setVehicle($vehicle);
        }

        $this->incognitoVehicleRepository->save($campaign);

        return $campaign;
    }

    /**
     * @param $id
     * @return array|null
     * @throws NotFoundException
     */
    public function getAllCampaigns($id)
    {
        $this->authService->assertGranted(PermissionInSystem::MANAGE_MYSTERY_SHOPPER_CAMPAIGN);

        $vehicle = $this->vehicleRepository->get($id);
        if (!$vehicle) {
            throw new NotFoundException('Vehicle ' . (string) $id);
        }

        return $this->incognitoVehicleRepository->findAllCampaignsForVehicle($vehicle);
    }

    /**
     * @param $vehicleId
     *
     * @throws NotFoundException
     *
     * @return IncognitoVehicle|null
     */
    public function getCurrent($vehicleId)
    {
        $this->authService->assertGranted(PermissionInSystem::MANAGE_MYSTERY_SHOPPER_CAMPAIGN);

        $vehicle = $this->vehicleRepository->get($vehicleId);
        if (!$vehicle) {
            throw new NotFoundException('Vehicle ' . (string) $vehicleId);
        }

        return $this->incognitoVehicleRepository->getCurrent($vehicle) ?: null;
    }

    /**
     * Retrieve all the potential validation messages from the input filter.
     *
     * @return array
     */
    public function getValidationMessages()
    {
        return $this->mysteryShopperInputFilter->getMessages();
    }

    /**
     * To initiate the input filter validators and set its data.
     *
     * @param array $data
     * @param bool|false $toUpdate
     */
    private function prepareInputFilter($data, $toUpdate = false)
    {
        if (true === $toUpdate) {
            $this->mysteryShopperInputFilter->setToEditMode();
        }
        $this->mysteryShopperInputFilter->init();
        $this->mysteryShopperInputFilter->setData($data);
    }

    /**
     * To inject a new field to the data, addressing all the required date fields using the value object "CampaignDates".
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareCampaignDatesField($data, IncognitoVehicle $campaign = null)
    {

        $data[MysteryShopperInputFilter::FIELD_CAMPAIGN_DATES] = new CampaignDates(
            ArrayUtils::tryGet(
                $data,
                MysteryShopperInputFilter::FIELD_START_DATE,
                is_null($campaign)? null : $campaign->getStartDate()->format('Y-m-d H:i:s')
            ),
            ArrayUtils::tryGet(
                $data,
                MysteryShopperInputFilter::FIELD_END_DATE,
                is_null($campaign)? null : $campaign->getEndDate()->format('Y-m-d H:i:s')
            ),
            ArrayUtils::tryGet(
                $data,
                MysteryShopperInputFilter::FIELD_TEST_DATE,
                is_null($campaign)? null : $campaign->getTestDate()->format('Y-m-d H:i:s')
            )
        );

        return $data;
    }

    /**
     * To prepare required context for the CampaignDateValidator which needs understanding of all the potential existing
     *  campaigns for the same vehicle (aka. booked date ranges).
     *
     * @param IncognitoVehicle[]    $bookedCampaigns
     * @param int                   $filteredCampaignId to filter out the upd campaign when needed
     * @return array
     */
    private function prepareContext($bookedCampaigns, $filteredCampaignId = null)
    {
        $bookedDateRanges = [];

        foreach ($bookedCampaigns as $incognitoVehicle) {

            if (!is_null($filteredCampaignId) && $filteredCampaignId == $incognitoVehicle->getId()) {
                continue;
            }

            $bookedDateRanges[] = [
                MysteryShopperInputFilter::FIELD_START_DATE => $incognitoVehicle->getStartDate(),
                MysteryShopperInputFilter::FIELD_END_DATE   => $incognitoVehicle->getEndDate(),
            ];
        }

        $context[MysteryShopperInputFilter::FIELD_BOOKED_DATE_RANGES] = $bookedDateRanges;

        return $context;
    }

    /**
     * @return \DvsaEntities\Entity\Person
     * @throws NotFoundException
     */
    private function getPerson()
    {
        $personId = $this->identityProvider->getIdentity()->getUserId();
        // If person not found a not found exception will be thrown
        $person = $this->personRepository->get($personId);

        return $person;
    }
}
