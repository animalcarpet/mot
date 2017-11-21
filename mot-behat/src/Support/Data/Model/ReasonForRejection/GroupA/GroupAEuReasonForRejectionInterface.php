<?php

namespace Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupA;

interface GroupAEuReasonForRejectionInterface extends GroupAReasonForRejectionInterface
{
    public function getForClass1Dangerous(): int;

    public function getForClass2Dangerous(): int;
}
