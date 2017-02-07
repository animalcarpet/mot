<?php

namespace DvsaCommonTest\Specification\Stubs;

use DvsaCommon\Specification\AbstractSpecification;

class FalseSpecification extends AbstractSpecification
{
    /**
     * Verify if given candidate object fulfills the specification
     *
     * @param mixed $candidate
     * @return bool
     */
    public function isSatisfiedBy($candidate)
    {
        return false;
    }
}