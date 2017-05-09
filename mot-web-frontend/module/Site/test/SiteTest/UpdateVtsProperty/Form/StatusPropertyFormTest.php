<?php

namespace SiteTest\UpdateVtsProperty\Form;

use DvsaCommon\Model\VtsStatus;
use Site\UpdateVtsProperty\Process\Form\StatusPropertyForm;

class StatusPropertyFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new StatusPropertyForm();
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function validData()
    {
        $data = [];

        $statuses = VtsStatus::getStatuses();

        foreach ($statuses as $code => $name) {
            $data[] = [[StatusPropertyForm::FIELD_STATUS => $code]];
        }

        return $data;
    }

    /**
     * @dataProvider invalidData
     */
    public function testFormReturnsErrorMsgForInvalidData(array $data)
    {
        $form = new StatusPropertyForm();
        $form->setData($data);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());

        $messages = $form->getStatusElement()->getMessages();
        $this->assertCount(1, $messages);
        $this->assertEquals(StatusPropertyForm::STATUS_EMPTY_MSG, array_shift($messages));
    }

    public function invalidData()
    {
        return [
            [[StatusPropertyForm::FIELD_STATUS => '']],
            [[StatusPropertyForm::FIELD_STATUS => ' ']],
        ];
    }
}
