<?php

namespace SiteTest\Table;

use Core\Routing\VtsRouteList;
use DvsaCommon\ApiClient\Site\Dto\GroupAssessmentListItem;
use Organisation\Presenter\UrlPresenterData;
use Site\Table\TestersAnnualAssessmentTable;

class TestersAnnualAssessmentTableTest extends \PHPUnit_Framework_TestCase
{
    const USERNAME = 'username1';
    const MIDDLE_NAME = 'middle';
    const FAMILY_NAME = 'family';
    const FIRST_NAME = 'first';
    const USER_ID = 123123;
    const VTS_ID = 1234;

    public function testTableBuilding()
    {
        $dto = $this->getRowItem();

        $tableBuilder = new TestersAnnualAssessmentTable();
        $table = $tableBuilder->getTableWithAssessments([$dto], 'A', self::VTS_ID, 'vts-tester-assessments');

        $row = $table->getData()[0];
        /** @var UrlPresenterData $link */
        $link = $row['link'];

        $this->assertSame('1 September 2002', $row['dateAwarded']);
        $this->assertSame(self::USERNAME, $row['username']);
        $this->assertSame('first middle family', $row['fullName']);
        $this->assertSame('View', $link->getValue());
        $this->assertSame(VtsRouteList::VTS_PERSON_ANNUAL_ASSESSMENT, $link->getRoot());
        $this->assertSame(self::USER_ID, $link->getParams()['id']);
        $this->assertSame(self::VTS_ID, $link->getParams()['vehicleTestingStationId']);
        $this->assertSame('vts-tester-assessments', $link->getQueryParams()['query']['backTo']);

        $dto->setDateAwarded(null);
        $table = $tableBuilder->getTableWithAssessments([$dto], 'A', self::VTS_ID, '');
        $this->assertSame('No assessment recorded', $table->getData()[0]['dateAwarded']);
    }

    /**
     * @return GroupAssessmentListItem
     */
    private function getRowItem()
    {
        return (new GroupAssessmentListItem())
            ->setUserMiddleName(self::MIDDLE_NAME)->setUserFamilyName(self::FAMILY_NAME)->setUserFirstName(self::FIRST_NAME)
            ->setUsername(self::USERNAME)->setUserId(self::USER_ID)
            ->setDateAwarded(new \DateTime('2002-09-01'));
    }
}