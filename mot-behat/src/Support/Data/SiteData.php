<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Vts;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Data\Params\AuthorisedExaminerParams;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Response;
use DvsaCommon\Dto\Search\SiteSearchParamsDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Site\SiteListDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use DvsaCommon\Utility\DtoHydrator;

class SiteData
{
    const DEFAULT_NAME = "Crazy cars garage";

    private $authorisedExaminerData;
    private $vts;
    private $userData;
    private $testSupportHelper;

    private $siteCollection;
    private $siteSearchCollection;

    public function __construct(
        AuthorisedExaminerData $authorisedExaminerData,
        Vts $vts,
        UserData $userData,
        TestSupportHelper $testSupportHelper
    )
    {
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->vts = $vts;
        $this->userData = $userData;
        $this->testSupportHelper = $testSupportHelper;
        $this->siteCollection = SharedDataCollection::get(SiteDto::class);
    }

    public function createAreaOffice($name = "Area Office")
    {
        $site = $this->createUnassociatedSite([SiteParams::NAME => $name, SiteParams::TYPE => SiteTypeCode::AREA_OFFICE]);

        $siteNumber = $this
            ->testSupportHelper
            ->getVtsService()
            ->changeSiteNumberForAreaOffice($site->getId());

        $site->setSiteNumber($siteNumber);

        return $site;
    }

    public function createUnassociatedSiteByUser(AuthenticatedUser $user, array $params = [])
    {
        $default = SiteParams::getDefaultParams();
        $params = array_replace($default, $params);

        $site = $this->tryGet($params[SiteParams::NAME]);
        if ($site !== null) {
            return $site;
        }

        $response = $this->vts->create($user->getAccessToken(), $params);
        $responseBody = $response->getBody();
        if (!is_object($responseBody)) {
            throw new \Exception("createSite: responseBody is not an object: failed to create Vts");
        }

        $site = $responseBody->getData();
        $dto = new SiteDto();
        $dto
            ->setId($site[SiteParams::ID])
            ->setSiteNumber($site[SiteParams::SITE_NUMBER])
            ->setName($params[SiteParams::NAME])
            ->setType($params[SiteParams::TYPE])
        ;

        $this->siteCollection->add($dto, $params[SiteParams::NAME]);

        return $dto;
    }

    public function createUnassociatedSite(array $params = [])
    {
        return $this->createUnassociatedSiteByUser($this->userData->createAreaOffice1User(), $params);
    }

    public function create($name = self::DEFAULT_NAME)
    {
        return $this->createWithParams([SiteParams::NAME => $name]);
    }

    public function createWithParams(array $params = [])
    {
        return $this->createBy($this->userData->createAreaOffice1User(), $params);
    }

    public function createBy(AuthenticatedUser $user, array $params = [])
    {
        $default = $default = SiteParams::getDefaultParams();
        $default = array_merge(
            $default,
            [
                AuthorisedExaminerParams::AE_NAME => AuthorisedExaminerData::DEFAULT_NAME,
                SiteParams::START_DATE => null,
                SiteParams::END_DATE => null
            ]
        );

        $data = array_replace($default, $params);

        $site = $this->tryGet($data[SiteParams::NAME]);
        if ($site !== null) {
            return $site;
        }

        $site = $this->createUnassociatedSiteByUser($user, $data);

        $slots = ArrayUtils::tryGet($data, AuthorisedExaminerParams::SLOTS, AuthorisedExaminerData::DEFAULT_SLOTS);
        $ae = $this->authorisedExaminerData->createWithCustomSlots($slots, $data[AuthorisedExaminerParams::AE_NAME]);

        $site->setOrganisation($ae);

        $this->authorisedExaminerData->linkAuthorisedExaminerWithSite($ae, $site);

        if ($data[SiteParams::START_DATE] || $data[SiteParams::END_DATE]) {
            $this->changeAssociatedDate($ae->getId(), $site->getId(), $data[SiteParams::START_DATE], $data[SiteParams::END_DATE]);
        }

        $this->siteCollection->add($site, $data[SiteParams::NAME]);

        return $site;
    }

