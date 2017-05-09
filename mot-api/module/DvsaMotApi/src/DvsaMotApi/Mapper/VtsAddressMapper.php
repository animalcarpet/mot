<?php

namespace DvsaMotApi\Mapper;

/**
 * Used to map Vts address to different other objects
 * Class VtsAddressMapper.
 */
class VtsAddressMapper
{
    /**
     * Maps VTS address to string that is compliant with old string representation of the VTS address.
     *
     * @param $address
     *
     * @return null|string
     */
    public static function mapToVtsTitleString($address)
    {
        if (is_null($address)) {
            return null;
        }
        $elems = [$address->getAddressLine1(), $address->getPostcode(), $address->getTown()];

        return implode(
            ', ', array_filter(
                $elems,
                function ($_) {
                    return !is_null($_);
                }
            )
        );
    }
}
