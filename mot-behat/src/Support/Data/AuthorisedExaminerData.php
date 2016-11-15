<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\AuthorisedExaminer;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultAreaOffice;
use Dvsa\Mot\Behat\Support\Data\Params\AssignedAreaOfficeParams;
use Dvsa\Mot\Behat\Support\Data\Params\AuthorisedExaminerAuthorisationParams;
use Dvsa\Mot\Behat\Support\Data\Params\AuthorisedExaminerParams;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Dto\AreaOffice\AreaOfficeDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\AuthorisationForAuthorisedExaminerStatusCode;
use DvsaCommon\Utility\DtoHydrator;
use TestSupport\Service\AccountService;
use Dvsa\Mot\Behat\Support\Response;
use Zend\Http\Response as HttpResponse;

class AuthorisedExaminerData
{
    const DEFAULT_NAME = "Vehicle Fixes Ltd";
    const DEFAULT_SLOTS = 1001;

    private $userData;
    private $authorisedExaminer;
    private $testSupportHelper;

    private $aeCollection;

    public function __construct(
        UserData $userData,
        AuthorisedExaminer $authorisedExaminer,
        TestSupportHelper $testSupportHelper
    )
    {
        $this->userData = $userData;
        $this->authorisedExaminer = $authorisedExaminer;
        $this->testSupportHelper = $testSupportHelper;
        $this->aeCollection = SharedDataCollection::get(OrganisationDto::class);
    }

    public function create($name = self::DEFAULT_NAME)
    {
        return $this->createWithCustomSlots(self::DEFAULT_SLOTS, $name);
    }

    public function createWithCustomSlots($slots, $name = self::DEFAULT_NAME)
    {
        $ae = $this->tryGet($name);
        if ($ae !== null) {
            return $ae;
        }

        $ae = $this->createWithoutAedm($name, $slots);
        $this->userData->createAedmAssignedWithOrganisation($ae->getId());

        return $ae;
    }

    /**
     * @param int $slots
     * @param string $name
     * @return OrganisationDto
     */
    public function createWithoutAedm($name = self::DEFAULT_NAME, $slots = self::DEFAULT_SLOTS)
    {
        $ae = $this->tryGet($name);
        if ($ae !== null) {
            return $ae;
        }

        $ao1User = $this->userData->createAreaOffice1User();

        return $this->createByUser($ao1User, $name, $slots);
    }

    public function createByUser(AuthenticatedUser $user, $aeName = self::DEFAULT_NAME, $slots = self::DEFAULT_SLOTS)
    {
        $ae = $this->createUnapprovedByUser($user, $aeName, $slots);
        //$this->updateStatusToApprove($ae, $user);

        return $ae;
    }

    public function createUnapprovedByUser(AuthenticatedUser $user, $aeName = self::DEFAULT_NAME, $slots = self::DEFAULT_SLOTS)
    {
        $data = [
            AuthorisedExaminerParams::SLOTS => $slots,
            PersonParams::REQUESTOR => [
                PersonParams::USERNAME => $user->getUsername(),
                PersonParams::PASSWORD => AccountService::PASSWORD
            ],
            AuthorisedExaminerParams::AREA_OFFICE_SITE_NUMBER => DefaultAreaOffice::get()->getSiteNumber(),
        ];

        $ae = $this->testSupportHelper->getAeService()->create($data);

        $assignedAreaOffice = new AreaOfficeDto();
        $assignedAreaOffice->setSiteNumber($data[AuthorisedExaminerParams::AREA_OFFICE_SITE_NUMBER]);

        $aea = new AuthorisedExaminerAuthorisationDto();
        $aea->setAuthorisedExaminerRef($ae->data[AuthorisedExaminerParams::AE_REF]);
        $aea->setAssignedAreaOffice($assignedAreaOffice);

        $dto = new OrganisationDto();
        $dto
            ->setId($ae->data[AuthorisedExaminerParams::ID])
            ->setName($ae->data[AuthorisedExaminerParams::AE_NAME])
            ->setAuthorisedExaminerAuthorisation($aea)
        ;

        $this->aeCollection->add($dto, $aeName);

        return $dto;
    }

    public function linkAuthorisedExaminerWithSite(OrganisationDto $ae, SiteDto $site)
    {
        $ao1User = $this->userData->createAreaOffice1User();
        return $this->linkAuthorisedExaminerWithSiteByUser($ao1User, $ae, $site);

    }

