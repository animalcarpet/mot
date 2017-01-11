<?php

namespace TestSupport\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Constants\FacilityTypeCode;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Site\FacilityDto;
use DvsaCommon\Dto\Site\FacilityTypeDto;
use DvsaCommon\Dto\Site\SiteContactDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\DtoHydrator;
use TestSupport\Helper\TestDataResponseHelper;
use DvsaCommon\UrlBuilder\UrlBuilder;
use TestSupport\Helper\DataGeneratorHelper;
use TestSupport\Helper\TestSupportRestClientHelper;

class VtsService
{

    const STATUS_APPROVED = 2;
    const NON_WORKING_DAY_COUNTRY_CODE = "GBENG";

    /**
     * @var TestSupportRestClientHelper
     */
    private $restClientHelper;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    public function __construct(
        TestSupportRestClientHelper $restClientHelper,
        EntityManager $em,
        UrlBuilder $urlBuilder
    ) {
        $this->restClientHelper = $restClientHelper;
        $this->em = $em;
        $this->urlBuilder = $urlBuilder;
    }

    public function create(array $data)
    {
        $dataGenerator = DataGeneratorHelper::buildForDifferentiator($data);
        $email = $dataGenerator->emailAddress();

        $default = [
            'name' => ArrayUtils::tryGet($data, 'siteName', $dataGenerator->siteName()),
            'addressLine1' => ArrayUtils::tryGet($data, 'addressLine1', $dataGenerator->addressLine1()),
            'town' => ArrayUtils::tryGet($data, 'town', 'Boston'),
            'country' => ArrayUtils::tryGet($data, 'country', 'England'),
            'postcode' => ArrayUtils::tryGet($data, 'postcode', 'BT2 4RR'),
            'email' => ArrayUtils::tryGet($data, 'email', $email),
            'phoneNumber' => ArrayUtils::tryGet($data, 'phoneNumber', $dataGenerator->phoneNumber()),
            'classes' => [1, 2, 3, 4, 5, 7],
        ];
        $data = array_merge($data, $default);

        $result = $this->restClientHelper->getJsonClient($data)->post(
            UrlBuilder::of()->vehicleTestingStation()->toString(),
            DtoHydrator::dtoToJson($this->generateSiteDto($data))
        );

        $siteId = $result['data']['id'];
        $siteNumber = $result['data']['siteNumber'];
        $this->addOtherDataToSite($siteId, $data);

        return TestDataResponseHelper::jsonOk(
            [
                "message" => "VTS created",
                "id" => $siteId,
                "siteNumber" => $siteNumber,
                "name" => $data["name"],
                "town" => $data["town"],
                "postcode" => $data["postcode"]
            ]
        );
    }

    private function generateSiteDto($site)
    {
        $address = (new AddressDto())
            ->setAddressLine1($site['addressLine1'])
            ->setPostcode($site['postcode'])
            ->setCountry($site['country'])
            ->setTown($site['town']);

        $email = (new EmailDto())
            ->setEmail($site['email'])
            ->setIsPrimary(true);

        $phone = (new PhoneDto())
            ->setNumber($site['phoneNumber'])
            ->setContactType(PhoneContactTypeCode::BUSINESS)
            ->setIsPrimary(true);

        $contact = new SiteContactDto();
        $contact
            ->setType(SiteContactTypeCode::BUSINESS)
            ->setAddress($address)
            ->setEmails([$email])
            ->setPhones([$phone]);

        $facility = (new FacilityDto())
            ->setName('OPTL')
            ->setType((new FacilityTypeDto())->setCode(FacilityTypeCode::ONE_PERSON_TEST_LANE));

        //  logical block :: assemble dto
        $siteDto = new VehicleTestingStationDto();
        $siteDto
            ->setName($site['name'])
            ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
            ->setTestClasses($site['classes'])
            ->setIsDualLanguage(false)
            ->setContacts([$contact])
            ->setFacilities([$facility])
            ->setIsOptlSelected(true)
            ->setIsTptlSelected(true);

        return $siteDto;
    }

    /**
     * @param int   $siteId
     * @param array $data
     *
     * @throws \Exception
     */
    private function addOtherDataToSite($siteId, $data)
    {
        if (isset($data['aeId'])) {
            $this->em->getConnection()->executeUpdate(
                "UPDATE site SET organisation_id = ? WHERE id = ?",
                [$data['aeId'], $siteId]
            );
        }

        $openingTimes = [];
        for ($i = 1; $i < 8; $i++) {

            /*
             * This isn't ideal. There is a half-hour closed window always. Overnight tests could well choke unless
             * started after midnight.
             */
            $openTime = '00:00:00';
            $closeTime = '23:30:00';
            $isClosed = false;

            $openingTimes['weeklySchedule'][] = [
                'weekday'   => $i,
                'openTime'  => $openTime,
                'closeTime' => $closeTime,
                'isClosed'  => $isClosed
            ];
        }

        $urlBuilder = $this->urlBuilder->vehicleTestingStation()->routeParam('id', $siteId)->siteOpeningHours();

        $this->restClientHelper->getJsonClient($data)->put($urlBuilder, $openingTimes);
    }
}
