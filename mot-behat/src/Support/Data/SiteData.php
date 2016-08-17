<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Vts;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Response;
use DvsaCommon\Dto\Site\SiteDto;

class SiteData
{
    const DEFAULT_NAME = "default";

    private $defaultSiteData = [
        "name" => self::DEFAULT_NAME,
        'town' => "Toulouse",
        'postcode' => "BS1 3LL",
    ];

    private $authorisedExaminerData;
    private $vts;
    private $userData;
    private $testSupportHelper;

    private $siteCollection;
    private $responseCollection;

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
        $this->responseCollection = SharedDataCollection::get(Response::class);
    }

    public function createUnassociatedSite(array $params = [])
    {
        $params = array_replace($this->defaultSiteData, $params);

        $site = $this->tryGet($params["name"]);
        if ($site !== null) {
            return $site;
        }

        $user = $this->userData->createAreaOffice1User();

        $response = $this->vts->create($user->getAccessToken(), $params);
        $responseBody = $response->getBody();
        if (!is_object($responseBody)) {
            $this->responseCollection->add($responseBody, $params["name"]);
            throw new \Exception("createSite: responseBody is not an object: failed to create Vts");
        }

        $site = $responseBody->toArray()['data'];
        $dto = new SiteDto();
        $dto
            ->setId($site["id"])
            ->setSiteNumber($site["siteNumber"])
            ->setName($params["name"]);

        $this->siteCollection->add($dto, $params["name"]);

        return $dto;
    }

    public function create(array $params = [])
    {
        $default = array_merge(
            $this->defaultSiteData,
            [
                "aeName" => AuthorisedExaminerData::DEFAULT_NAME,
                "startDate" => null,
                "endDate" => null
            ]
        );

        $data = array_replace($default, $params);

        $site = $this->tryGet($data["name"]);
        if ($site !== null) {
            return $site;
        }

        $site = $this->createUnassociatedSite($data);
        $ae = $this->authorisedExaminerData->create(1001, $data["aeName"]);

        $site->setOrganisation($ae);

        $this->authorisedExaminerData->linkAuthorisedExaminerWithSite($ae, $site);

        if ($data["startDate"] || $data["endDate"]) {
            $this->changeAssociatedDate($ae->getId(), $site->getId(), $data["startDate"], $data["endDate"]);
        }

        $this->siteCollection->add($site, $data["name"]);

        return $site;
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

    /**
     * @param string $siteName
     * @return SiteDto
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

    public function getOrCreate($siteName = self::DEFAULT_NAME)
    {
        $site = $this->tryGet($siteName);
        if ($site === null) {
            $site = $this->create(["siteName" => $siteName]);
        }

        return $site;
    }

    public function getAll()
    {
        return $this->siteCollection;
    }

    /**
     * @return Response
     */
    public function getLastResponse()
    {
        return $this->responseCollection->last();
    }

    /**
     * @param $siteName
     * @return Response
     */
    public function getResponse($siteName)
    {
        return $this->responseCollection->get($siteName);
    }
}
