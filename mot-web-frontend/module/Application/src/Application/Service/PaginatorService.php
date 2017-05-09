<?php

namespace Application\Service;

class PaginatorService
{
    private $prevPage;
    private $nextPage;
    private $lastPage;
    private $itemsCount;

    public function __construct(array $items, $totalItemsCount, $currentPage, $pageSize = 20)
    {
        $lastPage = (int) ceil($totalItemsCount / $pageSize);
        $prevPage = null;
        $nextPage = null;
        if ($currentPage > 1 && $currentPage <= $lastPage) {
            $prevPage = $currentPage - 1;
        }

        if ($currentPage < $lastPage) {
            $nextPage = $currentPage + 1;
        }

        $this->prevPage = $prevPage;
        $this->nextPage = $nextPage;
        $this->lastPage = $lastPage;
        $this->itemsCount = count($items);
    }

    public function getPrevPage()
    {
        return $this->prevPage;
    }

    public function getNextPage()
    {
        return $this->nextPage;
    }

    public function getLastPage()
    {
        return $this->lastPage;
    }

    public function getItemsCount()
    {
        return $this->itemsCount;
    }
}
