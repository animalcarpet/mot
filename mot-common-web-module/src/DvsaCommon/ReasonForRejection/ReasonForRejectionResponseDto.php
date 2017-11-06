<?php

namespace DvsaCommon\ReasonForRejection;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class ReasonForRejectionResponseDto implements SearchReasonForRejectionResponseInterface, ReflectiveDtoInterface
{
    /** @var \DvsaCommon\Dto\ReasonForRejection\ReasonForRejectionDto[] */
    private $data;
    private $totalCount;
    private $itemPerPage;
    private $page;

    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param \DvsaCommon\Dto\ReasonForRejection\ReasonForRejectionDto[] $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function setTotalCount(int $totalCount)
    {
        $this->totalCount = $totalCount;
        return $this;
    }

    public function getItemPerPage(): int
    {
        return $this->itemPerPage;
    }

    public function setItemPerPage(int $itemPerPage)
    {
        $this->itemPerPage = $itemPerPage;
        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page)
    {
        $this->page = $page;
        return $this;
    }
}