    public function getTestLogs(AuthenticatedUser $user, SiteDto $siteDto)
    {
        $response = $this->vts->getTestLogs(
            $user->getAccessToken(), $siteDto->getId()
        );

        return $response->getBody()->getData()["resultCount"];
    }

    public function changeAssociatedDate($aeId, $siteId, $startDate, $endDate = null)
    {
        if ($startDate !== null && (!$startDate instanceof \DateTime)) {
            $startDate = new \DateTime($startDate);
        }

        if ($endDate !== null && (!$endDate instanceof \DateTime)) {
            $endDate = new \DateTime($endDate);
        }

        $this
            ->testSupportHelper
            ->getVtsService()
            ->changeAssociatedDate(
                $aeId,
                $siteId,
                $startDate,
                $endDate
            );
    }

    public function changeEndDateOfAssociation($aeId, $siteId, \DateTime $endDate)
    {
        $this
            ->testSupportHelper
            ->getVtsService()
            ->changeEndDateOfAssociation($aeId, $siteId, $endDate);
    }

    public function searchVtsByName(AuthenticatedUser $user, $siteName)
    {
        $params = [ SiteParams::SITE_NAME => $siteName];
        return $this->searchVtsByParams($user, $params);
    }

    public function searchVtsByNumber(AuthenticatedUser $user, $siteNumber)
    {
        $params = [ SiteParams::SITE_NUMBER => $siteNumber];
        return $this->searchVtsByParams($user, $params);
    }

    public function searchVtsBySiteTown(AuthenticatedUser $user, $town)
    {
        $params = [ SiteParams::SITE_TOWN => $town];
        return $this->searchVtsByParams($user, $params);
    }

    public function searchVtsBySitePostcode(AuthenticatedUser $user, $postcode)
    {
        $params = [ SiteParams::SITE_POSTCODE => $postcode];
        return $this->searchVtsByParams($user, $params);
    }

    public function searchVtsByParams(AuthenticatedUser $user, array $params)
    {
        $defaults = [
            "pageNr" => 1,
            "rowsCount" => 10,
            "sortBy" => "site.name",
            "sortDirection" => "ASC",
            '_class' => SiteSearchParamsDto::class,
        ];

        $params = array_replace($defaults, $params);
        $response = $this->vts->searchVts($params, $user->getAccessToken());

        /** @var SiteListDto $siteListDto */
        $siteListDto = DtoHydrator::jsonToDto($response->getBody()->getData());

        return $siteListDto;
    }

    /**
     * @param AuthenticatedUser $user
     * @param $siteId
     * @return SiteDto
     */
    public function getVtsDetails(AuthenticatedUser $user, $siteId)
    {
        $response = $this->vts->getVtsDetails(
            $siteId,
            $user->getAccessToken()
        );

        return DtoHydrator::jsonToDto($response->getBody()->getData());
    }

    /**
     * @param string $siteName
     * @return VehicleTestingStationDto
     */
    public function get($siteName = self::DEFAULT_NAME)
    {
        $site = $this->tryGet($siteName);
        if ($site === null) {
            throw new \InvalidArgumentException(sprintf("Site with name '%s' not found", $siteName));
        }

        return $site;
    }

    /**
     * @param string $siteName
     * @return SiteDto|null
     */
    public function tryGet($siteName = self::DEFAULT_NAME)
    {
        if ($this->siteCollection->containsKey($siteName)) {
            return $this->siteCollection->get($siteName);
        }

        $sites = $this->siteCollection->filter(function (SiteDto $site) use ($siteName) {
            return $site->getName() === $siteName;
        });

        if (count($sites) === 1) {
            return $sites->first();
        }

        return null;
    }

    public function getAll()
    {
        return $this->siteCollection;
    }

    public function getSiteSearchCollection()
    {
        return $this->siteSearchCollection;
    }

    /**
     * @return Response
     */
    public function getLastResponse()
    {
        return $this->vts->getLastResponse();
    }
}
