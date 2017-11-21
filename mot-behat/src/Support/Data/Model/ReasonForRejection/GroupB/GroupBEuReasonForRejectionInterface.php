<?php

namespace Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupB;

interface GroupBEuReasonForRejectionInterface extends GroupBReasonForRejectionInterface
{
    public function getForClass3Dangerous(): int;

    public function getForClass4Dangerous(): int;

    public function getForClass5Dangerous(): int;

    public function getForClass7Dangerous(): int;
}
