<?php

namespace DvsaCommonApiTest\Service;

/*
 * This is a direct copy-paste of IsEqual but it looks only at the passed-in object's timestamp instead of the whole
 * thing.
 */

use PHPUnit_Framework_ComparisonFailure;
use PHPUnit_Framework_Constraint;
use PHPUnit_Framework_Comparator_Numeric;
use PHPUnit_Framework_Comparator_Scalar;
use PHPUnit_Framework_ExpectationFailedException;
use PHPUnit_Util_InvalidArgumentHelper;
use PHPUnit_Util_Type;

/**
 * Class DateIsEqual.
 */
class DateIsEqual extends PHPUnit_Framework_Constraint
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var float
     */
    protected $delta = 0;

    /**
     * @var int
     */
    protected $maxDepth = 10;

    /**
     * @var bool
     */
    protected $canonicalize = false;

    /**
     * @var bool
     */
    protected $ignoreCase = false;

    /**
     * @var PHPUnit_Framework_ComparisonFailure
     */
    protected $lastFailure;

    /**
     * @param mixed $value
     * @param float $delta
     * @param int   $maxDepth
     * @param bool  $canonicalize
     * @param bool  $ignoreCase
     */
    public function __construct($value, $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        if (!is_numeric($delta)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'numeric');
        }

        if (!is_int($maxDepth)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(3, 'integer');
        }

        if (!is_bool($canonicalize)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(4, 'boolean');
        }

        if (!is_bool($ignoreCase)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(5, 'boolean');
        }

        $this->value = $value;
        $this->delta = $delta;
        $this->maxDepth = $maxDepth;
        $this->canonicalize = $canonicalize;
        $this->ignoreCase = $ignoreCase;
    }

    /**
     * Evaluates the constraint for parameter $other.
     *
     * If $returnResult is set to FALSE (the default), an exception is thrown
     * in case of a failure. NULL is returned otherwise.
     *
     * If $returnResult is TRUE, the result of the evaluation is returned as
     * a boolean value instead: TRUE in case of success, FALSE in case of a
     * failure.
     *
     * @param mixed  $other        Value or object to evaluate
     * @param string $description  Additional information about the test
     * @param bool   $returnResult Whether to return a result or throw an exception
     *
     * @return mixed
     *
     * @throws PHPUnit_Framework_ExpectationFailedException
     */
    public function evaluate($other, $description = '', $returnResult = false)
    {
        try {
            //  --  check values has the same type  --
            $comparator = new \PHPUnit_Framework_Comparator_Type();
            $comparator->assertEquals($other, $this->value);

            //  --  check values are the same   --
            if ($this->value !== null && $other !== null) {
                $comparator = new PHPUnit_Framework_Comparator_Numeric();

                $comparator->assertEquals(
                    $this->value->getTimestamp(),
                    $other->getTimestamp(),
                    $this->delta,
                    $this->canonicalize,
                    $this->ignoreCase
                );

                $tzComparator = new PHPUnit_Framework_Comparator_Scalar();

                $tzComparator->assertEquals(
                    $this->value->getTimezone()->getName(),
                    $other->getTimezone()->getName(),
                    0,
                    $this->canonicalize,
                    $this->ignoreCase
                );
            } else {
                $comparator = new \PHPUnit_Framework_Comparator_Scalar();
                $comparator->assertEquals(
                    $this->value,
                    $other,
                    0,
                    $this->canonicalize,
                    $this->ignoreCase
                );
            }
        } catch (PHPUnit_Framework_ComparisonFailure $f) {
            if ($returnResult) {
                return false;
            }

            throw new PHPUnit_Framework_ExpectationFailedException(
                trim($description."\n".$f->getMessage()),
                $f
            );
        }

        return true;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        $delta = '';

        if (is_string($this->value)) {
            if (strpos($this->value, "\n") !== false) {
                return 'is equal to <text>';
            } else {
                return sprintf(
                    'is equal to <string:%s>',
                    $this->value
                );
            }
        } else {
            if ($this->delta != 0) {
                $delta = sprintf(
                    ' with delta <%F>',
                    $this->delta
                );
            }

            return sprintf(
                'is equal to %s%s',
                PHPUnit_Util_Type::export($this->value),
                $delta
            );
        }
    }
}
