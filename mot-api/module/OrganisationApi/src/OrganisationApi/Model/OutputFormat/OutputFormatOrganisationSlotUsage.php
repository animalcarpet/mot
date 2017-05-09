<?php

namespace OrganisationApi\Model\OutputFormat;

use DvsaCommonApi\Model\OutputFormat;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\Site;

/**
 * Class OutputFormatOrganisationSlotUsage.
 */
class OutputFormatOrganisationSlotUsage extends OutputFormat
{
    /**
     * Responsible for extracting the current item into the required format
     * and adding to the passed results array.
     *
     * @param $results
     * @param $key
     * @param Site $item
     *
     * @return mixed
     */
    public function extractItem(&$results, $key, $item)
    {
        $usage = $item['usage'];
        $item = $item[0];

        $result = [
            'id' => $item->getId(),
            'usage' => $usage,
            'name' => $item->getName(),
            'siteNumber' => $item->getSiteNumber(),
            'location' => $this->getInlineAddress($item->getAddress()),
            'authorisedExaminerId' => $item->getOrganisation()->getId(),
        ];

        $results[$item->getId()] = $result;
    }

    /**
     * @param Address $address
     *
     * @return string
     */
    public function getInlineAddress($address)
    {
        $parts = [
            $address->getAddressLine1(),
            $address->getTown(),
            $address->getPostcode(),
        ];

        return implode(', ', $parts);
    }
}
