<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Helper\ApiResourceHelper;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\SiteDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\AuthorisedExaminerSitePerformanceApiResource;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use PHPUnit_Framework_Assert as PHPUnit;

class TQIAeTesterPerformanceContext implements Context
{
    private $userData;
    private $siteData;
    private $apiResourceHelper;

    public function __construct(UserData $userData, SiteData $siteData, ApiResourceHelper $apiResourceHelper)
    {
        $this->userData = $userData;
        $this->siteData = $siteData;
        $this->apiResourceHelper = $apiResourceHelper;
    }

    /**
     * @Then being log in as an aedm in :ae I can view authorised examiner statistics with data:
     */
    public function beingLogInAsAnAedmInICanViewAuthorisedExaminerStatisticsWithData(OrganisationDto $ae, TableNode $table)
    {
        $stats = $this->retrieveAeStats($ae);
        $sites = $stats->getSites();

        $rows = $table->getColumnsHash();

        PHPUnit::assertEquals(count($rows), count($sites), "Expected number of sites does not match actual number of sites");

        foreach ($rows as $i => $row) {
            $this->assertAuthorisedExaminerSitePerformance($row, $sites);
        }
    }

    /**
     * @Then being log in as an aedm in :ae I can view authorised examiner statistics on page :page with data:
     */
    public function beingLogInAsAnAedmInICanViewAuthorisedExaminerStatisticsOnPageWithData(OrganisationDto $ae, $page, TableNode $table)
    {
        $stats = $this->retrieveAeStats($ae, $page, 2);
        $sites = $stats->getSites();

        $rows = $table->getColumnsHash();

        PHPUnit::assertEquals(count($rows), count($sites), "Expected number of sites does not match actual number of sites");

        foreach ($rows as $i => $row) {
            $this->assertAuthorisedExaminerSitePerformance($row, $sites);
        }
    }

    private function retrieveAeStats(OrganisationDto $ae, $page = null, $itemPerPage = null)
    {
        $aedm = $this->userData->getAedmByAeId($ae->getId());
        $this->userData->setCurrentLoggedUser($aedm);

        /** @var AuthorisedExaminerSitePerformanceApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(AuthorisedExaminerSitePerformanceApiResource::class);
        return $apiResource->getData($ae->getId(), $page, $itemPerPage);
    }

    /**
     * @param array $expected
     * @param SiteDto[] $actual
     */
    private function assertAuthorisedExaminerSitePerformance(array $expected, array $actual)
    {
        foreach ($actual as $site) {
            if ($expected[SiteParams::SITE_NAME] === $site->getName()) {
                if (empty($expected["currentRiskScore"])) {
                    PHPUnit::assertNull($site->getCurrentRiskAssessment());
                } else {
                    PHPUnit::assertEquals($expected["currentRiskScore"], $site->getCurrentRiskAssessment()->getScore());
                }

                if (empty($expected["currentAssessmentDate"])) {
                    PHPUnit::assertNull($site->getCurrentRiskAssessment());
                } else {
                    PHPUnit::assertEquals(
                        (new \DateTime($expected["currentAssessmentDate"]))->format("Y-m-d"),
                        $site->getCurrentRiskAssessment()->getDate()->format("Y-m-d")
                    );
                }

                if (empty($expected["previousRiskScore"])) {
                    PHPUnit::assertNull($site->getPreviousRiskAssessment());
                } else {
                    PHPUnit::assertEquals($expected["previousRiskScore"], $site->getPreviousRiskAssessment()->getScore());
                }

                if (empty($expected["previousAssessmentDate"])) {
                    PHPUnit::assertNull($site->getPreviousRiskAssessment());
                } else {
                    PHPUnit::assertEquals(
                        (new \DateTime($expected["previousAssessmentDate"]))->format("Y-m-d"),
                        $site->getPreviousRiskAssessment()->getDate()->format("Y-m-d")
                    );
                }

                return;
            }
        }

        throw new \InvalidArgumentException(sprintf("Site '%s' not found", $expected[SiteParams::SITE_NAME]));
    }
}
