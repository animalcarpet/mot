<?php

namespace DvsaElasticSearchTest\Model;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Search\SearchParamsDto;
use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaElasticSearch\Model\ESDocMotTestLog;

/**
 * Class ESDocMotTestLogTest.
 */
class ESDocMotTestLogTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    const INVALID_FORMAT = 'invalid-Format';

    /**
     * @var ESDocMotTestLog
     */
    protected $docMotTestLog;
    /**
     * @var SearchResultDto
     */
    protected $searchResultDto;

    public function setUp()
    {
        $this->searchResultDto = new SearchResultDto();

        $this->docMotTestLog = new ESDocMotTestLog();
    }

    /**
     * @dataProvider dataProviderTest
     */
    public function test(SearchParamsDto $searchParams, $isES, $expect)
    {
        $this->searchResultDto
            ->setIsElasticSearch($isES)
            ->setSearched($searchParams);

        if (!empty($expect['exception'])) {
            $this->setExpectedException(BadRequestException::class, $expect['exception']['message']);
        }

        $actual = $this->docMotTestLog->asJson($this->searchResultDto);

        if (!empty($expect['isResult'])) {
            $this->assertSame([], $actual);
        }
    }

    public function dataProviderTest()
    {
        return [
            [
                'searchParams' => (new SearchParamsDto())
                    ->setFormat(SearchParamConst::FORMAT_DATA_CSV),
                'isES' => false,
                'expect' => [
                    'isResult' => true,
                ],
            ],
            [
                'searchParams' => (new SearchParamsDto())
                    ->setFormat(SearchParamConst::FORMAT_DATA_TABLES),
                'isES' => true,
                'expect' => [
                    'isResult' => true,
                ],
            ],
            [
                'searchParams' => (new SearchParamsDto())
                    ->setFormat(self::INVALID_FORMAT),
                'isES' => false,
                'expect' => [
                    'exception' => [
                        'message' => 'Unknown search format: '.self::INVALID_FORMAT,
                    ],
                ],
            ],
        ];
    }
}
