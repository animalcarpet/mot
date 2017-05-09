<?php

namespace OrganisationTest\UpdateAeProperty\Form;

use DvsaClient\Mapper\OrganisationMapper;
use DvsaCommonTest\TestUtils\XMock;
use Organisation\UpdateAeProperty\Process\Form\AreaOfficePropertyForm;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class AreaOfficePropertyFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrganisationMapper|MockObj
     */
    private $organisationMapper;

    protected function setUp()
    {
        $this->organisationMapper = XMock::of(OrganisationMapper::class, []);
        $this->organisationMapper->expects($this->any())
            ->method('getAllAreaOffices')
            ->willReturn($this->fakedAreaOfficeList());
    }

    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new AreaOfficePropertyForm($this->organisationMapper->getAllAreaOffices(true));
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    /**
     * @dataProvider invalidData
     */
    public function testFormReturnsErrorMsgForInvalidData(array $data)
    {
        $form = new AreaOfficePropertyForm($this->organisationMapper->getAllAreaOffices(true));
        $form->setData($data);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());

        $messages = $form->getAreaOfficeElement()->getMessages();
        $this->assertGreaterThanOrEqual(1, count($messages));
        $this->assertEquals(AreaOfficePropertyForm::STATUS_EMPTY_MSG, array_shift($messages));
    }

    public function invalidData()
    {
        return [
            [[AreaOfficePropertyForm::FIELD_AREA_OFFICE => '']],
            [[AreaOfficePropertyForm::FIELD_AREA_OFFICE => ' ']],
        ];
    }

    public function validData()
    {
        return [
            [[AreaOfficePropertyForm::FIELD_AREA_OFFICE => '01']],
            [[AreaOfficePropertyForm::FIELD_AREA_OFFICE => '02']],
            [[AreaOfficePropertyForm::FIELD_AREA_OFFICE => '03']],
        ];
    }

    protected function fakedAreaOfficeList()
    {
        return [
                1 => '01',
                2 => '02',
                3 => '03',
        ];
    }
}
