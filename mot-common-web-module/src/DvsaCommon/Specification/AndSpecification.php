<?php

namespace DvsaCommon\Specification;


class AndSpecification extends AbstractSpecification
{
    /**
     * @var SpecificationInterface[]
     */
    private $specifications;

    /**
     * AndSpecification constructor.
     * @param SpecificationInterface $firstSpec
     * @param SpecificationInterface $secondSpec
     * @param SpecificationInterface[] ...$specifications
     */
    public function __construct(SpecificationInterface $firstSpec, SpecificationInterface $secondSpec, SpecificationInterface ...$specifications)
    {
        $this->specifications = array_merge([$firstSpec, $secondSpec], $specifications);
    }

    /**
     * Verify if given candidate object fulfills the specification
     *
     * @param mixed $candidate
     * @return bool
     */
    public function isSatisfiedBy($candidate)
    {
        $satisfied = [];

        foreach($this->specifications as $specification)
        {
            $satisfied[] = $specification->isSatisfiedBy($candidate);
        }

        return !in_array(false, $satisfied);
    }
}