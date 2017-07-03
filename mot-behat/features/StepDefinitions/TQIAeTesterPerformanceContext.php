<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Helper\ApiResourceHelper;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\SiteDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\AuthorisedExaminerSitePerformanceApiResource;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use PHPUnit_Framework_Assert as PHPUnit;

class TQIAeTesterPerformanceContext implements Context
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
