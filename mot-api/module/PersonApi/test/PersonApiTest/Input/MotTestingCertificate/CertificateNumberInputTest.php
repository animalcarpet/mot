<?php

namespace PersonApiTest\Input\MotTestingCertificate;

use Zend\Validator\NotEmpty;
use PersonApi\Input\MotTestingCertificate\CertificateNumberInput;
use PersonApiTest\Input\BaseInput;
use Zend\Validator\StringLength;

class CertificateNumberInputTest extends BaseInput
{
    /** @var CertificateNumberInput */
    private $input;

    protected function setUp()
    {
        $this->input = new CertificateNumberInput();
    }

    /**
     * @dataProvider getValidData
     *
     * @param $value
     */
    public function testValidData($value)
    {
        $this->input->setValue($value);

        $this->assertTrue($this->input->isValid());
    }

    public function getValidData()
    {
        return [
            ['numCert1223'],
            [$this->createString(CertificateNumberInput::MAX_LENGTH)],
        ];
    }

    /**
     * @dataProvider getInvalidData
     *
     * @param $value
     * @param $expectedMessages
     */
    public function testInvalidData($value, $expectedMessages)
    {
        $this->input->setValue($value);

        $this->assertFalse($this->input->isValid());

        $messages = $this->input->getMessages();
        $this->assertCount(count($expectedMessages), $messages);
        $this->assertEquals($expectedMessages, $messages);
    }

    public function getInvalidData()
    {
        return [
            [
                '',
                [NotEmpty::IS_EMPTY => CertificateNumberInput::MSG_EMPTY],
            ],
            [
                ' ',
                [NotEmpty::IS_EMPTY => CertificateNumberInput::MSG_EMPTY],
            ],
            [
                $this->createString(CertificateNumberInput::MAX_LENGTH, ' '),
                [NotEmpty::IS_EMPTY => CertificateNumberInput::MSG_EMPTY],
            ],
            [
                $this->createString(CertificateNumberInput::MAX_LENGTH + 1, ' '),
                [
                    NotEmpty::IS_EMPTY => CertificateNumberInput::MSG_EMPTY,
                    StringLength::TOO_LONG => $this->tooLongMsg(CertificateNumberInput::MSG_TOO_LONG, CertificateNumberInput::MAX_LENGTH),
                ],
            ],
            [
                $this->createString(CertificateNumberInput::MAX_LENGTH + 1),
                [StringLength::TOO_LONG => $this->tooLongMsg(CertificateNumberInput::MSG_TOO_LONG, CertificateNumberInput::MAX_LENGTH)],
            ],

        ];
    }
}