    public function linkAuthorisedExaminerWithSiteByUser(AuthenticatedUser $user, OrganisationDto $ae, SiteDto $site)
    {
        $response = $this->authorisedExaminer->linkAuthorisedExaminerWithSite(
            $user->getAccessToken(),
            $ae->getId(),
            $site->getSiteNumber()
        );

        return $response->getBody()->getData()["id"];
    }

    public function unlinkSiteFromAuthorisedExaminer(OrganisationDto $ae, SiteDto $site)
    {
        $linkId = $this->testSupportHelper->getAeService()->getLinkId($ae->getId(), $site->getId());
        $ao1User = $this->userData->createAreaOffice1User();

        $response = $this->authorisedExaminer->unlinkSiteFromAuthorisedExaminer($ao1User->getAccessToken(), $ae->getId(), $linkId);
        return $response->getBody()->getData();
    }

    public function updateStatusToApprove(OrganisationDto $ae, AuthenticatedUser $user)
    {
        $this->updateStatus($ae, $user, AuthorisationForAuthorisedExaminerStatusCode::APPROVED);
    }

    public function updateStatus(OrganisationDto $ae, AuthenticatedUser $user, $status)
    {
        $this->authorisedExaminer->updateStatusAuthorisedExaminer(
            $user->getAccessToken(),
            $ae->getId(),
            $status
        );
    }

    /**
     * @param AuthenticatedUser $user
     * @param OrganisationDto $ae
     * @return SearchResultDto
     */
    public function getTodaysTestLogs(AuthenticatedUser $user, OrganisationDto $ae)
    {
        $response = $this->authorisedExaminer->getTodaysTestLogs(
            $user->getAccessToken(), $ae->getId()
        );

        return DtoHydrator::jsonToDto($response->getBody()->getData());
    }

    /**
     * @param AuthenticatedUser $user
     * @param SiteDto $site
     * @return SearchResultDto
     */
    public function getTodaysSiteTestLogs(AuthenticatedUser $user, SiteDto $site)
    {
        $response = $this->authorisedExaminer->getTodaysTestLogs(
            $user->getAccessToken(),
            $site->getOrganisation()->getId(),
            $site->getId()
        );

        return DtoHydrator::jsonToDto($response->getBody()->getData());
    }

    public function search(AuthenticatedUser $user, $number)
    {
        $response = $this->authorisedExaminer->search($user->getAccessToken(), $number);
        $data = $response->getBody()->getData();

        $assignedAreaOfficeData = $data[AuthorisedExaminerParams::AUTHORISED_EXAMINER_AUTHORISATION][AuthorisedExaminerAuthorisationParams::ASSIGNED_AREA_OFFICE];

        $assignedAreaOffice = new AreaOfficeDto();
        $assignedAreaOffice->setSiteNumber($assignedAreaOfficeData[AssignedAreaOfficeParams::SITE_NUMBER]);

        $aea = new AuthorisedExaminerAuthorisationDto();
        $aea->setAuthorisedExaminerRef($data[AuthorisedExaminerParams::AUTHORISED_EXAMINER_AUTHORISATION][AuthorisedExaminerAuthorisationParams::AUTHORISED_EXAMINER_REF]);
        $aea->setAssignedAreaOffice($assignedAreaOffice);

        $dto = new OrganisationDto();
        $dto
            ->setId($data[AuthorisedExaminerParams::ID])
            ->setName($data[AuthorisedExaminerParams::NAME])
            ->setAuthorisedExaminerAuthorisation($aea)
        ;

        return $dto;
    }

    public function get($aeName = self::DEFAULT_NAME)
    {
        $ae = $this->tryGet($aeName);
        if ($ae === null) {
            throw new \InvalidArgumentException(sprintf("Authorised Examiner with name '%s' not found", $aeName));
        }

        return $ae;
    }

    /**
     * @param string $aeName
     * @return OrganisationDto|null
     */
    public function tryGet($aeName = self::DEFAULT_NAME)
    {
        if ($this->aeCollection->containsKey($aeName)) {
            return $this->aeCollection->get($aeName);
        }

        $aes = $this->aeCollection->filter(function (OrganisationDto $ae) use ($aeName) {
            return $ae->getName() === $aeName;
        });

        if (count($aes) === 1) {
            return $aes->first();
        }

        return null;
    }

    public function getAll()
    {
        return $this->aeCollection;
    }

    /**
     * @return Response
     */
    public function getLastResponse()
    {
        return $this->authorisedExaminer->getLastResponse();
    }
}
