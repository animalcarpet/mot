<?php


namespace Site\Table;


use Core\Routing\VtsRouteList;
use DvsaCommon\ApiClient\Site\Dto\GroupAssessmentListItem;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Dto\Search\SearchParamsDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Formatting\PersonFullNameFormatter;
use Organisation\Presenter\UrlPresenterData;
use Report\Table\Formatter\SubRow;
use Report\Table\Formatter\UrlPresenterLinkWithParams;
use Report\Table\Table;

class TestersAnnualAssessmentTable implements AutoWireableInterface
{
    const FIELD_FULL_NAME = "fullName";
    const FIELD_USERNAME = "username";
    const FIELD_DATE_AWARDED = "dateAwarded";
    const NUMERIC_CLASS = 'numeric';

    private $personFullNameFormatter;

    const FIELD_VIEW_LINK = 'link';

    public function __construct()
    {
        $this->personFullNameFormatter = new PersonFullNameFormatter();
    }

    /**
     * @param GroupAssessmentListItem[] $assessments
     * @param string $groupName
     * @param int $siteId
     * @return Table
     */
    public function getTableWithAssessments($assessments, $groupName, $siteId)
    {
        $table = new Table();
        $rows = [];

        foreach ($assessments as $testerAnnualAssessmentRow) {
            $date = DateTimeDisplayFormat::date($testerAnnualAssessmentRow->getDateAwarded());
            $rows[] = [
                self::FIELD_FULL_NAME => $this->personFullNameFormatter->format(
                    $testerAnnualAssessmentRow->getUserFirstName(),
                    $testerAnnualAssessmentRow->getUserMiddleName(),
                    $testerAnnualAssessmentRow->getUserFamilyName()
                ),
                self::FIELD_USERNAME => $testerAnnualAssessmentRow->getUsername(),
                self::FIELD_DATE_AWARDED => $date ? $date : "No assessment recorded",
                self::FIELD_VIEW_LINK => $this->generateUrlToPersonAssessmentView($siteId, $testerAnnualAssessmentRow),
            ];
        }

        $table->setData($rows)->setColumns($this->getTableColumns());
        $table->setSearchParams(new SearchParamsDto());
        $table->getTableOptions()->setTableId('certificate-table-group-' . $groupName);

        return $table;
    }

    /**
     * @return array
     */
    private function getTableColumns()
    {
        return [
            [
                'title' => "Tester",
                'sub' => [
                    [
                        'field' => self::FIELD_FULL_NAME,
                    ],
                    [
                        'field' => self::FIELD_USERNAME,
                        'formatter' => SubRow::class,
                    ],
                ],
            ],
            [
                'title' => "Date awarded",
                'thClass' => self::NUMERIC_CLASS,
                'tdClass' => self::NUMERIC_CLASS,
                'sub' => [
                    [
                        'field' => self::FIELD_DATE_AWARDED,
                    ],
                ],
            ],
            [
                'title' => "",
                'thClass' => self::NUMERIC_CLASS,
                'tdClass' => self::NUMERIC_CLASS,
                'sub' => [
                    [
                        'field' => self::FIELD_VIEW_LINK,
                        'formatter' => UrlPresenterLinkWithParams::class,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $siteId
     * @param GroupAssessmentListItem $testerAnnualAssessmentRow
     * @return UrlPresenterData
     */
    private function generateUrlToPersonAssessmentView($siteId, GroupAssessmentListItem $testerAnnualAssessmentRow)
    {
        return new UrlPresenterData(
            'View',
            VtsRouteList::VTS_PERSON_ANNUAL_ASSESSMENT,
            [
                'vehicleTestingStationId' => $siteId,
                'id' => $testerAnnualAssessmentRow->getUserId(),
            ],
            ['query' =>
                ['backTo' => 'vts-tester-assessments']
            ],
            'view-' . $testerAnnualAssessmentRow->getUserId()
        );
    }

}