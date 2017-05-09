<?php

namespace PersonApiTest\Input\MotTestingCertificate;

use DvsaCommon\Enum\VehicleClassGroupCode;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use PersonApi\Input\MotTestingCertificate\VehicleClassGroupCodeInput;
use PersonApiTest\Input\BaseInput;

class VehicleClassGroupCodeInputTest extends BaseInput
{
    /** @var VehicleClassGroupCodeInput */
    private $input;

    public function setUp()
    {
        $this->input = new VehicleClassGroupCodeInput();
    }

    /**
     * @dataProvider getValidData
     */
    public function testIsValidForValidData($group)
    {
        $this->input->setValue($group);
        $this->assertTrue($this->input->isValid());
    }

    public function getValidData()
    {
        return [
            [VehicleClassGroupCode::BIKES],
            [VehicleClassGroupCode::CARS_ETC],
        ];
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testIsValidForInvalidData($group, array $expectedMessages)
    {
        $this->input->setValue($group);

        $this->assertFalse($this->input->isValid());

        $this->assertEquals($expectedMessages, $this->input->getMessages());
    }

    public function getInvalidData()
    {
        return [
            [
                null,
                [
                    NotEmpty::IS_EMPTY => VehicleClassGroupCodeInput::MSG_EMPTY,
                    InArray::NOT_IN_ARRAY => VehicleClassGroupCodeInput::MSG_NOT_EXIST,
                ],
            ],
            [
                '',
                [
                    NotEmpty::IS_EMPTY => VehicleClassGroupCodeInput::MSG_EMPTY,
                    InArray::NOT_IN_ARRAY => VehicleClassGroupCodeInput::MSG_NOT_EXIST,
                ],
            ],
            [
                'C',
                [InArray::NOT_IN_ARRAY => VehicleClassGroupCodeInput::MSG_NOT_EXIST],
            ],
        ];
    }
}
