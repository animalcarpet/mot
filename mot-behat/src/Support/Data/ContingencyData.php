<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\ContingencyTest;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use DvsaCommon\Dto\MotTesting\ContingencyTestDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Utility\ArrayUtils;

class ContingencyData
{
    const CONTINGENCY_CODE = "12345A";

    private $userData;
    private $siteData;
    private $contingencyTest;

    private $contingencyCollection;
    private $emergencyLogs = [];

    public function __construct(
        UserData $userData,
        SiteData $siteData,
        ContingencyTest $contingencyTest
    )
    {
        $this->userData = $userData;
        $this->siteData = $siteData;
        $this->contingencyTest = $contingencyTest;
        $this->contingencyCollection = SharedDataCollection::get(ContingencyTestDto::class);
    }

    public function create(AuthenticatedUser $user, array $params = [])
    {
        $contingencyCode = ArrayUtils::tryGet($params, "contingencyCode", self::CONTINGENCY_CODE);
        $reasonCode = ArrayUtils::tryGet($params, "reasonCode", "SO");
        $dateTime = ArrayUtils::tryGet($params, "dateTime");
        $siteName = ArrayUtils::tryGet($params, SiteParams::SITE_NAME, SiteData::DEFAULT_NAME);

        if ($this->contingencyCollection->containsKey($siteName) === true) {
            return $this->contingencyCollection->get($siteName);
        }

        $site = $this->siteData->create($siteName);

        $response = $this->contingencyTest->getContingencyCodeID(
            $user->getAccessToken(),
            $contingencyCode,
            $reasonCode,
            $dateTime,
            $site->getId()
        );

        $dto = new ContingencyTestDto();
        $dto
            ->setContingencyCode($contingencyCode)
            ->setReasonCode($reasonCode)
            ->setSiteId($site->getId());

        $this->contingencyCollection->add($dto, $siteName);
        $this->emergencyLogs[$siteName] = $response->getBody()->getData()['emergencyLogId'];

        return $dto;
    }

    public function getContingencyCodeID(AuthenticatedUser $user, SiteDto $site, array $params = [])
    {
        $params[SiteParams::SITE_NAME] = $site->getName();
        return $this->create($user, $params);
    }

    public function getEmergencyLogId($siteName = SiteData::DEFAULT_NAME)
    {
        if (array_key_exists($siteName, $this->emergencyLogs) === false) {
            throw new \InvalidArgumentException(sprintf("Emergency log for site '%s' not found", $siteName));
        }

        return $this->emergencyLogs[$siteName];
    }

    /**
     * @param int $siteId
     * @return ContingencyTestDto
     */
    public function getBySiteId($siteId)
    {
        $collection = $this->contingencyCollection->filter(function (ContingencyTestDto $dto) use ($siteId) {
            return $dto->getSiteId() === $siteId;
        });

        return $collection->first();
    }

    public function getAll()
    {
        return $this->contingencyCollection;
    }
}
