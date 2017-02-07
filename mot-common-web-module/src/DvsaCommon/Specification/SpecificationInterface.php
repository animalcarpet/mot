<?php

namespace DvsaCommon\Specification;


interface SpecificationInterface
{

    /**
     * Verify if given candidate object fulfills the specification
     *
     * @param mixed $candidate
     * @return bool
     */
    public function isSatisfiedBy($candidate);
}