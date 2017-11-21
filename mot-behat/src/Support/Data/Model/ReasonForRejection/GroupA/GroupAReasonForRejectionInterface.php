<?php

namespace Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupA;

interface GroupAReasonForRejectionInterface
{
    public function getForClass1(): int;

    public function getForClass1Advisory(): int;

    public function getForClass1Prs(): int;

    public function getForClass2(): int;

    public function getForClass2Advisory(): int;

    public function getForClass2Prs(): int;
}
