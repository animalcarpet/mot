<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Helper\ApiResourceHelper;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\AuthorisedExaminerSitesPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\AuthorisedExaminerSitePerformanceApiResource;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use PHPUnit_Framework_Assert as PHPUnit;

class StatsAeTesterPerformanceContext implements Context
{
    private $userData;
    private $apiResourceHelper;

    public function __construct(UserData $userData, ApiResourceHelper $apiResourceHelper)
    {
        $this->userData = $userData;
        $this->apiResourceHelper = $apiResourceHelper;
    }

    /**
     * @Then being log in as an aedm in :ae I can view authorised examiner statistics with data:
     */
    public function beingLogInAsAnAedmInICanViewAuthorisedExaminerStatisticsWithData(OrganisationDto $ae, TableNode $table)
    {
        $aedm = $this->userData->getAedmByAeId($ae->getId());
        $this->userData->setCurrentLoggedUser($aedm);

        /** @var AuthorisedExaminerSitePerformanceApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(AuthorisedExaminerSitePerformanceApiResource::class);
        $stats = $apiResource->getData($ae->getId(), null, null);

        $rows = $table->getColumnsHash();
        $sites = $stats->getSites();

        foreach ($rows as $i => $row) {
            $this->assertAuthorisedExaminerSitePerformance($row, $sites);
        }
    }

    /**
     * @param array $expected
     * @param AuthorisedExaminerSitesPerformanceDto[] $actual
     */
    private function assertAuthorisedExaminerSitePerformance(array $expected, array $actual)
    {
        foreach ($actual as $site) {
            if ($expected["siteName"] === $site->getName()) {
                PHPUnit::assertEquals($expected["riskScore"], $site->getRiskAssessmentScore());
                return;
            }
        }

        throw new \InvalidArgumentException(sprintf("Site '%s' not found", $expected["siteName"]));
    }
}
