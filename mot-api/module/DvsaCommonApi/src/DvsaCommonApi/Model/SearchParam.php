<?php

namespace DvsaCommonApi\Model;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\AbstractDataTransferObject;
use DvsaCommon\Dto\Search\SearchParamsDto;
use Zend\Http\Request;

/**
 * Class SearchParam.
 */
class SearchParam
{
    const SEARCH_DATA_FORMAT_QUERY_PARAMETER = 'format';

    private static $SORT_DIRECTIONS = [
        SearchParamConst::SORT_DIRECTION_ASC => 1,
        SearchParamConst::SORT_DIRECTION_DESC => 1,
    ];

    protected $sortColumnId = 0;
    protected $sortDirection;

    protected $pageNr = 1;
    protected $rowCount = 10;
    protected $start = 0;

    protected $format = SearchParamConst::FORMAT_DATA_OBJECT;
    /** @var bool Tell to API is Es enable for this search */
    protected $isEsEnabled;

    /** @var bool Tell to API get data */
    protected $isApiGetData = true;
    /** @var bool Tell to API get total count of records */
    protected $isApiGetTotalCount = true;

    /**
     * Performs processing of the passed search string into
     * meaningful parts.
     */
    public function process()
    {
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            '' => '',
        ];
    }

    /**
     * Loads all the standard Data Tables request variables into
     * the current search params object from the passed Request object.
     *
     * @param Request $request
     *
     * @return $this
     */
    public function loadStandardDataTableValuesFromRequest($request)
    {
        $sortColumnId = (int) $request->getQuery(SearchParamConst::SORT_COLUMN_ID);
        $sortDirection = strtoupper($request->getQuery(SearchParamConst::SORT_DIRECTION));
        $rowCount = (int) $request->getQuery(SearchParamConst::ROW_COUNT, 10);
        $start = (int) $request->getQuery(SearchParamConst::START);
        $format = $request->getQuery(SearchParamConst::FORMAT);

        // validate values
        if ($rowCount == 0) {
            $rowCount = 10;
        }

        if ($format == null) {
            $format = SearchParamConst::FORMAT_DATA_OBJECT;
        }

        $this
            ->setSortColumnId($sortColumnId)
            ->setSortDirection($this->getValidSortDirectionValue($sortDirection))
            ->setRowCount($rowCount)
            ->setStart($start)
            ->setFormat($format);

        return $this;
    }

    public function getSortColumnNameDatabase()
    {
        return null;
    }

    /**
     * Remove words with invalid characters in them.
     *
     * @param $words
     *
     * @return string
     */
    protected function sanitizeWords($words)
    {
        $words = preg_replace('/([^\sa-zA-Z0-9])/', ' ', $words);
        $words = preg_replace('/\s+/', ' ', $words);

        return trim($words);
    }

    /**
     * Add a string as an array element if it is not null or empty.
     *
     * @param $parts
     * @param $string
     */
    protected function trimAndAddPart(&$parts, $string)
    {
        $string = trim($string);
        if (strlen($string)) {
            $parts[] = trim($string);
        }
    }

    /**
     * Map parameters values from Dto object.
     *
     * @param SearchParamsDto $dto
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function fromDto($dto)
    {
        if (!$dto instanceof SearchParamsDto) {
            throw new \InvalidArgumentException(
                __METHOD__.' Expects instance of SearchParamsDto, you passed '.get_class($dto)
            );
        }

        $this->setSortColumnId($dto->getSortBy());
        $this->setSortDirection($dto->getSortDirection());
        $this->setPageNr($dto->getPageNr());
        $this->setRowCount($dto->getRowsCount());
        $this->setStart($dto->getStart());
        $this->setFormat($dto->getFormat() ?: SearchParamConst::FORMAT_DATA_OBJECT);

        $this->setIsApiGetData($dto->isApiGetData());
        $this->setIsApiGetTotalCount($dto->isApiGetTotalCount());

        return $this;
    }

    /**
     * Map parameters values to Dto object.
     *
     * @param AbstractDataTransferObject $dto
     *
     * @return SearchParamsDto
     *
     * @throws \Exception
     */
    public function toDto(AbstractDataTransferObject &$dto = null)
    {
        if ($dto === null) {
            $dto = new SearchParamsDto();
        }

        $dto->setSortBy($this->getSortColumnId());
        $dto->setSortDirection($this->getSortDirection());
        $dto->setPageNr($this->getPageNr());
        $dto->setRowsCount($this->getRowCount());
        $dto->setStart($this->getStart());
        $dto->setFormat($this->getFormat());

        $dto->setIsApiGetData($this->isApiGetData());
        $dto->setIsApiGetTotalCount($this->isApiGetTotalCount());

        return $dto;
    }

    /**
     * @return int
     */
    public function getPageNr()
    {
        return max(0, $this->pageNr);
    }

    /**
     * @param int $pageNr
     *
     * @return $this
     */
    public function setPageNr($pageNr)
    {
        $this->pageNr = (int) $pageNr;

        return $this;
    }

    /**
     * @param $rowCount
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setRowCount($rowCount)
    {
        $rowCount = (int) $rowCount;

        if ($rowCount < 0) {
            throw new \Exception('Invalid row count, must be 0 or more');
        }

        $this->rowCount = $rowCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }

    /**
     * @param int $sortColumnId
     *
     * @return $this
     */
    public function setSortColumnId($sortColumnId)
    {
        $this->sortColumnId = $sortColumnId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortColumnId()
    {
        return $this->sortColumnId;
    }

    /**
     * @param $sortDirection
     *
     * @return $this
     */
    public function setSortDirection($sortDirection)
    {
        $this->sortDirection = $this->getValidSortDirectionValue($sortDirection);

        return $this;
    }

    /**
     * Utility function to help get a default value for sort direction.
     *
     * @param $direction
     *
     * @return string DESC|ASC
     */
    public function getValidSortDirectionValue($direction)
    {
        $direction = strtoupper($direction);

        if (isset(self::$SORT_DIRECTIONS[$direction])) {
            return $direction;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getSortDirection()
    {
        return $this->sortDirection;
    }

    /**
     * @param int $start
     *
     * @return $this
     */
    public function setStart($start)
    {
        $this->start = (int) $start;

        return $this;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        if ($this->start === 0 && $this->getPageNr() > 1) {
            return ($this->getPageNr() - 1) * $this->getRowCount();
        }

        return $this->start;
    }

    /**
     * @param int $format
     *
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return int
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @return bool
     */
    public function isApiGetData()
    {
        return $this->isApiGetData;
    }

    /**
     * Tell to API to get data in during request.
     *
     * @param bool $isGetData
     *
     * @return $this
     */
    public function setIsApiGetData($isGetData)
    {
        $this->isApiGetData = (bool) $isGetData;

        return $this;
    }

    /**
     * @return bool
     */
    public function isApiGetTotalCount()
    {
        return $this->isApiGetTotalCount;
    }

    /**
     * Tell to API to get total records count in during request.
     *
     * @param bool $isGetTotalCount
     *
     * @return $this
     */
    public function setIsApiGetTotalCount($isGetTotalCount)
    {
        $this->isApiGetTotalCount = (bool) $isGetTotalCount;

        return $this;
    }
}
