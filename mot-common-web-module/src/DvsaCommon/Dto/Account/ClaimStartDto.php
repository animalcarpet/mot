<?php

namespace DvsaCommon\Dto\Account;

use DvsaCommon\Dto\AbstractDataTransferObject;

/**
 * Account claim init data.
 */
class ClaimStartDto extends AbstractDataTransferObject
{

    /**
     * @var int 6 digit pin generated for account
     */
    private $pin;


    /**
     * @return int
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * @param int $pin
     * @return $this
     */
    public function setPin($pin)
    {
        $this->pin = $pin;
        return $this;
    }
}
