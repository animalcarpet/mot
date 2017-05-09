<?php

namespace DvsaEntities\DqlBuilder\SearchParam;

use DvsaCommon\Dto\Search\DemoTestRequestsSearchParamsDto;
use DvsaCommon\Dto\Search\SearchParamsDto;
use DvsaCommonApi\Model\SearchParam;

/**
 * Class DemoTestRequestsSearchParam.
 */
class DemoTestRequestsSearchParam extends SearchParam
{
    const DEFAULT_SORT_COLUMN = 'person.username';

    private static $sortCriteria = [
        DemoTestRequestsSearchParamsDto::SORT_BY_USERNAME => ['person.username'],
        DemoTestRequestsSearchParamsDto::SORT_BY_CONTACT => ['email.email'],
        DemoTestRequestsSearchParamsDto::SORT_BY_GROUP => ['vehicle_class_group.code'],
        DemoTestRequestsSearchParamsDto::SORT_BY_VTS_POSTCODE => ['address.postcode'],
        DemoTestRequestsSearchParamsDto::SORT_BY_DATE_ADDED => ['qualification_award.createdOn'],
    ];

    /**
     * @return array
     */
    public function getSortColumnNameDatabase()
    {
        $sortBy = $this->getSortColumnId();

        if (isset(self::$sortCriteria[$sortBy])) {
            return self::$sortCriteria[$sortBy];
        }

        return self::DEFAULT_SORT_COLUMN;
    }

    /**
     * @param DemoTestRequestsSearchParamsDto $dto
     *
     * @return $this
     */
    public function fromDto($dto)
    {
        if (!$dto instanceof DemoTestRequestsSearchParamsDto) {
            throw new \InvalidArgumentException(
                __METHOD__.' Expects instance of SearchParamsDto, you passed '.get_class($dto)
            );
        }

        parent::fromDto($dto);

        return $this;
    }

    /**
     * Check if at least one of the searches have been filled.
     *
     * @throws \UnexpectedValueException
     *
     * @return $this
     */
    public function process()
    {
        return $this;
    }
}
