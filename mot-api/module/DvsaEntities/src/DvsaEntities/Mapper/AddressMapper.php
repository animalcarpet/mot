<?php

namespace DvsaEntities\Mapper;

use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Entity\Address;

/**
 * Address details mapper.
 */
class AddressMapper
{
    /**
     * Map data sent to MOT-API.
     *
     * @param Address $address
     * @param array   $data
     *
     * @return Address
     */
    public function mapToEntity(Address $address, array $data)
    {
        $address
            ->setTown(ArrayUtils::tryGet($data, 'town', ''))
            ->setPostcode(ArrayUtils::tryGet($data, 'postcode', ''))
            ->setAddressLine1(ArrayUtils::tryGet($data, 'addressLine1', ''))
            ->setAddressLine2(ArrayUtils::tryGet($data, 'addressLine2', ''))
            ->setAddressLine3(ArrayUtils::tryGet($data, 'addressLine3', ''))
            ->setAddressLine4(ArrayUtils::tryGet($data, 'addressLine4', ''))
            ->setCountry(ArrayUtils::tryGet($data, 'country'));

        return $address;
    }

    /**
     * @param Address $address
     *
     * @return AddressDto
     */
    public function toDto(Address $address = null)
    {
        $addressDto = new AddressDto();

        if ($address instanceof Address) {
            $addressDto
                ->setTown($address->getTown())
                ->setPostcode($address->getPostcode())
                ->setAddressLine1($address->getAddressLine1())
                ->setAddressLine2($address->getAddressLine2())
                ->setAddressLine3($address->getAddressLine3())
                ->setAddressLine4($address->getAddressLine4())
                ->setCountry($address->getCountry());
        }

        return $addressDto;
    }
}
