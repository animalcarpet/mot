<?php
namespace DvsaCommonTest\Specification\Stubs;

use DvsaCommon\Specification\AbstractSpecification;

class TrueSpecification extends AbstractSpecification
{
    /**
     * Verify if given candidate object fulfills the specification
     *
     * @param mixed $candidate
     * @return bool
     */
    public function isSatisfiedBy($candidate)
    {
        return true;
    }
}