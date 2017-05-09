<?php

namespace DvsaCommonApi\Formatting;

use DvsaEntities\Entity\Address;

class AddressFormatter
{
    public function format(Address $address, $showCountry = false)
    {
        $addressArray = [
            $address->getAddressLine1(),
            $address->getAddressLine2(),
            $address->getAddressLine3(),
            $address->getAddressLine4(),
            $address->getTown(),
            $address->getPostcode(),
        ];
        if ($showCountry) {
            $addressArray[] = $address->getCountry();
        }

        return implode(', ', array_filter($addressArray));
    }
}
