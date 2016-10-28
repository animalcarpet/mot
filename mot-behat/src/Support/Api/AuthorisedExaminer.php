<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultAreaOffice;
use Dvsa\Mot\Behat\Support\Data\Map\RoleMap;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Request;
use DvsaCommon\Dto\Common\AuthForAeStatusDto;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\AuthorisationForAuthorisedExaminerStatusCode;
use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Enum\OrganisationSiteStatusCode;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilder;
use DvsaCommon\UrlBuilder\OrganisationUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;

class AuthorisedExaminer extends MotApi
{
    const AE_NAME = 'some ae name';
    const POSITION_DELETE = '/organisation/{organisation_id}/position/{position_id}';
    const TEST_LOGS = 'authorised-examiner/{authorised_examiner_id}/mot-test-log';

    public function search($token, $aeNumber)
    {
        return $this->sendGetRequest(
            $token,
            AuthorisedExaminerUrlBuilder::of()->authorisedExaminerByNumber($aeNumber)
            );
    }

    public function getAuthorisedExaminerDetails($token, $aeId)
    {
        return $this->sendGetRequest(
            $token,
            AuthorisedExaminerUrlBuilder::of($aeId)
        );
    }

    public function getAuthorisedExaminerPositions($token, $aeId)
    {
        $url = OrganisationUrlBuilder::position($aeId)->toString();

        return $this->sendGetRequest(
            $token,
            $url
        );
    }

    public function createAuthorisedExaminer($token)
    {
        $aeDto = new AuthorisedExaminerAuthorisationDto();
        $aeDto->setAssignedAreaOffice(DefaultAreaOffice::get()->getSiteNumber());
        $statusDto = new AuthForAeStatusDto();
        $statusDto->setCode(AuthorisationForAuthorisedExaminerStatusCode::APPROVED);
        $aeDto->setStatus($statusDto);

        $dto = (new OrganisationDto())
            ->setAuthorisedExaminerAuthorisation($aeDto)
            ->setContacts(
                [
                    (new OrganisationContactDto())
                        ->setType(OrganisationContactTypeCode::REGISTERED_COMPANY)
                        ->setAddress(
                            (new AddressDto())
                                ->setAddressLine1('AddressLine1')
                                ->setTown('Town')
                                ->setPostcode('Postcode')
                        )
                        ->setEmails(
                            [
                                (new EmailDto())
                                    ->setIsSupplied(false)
                                    ->setIsPrimary(true)
                            ]
                        )
                        ->setPhones(
                            [
                                (new PhoneDto())
                                    ->setContactType(PhoneContactTypeCode::BUSINESS)
                                    ->setIsPrimary(true)
                                    ->setNumber('0123456789')
                            ]
                        )
                ]
            )
            ->setName(self::AE_NAME)
            ->setCompanyType(CompanyTypeCode::SOLE_TRADER);

        return $this->sendPostRequest(
            $token,
            'authorised-examiner',
            DtoHydrator::dtoToJson($dto)
        );
    }

    public function updateStatusAuthorisedExaminer($token, $id, $status)
    {
        return $this->sendPatchRequest(
            $token,
            AuthorisedExaminerUrlBuilder::of($id),
            [AuthorisedExaminerPatchModel::STATUS => $status]
        );
    }

    public function nominate($userId, $orgRoleCode, $orgId, $token)
    {
        $orgRoleId = (new RoleMap())->get($orgRoleCode)->getId();

        return $this->sendPostRequest(
            $token,
            OrganisationUrlBuilder::position($orgId),
            [
                "nomineeId" => $userId,
                "roleId" => $orgRoleId
            ]
        );
    }

    public function linkAuthorisedExaminerWithSite($token, $aeId, $siteNumber)
    {
        $linkUrl = AuthorisedExaminerUrlBuilder::siteLink($aeId);

        return $this->sendPostRequest(
            $token,
            $linkUrl,
            [SiteParams::SITE_NUMBER => $siteNumber]
        );
    }

    public function unlinkSiteFromAuthorisedExaminer($token, $aeId, $linkId)
    {
        $unlinkUrl = AuthorisedExaminerUrlBuilder::siteLink($aeId, $linkId);
        $request = new Request(
            'PUT',
            $unlinkUrl,
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $token],
            //todo add const: status: surrender
            json_encode(OrganisationSiteStatusCode::SURRENDERED)
        );

        $response = $this->client->request($request);
        $this->lasteResponse = $response;
        return $response;
    }

    public function denominate($orgId, $positionId, $token)
    {
        $url = self::POSITION_DELETE;
        $url = str_replace("{organisation_id}", $orgId, $url);
        $url = str_replace("{position_id}", $positionId, $url);

        return $this->sendDeleteRequest(
            $token,
            $url
        );
    }

    public function getTodaysTestLogs($token, $examinerId, $siteId = null)
    {
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
            str_replace('{authorised_examiner_id}', $examinerId, self::TEST_LOGS),
            $params
        );
    }
}
