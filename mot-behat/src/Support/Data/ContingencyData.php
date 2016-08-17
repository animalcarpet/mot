<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\ContingencyTest;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use DvsaCommon\Dto\MotTesting\ContingencyTestDto;
use DvsaCommon\Utility\ArrayUtils;

class ContingencyData
{
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
        $this->contingencyCollection = new DataCollection(ContingencyTestDto::class);
    }

    public function create(AuthenticatedUser $user, array $params = [])
    {
        $contingencyCode = ArrayUtils::tryGet($params, "contingencyCode", "12345A");
        $reasonCode = ArrayUtils::tryGet($params, "reasonCode", "SO");
        $dateTime = ArrayUtils::tryGet($params, "dateTime");
        $siteName = ArrayUtils::tryGet($params, "siteName", SiteData::DEFAULT_NAME);

        if ($this->contingencyCollection->containsKey($siteName) === true) {
            return $this->contingencyCollection->get($siteName);
        }

        $site = $this->siteData->create(["name" => $siteName]);

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
        $this->emergencyLogs[$siteName] = $response->getBody()['data']['emergencyLogId'];

        return $dto;
    }

    public function getEmergencyLogId($siteName = SiteData::DEFAULT_NAME)
    {
        if (array_key_exists($siteName, $this->emergencyLogs) === false) {
            throw new \InvalidArgumentException(sprintf("Emergency log for site '%s' not found", $siteName));
        }

        return $this->emergencyLogs[$siteName];
    }
}
