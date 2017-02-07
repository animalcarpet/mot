<?php

namespace DvsaCommon\Specification;


abstract class AbstractSpecification implements SpecificationInterface
{
    /**
     * @param SpecificationInterface[] ...$specifications
     * @return AndSpecification
     */
    public function andSpecification(SpecificationInterface ...$specifications)
    {
        return new AndSpecification($this, ...$specifications);
    }

    /**
     * @param SpecificationInterface[] ...$specifications
     * @return OrSpecification
     */
    public function orSpecification(SpecificationInterface ...$specifications)
    {
        return new OrSpecification($this, ...$specifications);
    }

    /**
     * @return NotSpecification
     */
    public function not()
    {
        return new NotSpecification($this);
    }

    /**
     * Verify if given candidate object fulfills the specification
     *
     * @param mixed $candidate
     * @return bool
     */
    abstract public function isSatisfiedBy($candidate);
}