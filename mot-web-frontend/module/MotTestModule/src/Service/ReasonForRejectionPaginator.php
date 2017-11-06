<?php
namespace Dvsa\Mot\Frontend\MotTestModule\Service;

use Application\Paginator\PaginatorInterface;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\DefectCollection;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionResponseInterface;

class ReasonForRejectionPaginator implements AutoWireableInterface, PaginatorInterface
{
    private $response;
    private $pagesInRange;
    private $lastPage;

    public function __construct(SearchReasonForRejectionResponseInterface $response, int $pagesInRange = PaginatorInterface::PAGES_IN_RANGE)
    {
        $this->response = $response;
        $this->pagesInRange = $pagesInRange;
        $this->lastPage = (int) ceil($this->response->getTotalCount()/$this->response->getItemPerPage());
    }

    public function getTotalItemCount(): int
    {
        return $this->response->getTotalCount();
    }

    public function getItems(): array
    {
        $data = $this->response->getData();
        return DefectCollection::fromSearchResult($data)->getDefects();
    }

    public function getPage(): int
    {
        return $this->response->getPage();
    }

    public function getPagesInRange(): array
    {
        $currentPage = $this->getPage();

        $pagesInRange = [$currentPage];
        $prevPage = $currentPage - 1;
        $nextPage = $currentPage + 1;
        $stop = false;

        do {
            $isOutOfLeftBound = ($prevPage < 1);
            $isOutOfRightBound = ($nextPage > $this->lastPage);

            if ($isOutOfLeftBound === false && count($pagesInRange) < $this->pagesInRange) {
                $pagesInRange[] = $prevPage;
            }

            if ($isOutOfRightBound === false && count($pagesInRange) < $this->pagesInRange) {
                $pagesInRange[] = $nextPage;
            }

            if (($isOutOfLeftBound && $isOutOfRightBound) || count($pagesInRange) >= $this->pagesInRange) {
                $stop = true;
            }

            $prevPage--;
            $nextPage++;

        } while($stop === false);

        sort($pagesInRange);
        return $pagesInRange;
    }

    public function hasPreviousPage(): bool
    {
        return ($this->response->getPage() > 1);
    }

    public function hasNextPage(): bool
    {
        return ($this->lastPage > $this->response->getPage());
    }

    public function getFirstItemNumber(): int
    {
        return (($this->response->getPage() - 1) * $this->response->getItemPerPage()) + 1;
    }

    public function getLastItemNumber(): int
    {
        if ($this->isLastPage()) {
            $lastItemNumber = $this->getTotalItemCount();
        } else {
            $lastItemNumber = $this->response->getPage() * $this->response->getItemPerPage();
        }

        return $lastItemNumber;
    }

    private function isLastPage()
    {
        return ($this->lastPage === $this->getPage());
    }
}
