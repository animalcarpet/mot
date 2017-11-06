<?php

namespace Application\Paginator;

interface PaginatorInterface
{
    const PAGES_IN_RANGE = 5;

    public function getTotalItemCount(): int;

    public function getItems(): array;

    public function getPage(): int;

    public function getPagesInRange(): array;

    public function hasPreviousPage(): bool;

    public function hasNextPage(): bool;

    public function getFirstItemNumber(): int;

    public function getLastItemNumber(): int;
}
