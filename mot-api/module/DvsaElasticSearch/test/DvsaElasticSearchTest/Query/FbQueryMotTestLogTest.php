<?php

namespace DvsaElasticSearchTest\Query;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommonTest\TestUtils\XMock;
use DvsaElasticSearch\Query\FbQueryMotTestLog;
use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\MotTestRepository;
use \PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\DateTime;
use DvsaCommon\Dto\Search\MotTestSearchParamsDto;

/**
 * Class FbQueryMotTestLogTest
 *
 * @package DvsaElasticSearchTest\Query
 */
class FbQueryMotTestLogTest extends \PHPUnit_Framework_TestCase
{
    public function testFbQueryMotTestExecute()
    {
        $optionalMotTestTypes = [];

        $fbQueryMotTestLog = new FbQueryMotTestLog();

        //  --  mock   --
        $mockRepo = XMock::of(MotTestRepository::class, ['getMotTestLogsResultCount', 'getMotTestLogsResult']);
        $mockRepo->expects($this->once())
            ->method('getMotTestLogsResultCount')
            ->willReturn(['count' => 10]);
        $mockRepo->expects($this->once())
            ->method('getMotTestLogsResult')
            ->willReturn([]);

        $mockSearchParam = XMock::of(MotTestSearchParam::class, ['getRepository', 'toDto', 'isApiGetTotalCount', 'isApiGetData']);
        $mockSearchParam->expects($this->once())
            ->method('getRepository')
            ->willReturn($mockRepo);

        $motTestSearchParamsDto = new MotTestSearchParamsDto();
        $motTestSearchParamsDto->setFormat(SearchParamConst::FORMAT_DATA_CSV);
        $mockSearchParam->expects($this->once())
            ->method('toDto')
            ->willReturn($motTestSearchParamsDto);

        $mockSearchParam->expects($this->once())
            ->method('isApiGetTotalCount')
            ->willReturn(true);

        $mockSearchParam->expects($this->once())
            ->method('isApiGetData')
            ->willReturn(true);

        // Request and check
        $this->assertInstanceOf(SearchResultDto::class, $fbQueryMotTestLog->execute($mockSearchParam, $optionalMotTestTypes));
    }
}
