<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Request;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Site\EnforcementSiteAssessmentDto;
use DvsaCommon\Dto\Site\SiteContactDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Enum\SiteStatusCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Model\VehicleTestingStation;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommon\Dto\Site\FacilityDto;
use DvsaCommon\Dto\Site\FacilityTypeDto;
use DvsaCommon\Constants\FacilityTypeCode;
use DvsaCommon\Validator\EmailAddressValidator;

class Vts extends MotApi
{
    const SITE_NAME = 'Garage Name';
    const PATH = 'vehicle-testing-station/{vts_id}';
    const SEARCH = 'vehicle-testing-station/search';
    const POSITION = 'site/{site_id}/position';
    const TESTING_FACILITIES = 'vehicle-testing-station/{site_id}/testing-facilities';
    const SITE_DETAILS = 'vehicle-testing-station/{site_id}/site-details';
    const RISK_ASSESMENT = 'vehicle-testing-station/{site_id}/risk-assessment';
    const TEST_LOG = 'vehicle-testing-station/{site_id}/mot-test-log';

    public function getVtsDetails($vtsId, $token)
    {
        return $this->sendGetRequest(
            $token,
            str_replace('{vts_id}', $vtsId, self::PATH)
        );
    }

    public function searchVts($params, $token)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            self::SEARCH,
            $params
        );
    }

    public function nominateToRole($nomineeId, $siteRoleCode, $siteId, $token)
    {
        $data = [
            "nomineeId" => $nomineeId,
            "roleCode" => $siteRoleCode
        ];

        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            str_replace("{site_id}", $siteId, self::POSITION),
            $data
        );
    }

    protected function getDefaults()
    {
        return SiteParams::getDefaultParams();
    }

    public function create($token, $site = [])
    {
        $site = array_merge($this->getDefaults(), $site);

        return $this->sendPostRequest(
            $token,
            UrlBuilder::of()->vehicleTestingStation()->toString(),
            DtoHydrator::dtoToJson($this->generateSiteDto($site))
        );
    }

    /**
     * @param string $token
     * @param int $siteId
     * @param array $site
     * @param int $numOptl
     * @param int $numTptl
     */
    public function updateTestingFacilities($token, $siteId, array $site, $numOptl, $numTptl)
    {
        $site = array_merge($this->getDefaults(), $site);
        $siteDto = $this->generateSiteDto($site);

        // add number of specified testing facilities
        $facilities = [];
        for ($i = 0; $i < $numOptl; $i++) {
            $facility = (new FacilityDto())
                ->setName(FacilityTypeCode::ONE_PERSON_TEST_LANE)
                ->setType((new FacilityTypeDto())->setCode(FacilityTypeCode::ONE_PERSON_TEST_LANE));
            array_push($facilities, $facility);
        }

        for ($i = 0; $i < $numTptl; $i++) {
            $facility = (new FacilityDto())
                ->setName(FacilityTypeCode::TWO_PERSON_TEST_LANE)
                ->setType((new FacilityTypeDto())->setCode(FacilityTypeCode::TWO_PERSON_TEST_LANE));
            array_push($facilities, $facility);
        }

        $siteDto->setFacilities($facilities);

        return $this->sendPutRequest(
            $token,
            str_replace('{site_id}', $siteId, self::TESTING_FACILITIES),
            DtoHydrator::dtoToJson($siteDto)
        );
    }

    public function updateSiteDetails($token, $siteId, array $site)
    {
        return $this->sendPatchRequest(
            $token,
            VehicleTestingStationUrlBuilder::vtsDetails($siteId),
            $site
        );
    }

    private function generateSiteDto($site)
    {
        $address = (new AddressDto())
            ->setAddressLine1($site[SiteParams::ADDRESS_LINE_1])
            ->setPostcode($site[SiteParams::POSTCODE])
            ->setTown($site[SiteParams::TOWN]);

        $email = (new EmailDto())
            ->setEmail($site[SiteParams::EMAIL])
            ->setIsPrimary(true);

        $phone = (new PhoneDto())
            ->setNumber($site[SiteParams::PHONE_NUMBER])
            ->setContactType(PhoneContactTypeCode::BUSINESS)
            ->setIsPrimary(true);

        $contact = new SiteContactDto();
        $contact
            ->setType(SiteContactTypeCode::BUSINESS)
            ->setAddress($address)
            ->setEmails([$email])
            ->setPhones([$phone]);

        $facility = (new FacilityDto())
            ->setName(FacilityTypeCode::ONE_PERSON_TEST_LANE)
            ->setType((new FacilityTypeDto())->setCode(FacilityTypeCode::ONE_PERSON_TEST_LANE));

        //  logical block :: assemble dto
        $siteDto = new VehicleTestingStationDto();
        $siteDto
            ->setName($site[SiteParams::NAME])
            ->setStatus($site[SiteParams::STATUS])
            ->setType($site[SiteParams::TYPE])
            ->setTestClasses($site[SiteParams::CLASSES])
            ->setIsDualLanguage(false)
            ->setFacilities([$facility])
            ->setIsOptlSelected(true)
            ->setIsTptlSelected(true)
            ->setContacts([$contact]);

        return $siteDto;
    }

    public function addRiskAssessment($token, $siteId, $riskAssessment = [])
    {
        $defaults = [
            "id" => null,
            "siteAssessmentScore" => null,
            "aeRepresentativesFullName" => null,
            "aeRepresentativesRole" => null,
            "aeRepresentativesUserId" => null,
            "testerUserId" => null,
            "testerFullName" => null,
            "dvsaExaminersFullName" => null,
            "dvsaExaminersUserId" => null,
            "dateOfAssessment" => null,
            "siteId" => $siteId,
            "aeOrganisationId" => null,
            "validateOnly" => false,
            "userIsNotAssessor" => false,
            "_class" => "DvsaCommon\\Dto\\Site\\EnforcementSiteAssessmentDto"
        ];

        $riskAssessment = array_replace($defaults, $riskAssessment);

        return $this->sendPostRequest(
            $token,
            str_replace('{site_id}', $siteId, self::RISK_ASSESMENT),
            $riskAssessment
        );
    }

    public function getRiskAssessment($token, $siteId)
    {
        return $this->sendGetRequest(
            $token,
            str_replace('{site_id}', $siteId, self::RISK_ASSESMENT)
        );
    }

    public function removeAllTestClasses($token, $siteId)
    {
        return $this->updateSiteDetails($token, $siteId,
            [
                VehicleTestingStation::PATCH_PROPERTY_CLASSES => [],
                '_class' => VehicleTestingStationDto::class,
            ]);
    }

    public function getTestLogs($token, $siteId)
    {
        //todo probably there's no need to pass that much params to api
        $params = [
            'organisationId' => NULL,
            'siteId' => $siteId,
            'siteNr' => NULL,
            'personId' => NULL,
            'vehicleId' => NULL,
            'vehicleRegNr' => NULL,
            'vehicleVin' => NULL,
            'dateFromTs' => strtotime('today 01 am'),
            'dateToTs' => strtotime('tomorrow 01 am'),
            'status' =>
                array (
                    0 => MotTestStatusName::ABANDONED,
                    1 => MotTestStatusName::ABORTED,
                    2 => MotTestStatusName::ABORTED_VE,
                    3 => MotTestStatusName::FAILED,
                    4 => MotTestStatusName::PASSED,
                    5 => MotTestStatusName::REFUSED,
                ),
            'testType' =>
                array (
                    0 => MotTestTypeCode::NORMAL_TEST,
                    1 => MotTestTypeCode::PARTIAL_RETEST_LEFT_VTS,
                    2 => MotTestTypeCode::PARTIAL_RETEST_REPAIRED_AT_VTS,
                    3 => MotTestTypeCode::RE_TEST,
                ),
            'format' => 'DATA_CSV',
            'isSearchRecent' => false,
            'pageNr' => 1,
            'rowsCount' => 50000,
            'searchTerm' => NULL,
            'sortBy' => 'testDateTime',
            'sortDirection' => 'DESC',
            'start' => NULL,
            'filter' => NULL,
            'isApiGetData' => true,
            'isApiGetTotalCount' => false,
            'isEsEnabled' => NULL,
            '_class' => 'DvsaCommon\Dto\Search\MotTestSearchParamsDto',
        ];

        return $this->sendPostRequest(
            $token,
            str_replace('{site_id}', $siteId, self::TEST_LOG),
            $params
        );
    }
}