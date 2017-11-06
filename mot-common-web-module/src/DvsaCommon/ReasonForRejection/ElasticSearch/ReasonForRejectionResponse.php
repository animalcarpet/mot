<?php
namespace DvsaCommon\ReasonForRejection\ElasticSearch;

use DvsaCommon\Dto\ReasonForRejection\ReasonForRejectionDto;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionResponseInterface;
use DvsaCommon\Utility\TypeCheck;

class ReasonForRejectionResponse implements SearchReasonForRejectionResponseInterface
{
    /**
     * @var ReasonForRejectionDto[]
     */
    private $response;
    private $totalCount;
    private $itemPerPage;
    private $page;

    /**
     * ReasonForRejectionResponse constructor.
     *
     * @param ReasonForRejectionDto[] $dto
     * @param int $totalCount
     * @param int $itemPerPage
     * @param int $page
     */
    public function __construct(array $dto, int $totalCount, int $itemPerPage, int $page)
    {
        TypeCheck::assertCollectionOfClass($dto, ReasonForRejectionDto::class);

        $this->response = $dto;
        $this->totalCount = $totalCount;
        $this->itemPerPage = $itemPerPage;
        $this->page = $page;
    }

    /**
     * @return ReasonForRejectionDto[]
     */
    public function getData(): array
    {
        return $this->response;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getItemPerPage(): int
    {
        return $this->itemPerPage;
    }

    public function getPage(): int
    {
        return $this->page;
    }
}
