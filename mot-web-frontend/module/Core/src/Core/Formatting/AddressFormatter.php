<?php

namespace Core\Formatting;

use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Formatting\Utf8Escaper;
use DvsaCommon\Utility\ArrayUtils;

class AddressFormatter
{
    protected $addressPartsGlue = ',</br>';

    public function escapedDtoToMultiLine(AddressDto $dto, $showCountry = false)
    {
        if ($showCountry) {
            return static::escapeAddressToMultiLine(
                $dto->getAddressLine1(),
                $dto->getAddressLine2(),
                $dto->getAddressLine3(),
                $dto->getAddressLine4(),
                $dto->getTown(),
                $dto->getCountry(),
                $dto->getPostcode()
            );
        } else {
            return static::escapeAddressToMultiLine(
                $dto->getAddressLine1(),
                $dto->getAddressLine2(),
                $dto->getAddressLine3(),
                $dto->getAddressLine4(),
                null,
                $dto->getTown(),
                $dto->getPostcode()
            );
        }
    }

    public function escapeAddressToMultiLine(
        $addressLine1 = null,
        $addressLine2 = null,
        $addressLine3 = null,
        $addressLine4 = null,
        $country = null,
        $town = null,
        $postcode = null
    )
    {
        $escaper = new Utf8Escaper();

        $lines = [
            $escaper->escapeHtml($addressLine1),
            $escaper->escapeHtml($addressLine2),
            $escaper->escapeHtml($addressLine3),
            $escaper->escapeHtml($addressLine4),
            $escaper->escapeHtml($country),
            $escaper->escapeHtml($town),
            $escaper->escapeHtml($postcode),
        ];

        return trim(ArrayUtils::joinNonEmpty($this->addressPartsGlue, $lines));
    }

    /**
     * @param string $addressPartsGlue
     * @return AddressFormatter
     *
     * @deprecated This is hax, add a method that uses a specific glue
     */
    public function setAddressPartsGlue($addressPartsGlue)
    {
        $this->addressPartsGlue = $addressPartsGlue;
        return $this;
    }
}
