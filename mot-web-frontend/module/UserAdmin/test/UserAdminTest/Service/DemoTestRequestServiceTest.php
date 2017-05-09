<?php

namespace UserAdminTest\Service;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Search\DemoTestRequestsSearchParamsDto;
use DvsaCommonTest\TestUtils\XMock;
use DvsaReport\Service\Csv\CsvService;
use UserAdmin\Service\DemoTestRequestService;
use Zend\Stdlib\Parameters;

class DemoTestRequestServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testSearchParamsDataProvider
     *
     * @param array $parameters
     * @param array $expectedParameters
     *
     * @throws \Exception
     */
    public function testSearchParamsAreGeneratedCorrectly(array $parameters, array $expectedParameters)
    {
        $service = new DemoTestRequestService(Xmock::of(CsvService::class));
        $parameters = new Parameters($parameters);
        $searchDto = $service->getSortParams($parameters);

        $this->assertEquals($searchDto->getSortBy(), $expectedParameters[0]);
        $this->assertEquals($searchDto->getPageNr(), $expectedParameters[1]);
        $this->assertEquals($searchDto->getSortDirection(), $expectedParameters[2]);
    }

    /**
     * @dataProvider testSearchParamsDataProvider
     *
     * @param array $parameters
     * @param array $expectedParameters
     *
     * @throws \Exception
     */
    public function testSearchParamsForCsvDownloadAreGeneratedCorrectly(array $parameters, array $expectedParameters)
    {
        $service = new DemoTestRequestService(Xmock::of(CsvService::class));
        $parameters = new Parameters($parameters);
        $searchDto = $service->getSortParamsForCsv($parameters);

        $this->assertEquals($searchDto->getSortBy(), $expectedParameters[0]);
        $this->assertEquals($searchDto->getPageNr(), null);
        $this->assertEquals($searchDto->getRowsCount(), null);
        $this->assertEquals($searchDto->getSortDirection(), $expectedParameters[2]);
    }

    public function testSearchParamsDataProvider()
    {
        return [
            [
                [
                    SearchParamConst::SORT_BY => 'adsasd',
                    SearchParamConst::PAGE_NR => 'rtyurtyu',
                    SearchParamConst::SORT_DIRECTION => 'adsasd',
                ],
                [
                    DemoTestRequestService::DEFAULT_SORT_BY,
                    1,
                    DemoTestRequestService::DEFAULT_SORT_DIRECTION,
                ],
            ],
            [
                [
                    SearchParamConst::SORT_BY => DemoTestRequestsSearchParamsDto::SORT_BY_USERNAME,
                    SearchParamConst::PAGE_NR => '20',
                    SearchParamConst::SORT_DIRECTION => SearchParamConst::SORT_DIRECTION_DESC,
                ],
                [
                    DemoTestRequestsSearchParamsDto::SORT_BY_USERNAME,
                    20,
                    SearchParamConst::SORT_DIRECTION_DESC,
                ],
            ],
            [
                [
                    SearchParamConst::SORT_BY => 'somethingThatDoesNotExist',
                    SearchParamConst::PAGE_NR => -1,
                    SearchParamConst::SORT_DIRECTION => 'CSSD',
                ],
                [
                    DemoTestRequestService::DEFAULT_SORT_BY,
                    1,
                    DemoTestRequestService::DEFAULT_SORT_DIRECTION,
                ],
            ],
        ];
    }
}
