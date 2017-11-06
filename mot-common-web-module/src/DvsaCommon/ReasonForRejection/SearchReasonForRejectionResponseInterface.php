<?php

namespace DvsaCommon\ReasonForRejection;

interface SearchReasonForRejectionResponseInterface
{
    /**
     * @return \DvsaCommon\Dto\ReasonForRejection\ReasonForRejectionDto[]
     */
    public function getData(): array;

    public function getTotalCount(): int;

    public function getItemPerPage(): int;

    public function getPage(): int;
}
