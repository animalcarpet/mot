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
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\DtoHydrator;
use TestSupport\Helper\TestDataResponseHelper;
use DvsaCommon\UrlBuilder\UrlBuilder;
use TestSupport\Helper\DataGeneratorHelper;
use TestSupport\Helper\TestSupportRestClientHelper;
use Doctrine\DBAL\DBALException;

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
    )
    {
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
            'startDate' =>  ArrayUtils::tryGet($data, 'startDate', $dataGenerator->startDate()),
            'classes' => VehicleClassCode::getAll(),
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
     * @param int $siteId
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

            $dataGenerator = DataGeneratorHelper::buildForDifferentiator($data);

            $stmt = $this->em->getConnection()->prepare("
                INSERT INTO organisation_site_map (
                  organisation_id,
                  site_id,
                  trading_name,
                  status_id,
                  start_date,
                  end_date,
                  status_changed_on,
                  created_by
                ) VALUES (?, ?, ?, ?, ?, ?, NOW(),
                  (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data')
                )"
            );

            $stmt->bindValue(1, $data['aeId']);
            $stmt->bindValue(2, $siteId);
            $stmt->bindValue(3, ArrayUtils::tryGet($data, 'siteName', $dataGenerator->siteName()));
            $stmt->bindValue(4, self::STATUS_APPROVED);
            $stmt->bindValue(5, $data['startDate']);
            $stmt->bindValue(6, date('Y-m-d H:i:s', strtotime('+6 months')));
            $stmt->execute();
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
                'weekday' => $i,
                'openTime' => $openTime,
                'closeTime' => $closeTime,
                'isClosed' => $isClosed
            ];
        }

        $urlBuilder = $this->urlBuilder->vehicleTestingStation()->routeParam('id', $siteId)->siteOpeningHours();

        $this->restClientHelper->getJsonClient($data)->put($urlBuilder, $openingTimes);
    }

    public function changeAssociatedDate($aeId, $siteId, \DateTime $startDate, \DateTime $endDate = null)
    {
        $data = ["start_date" => $startDate->format("Y-m-d H:i:s")];
        if ($endDate !== null) {
            $data["end_date"] = $endDate->format("Y-m-d H:i:s");
        }

        $this->em->getConnection()->update(
            'organisation_site_map',
            $data,
            ['organisation_id' => $aeId, 'site_id' => $siteId]
        );
    }

    public function changeEndDateOfAssociation($aeId, $siteId, \DateTime $endDate)
    {
        $this->em->getConnection()->update(
            'organisation_site_map',
            ["end_date" => $endDate->format("Y-m-d H:i:s")],
            ['organisation_id' => $aeId, 'site_id' => $siteId]
        );
    }

    public function changeSiteNumberForAreaOffice($siteId)
    {
        $created = false;

        do {
            try {
                $siteNumber = $this->updateAreaOfficeNumber($siteId);
                $created = true;
            } catch(DBALException $e) {

            }

        } while ($created === false);

        return $siteNumber;

    }

    private function updateAreaOfficeNumber($siteId)
    {
        $siteNumber =  (int) $this->em->getConnection()->fetchColumn(
            "SELECT MAX(site_number) FROM site INNER JOIN site_type ON site.type_id = site_type.id WHERE site_type.code = ? AND LENGTH(site.site_number) = ?",
            [SiteTypeCode::AREA_OFFICE, 2]
        );

        $siteNumber += 1;
        $siteNumber = str_pad($siteNumber, 2, "0", STR_PAD_LEFT);

        $this->em->getConnection()->update(
            "site",
            ["site_number" => $siteNumber],
            ["id" => $siteId]
        );

        return $siteNumber;
    }
}
