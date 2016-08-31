<?php

namespace DvsaCommon\PHPUnit\Constraint;

use DvsaCommon\Date\TimeSpan;

class TimeSpanEqualsConstraint extends \PHPUnit_Framework_Constraint
{
    private $expected;

    public function __construct(TimeSpan $expected)
    {
        parent::__construct();

        $this->exporter = new TimeSpanConstraintExporter();

        $this->expected = $expected;
    }

    protected function matches($other)
    {
        return $this->expected->equals($other);
    }

    public function toString()
    {
        return " matches expected " . $this->exporter->export($this->expected);
    }

}
