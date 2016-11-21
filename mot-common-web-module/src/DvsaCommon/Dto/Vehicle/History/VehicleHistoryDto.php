<?php

namespace DvsaCommon\Dto\Vehicle\History;

use DvsaCommon\Enum\MotTestStatusName;

class VehicleHistoryDto
{
    /** @var \ArrayObject $historyItems */
    private $historyItems;
    /** @var \ArrayObject $historyItemsOnOtherSite */
    private $historyItemsOnOtherSite;

    public function __construct()
    {
        $this->historyItems = new \ArrayObject();
        $this->historyItemsOnOtherSite = new \ArrayObject();
    }

    /**
     * @return bool
     */
    public function hasHistory()
    {
        return $this->historyItems->count() > 0;
    }

    /**
     * @return bool
     */
    public function hasHistoryOnOtherSite()
    {
        return $this->historyItemsOnOtherSite->count() > 0;
    }

    /**
     * @return \ArrayIterator|VehicleHistoryItemDto[]
     */
    public function getIterator()
    {
        return $this->historyItems->getIterator();
    }

    /**
     * @return \ArrayIterator|VehicleHistoryItemDto[]
     */
    public function getIteratorOnOtherSite()
    {
        return $this->historyItemsOnOtherSite->getIterator();
    }

    /**
     * @param VehicleHistoryItemDto $itemDto
     */
    public function addItem(VehicleHistoryItemDto $itemDto)
    {
        $this->historyItems->append($itemDto);
    }

    /**
     * @param VehicleHistoryItemDto $itemDto
     */
    public function addItemToOtherSite(VehicleHistoryItemDto $itemDto)
    {
        $this->historyItemsOnOtherSite->append($itemDto);
    }

    /**
    * @return bool
    */
    public function isMostRecentNonAbandoned(VehicleHistoryItemDto $vhi)
    {
        if ($vhi->getStatus() === MotTestStatusName::ABANDONED) {
            return false;
        }
        $vhiDateTime = new \DateTime($vhi->getIssuedDate());

        foreach ($this->historyItems as $aHistoryItem) {
            if ($aHistoryItem->getStatus() === MotTestStatusName::ABANDONED) {
                continue;
            }
            $compareableDateTime = new \DateTime($aHistoryItem->getIssuedDate());
            if ($compareableDateTime->getTimestamp() > $vhiDateTime->getTimestamp()) {
                return false;
            }
        }
        return true;
    }
}
