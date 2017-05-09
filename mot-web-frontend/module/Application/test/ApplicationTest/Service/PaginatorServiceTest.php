<?php

namespace ApplicationTest\Service;

use Application\Service\PaginatorService;

class PaginatorServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testPaginator(array $items, $totalItemsCount, $currentPage, $pageSize, array $expectedData)
    {
        $paginator = new PaginatorService($items, $totalItemsCount, $currentPage, $pageSize);

        $this->assertEquals($expectedData['prevPage'], $paginator->getPrevPage());
        $this->assertEquals($expectedData['nextPage'], $paginator->getNextPage());
        $this->assertEquals($expectedData['lastPage'], $paginator->getLastPage());
        $this->assertEquals($expectedData['itemsCount'], $paginator->getItemsCount());
    }

    public function dataProvider()
    {
        return [
            [$this->getCollection(2), 5, 1, 2, ['prevPage' => null, 'nextPage' => 2, 'lastPage' => 3, 'itemsCount' => 2]],
            [$this->getCollection(2), 5, 2, 2, ['prevPage' => 1, 'nextPage' => 3, 'lastPage' => 3, 'itemsCount' => 2]],
            [$this->getCollection(1), 5, 3, 2, ['prevPage' => 2, 'nextPage' => null, 'lastPage' => 3, 'itemsCount' => 1]],
            [$this->getCollection(0), 5, 4, 2, ['prevPage' => null, 'nextPage' => null, 'lastPage' => 3, 'itemsCount' => 0]],
        ];
    }

    private function getCollection($number)
    {
        $data = [];
        for ($i = 0; $i < $number; ++$i) {
            $data[] = ['number' => $i];
        }

        return $data;
    }
}
