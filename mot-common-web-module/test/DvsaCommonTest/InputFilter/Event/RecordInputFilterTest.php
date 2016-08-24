<?php

namespace ApplicationTest\InputFilter\Event;

use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Factory\InputFilter\Event\RecordInputFilterFactory;
use DvsaCommon\InputFilter\Event\RecordInputFilter;
use DvsaCommonTest\Bootstrap;

class RecordInputFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var RecordInputFilter */
    private $subject;

    public function setUp()
    {
        $factory = new RecordInputFilterFactory();
        $this->subject = $factory->createService(Bootstrap::getServiceManager());
    }

    public function testInputFilterFactory()
    {
        $this->assertInstanceOf(
            RecordInputFilter::class,
            $this->subject
        );
    }

    public function testValidators()
    {
        $this->subject->setData($this->getGoodData());

        $this->assertSame(true, $this->subject->isValid());

        $errorMessages = $this->subject->getMessages();
        $this->assertEmpty($errorMessages);
    }

    public function getGoodData()
    {
        return [
            RecordInputFilter::FIELD_TYPE => EventTypeCode::APPEAL_AGAINST_DISCIPLINARY_ACTION,
            RecordInputFilter::FIELD_DATE => [
                RecordInputFilter::FIELD_DAY => 2,
                RecordInputFilter::FIELD_MONTH => 2,
                RecordInputFilter::FIELD_YEAR => 2015,
            ]
        ];
    }
}
