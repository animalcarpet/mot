<?php

namespace UserApiTest\Message\Service\Validator;

use UserApi\Message\Service\Validator\MessageValidator;

class MessageValidatorTest extends \PHPUnit_Framework_TestCase
{
    private $correctData = [
        'personId' => 'not relevant',
        'messageTypeCode' => 'PRL',
    ];

    public function testValidateCorrectDataDoesNotThrowException()
    {
        $this->callValidator($this->correctData);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function testValidateMissingRequiredPersonIdFieldThrowsException()
    {
        $data = $this->correctData;
        unset($data['personId']);

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function testValidateMissingRequiredMessageTypeCodeFieldThrowsException()
    {
        $data = $this->correctData;
        unset($data['messageTypeCode']);

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateInvaliddMessageTypeCodeFieldThrowsException()
    {
        $data = $this->correctData;
        $data['messageTypeCode'] = 'invalid value';

        $this->callValidator($data);
    }

    /**
     * @param array $data
     */
    private function callValidator($data)
    {
        $validator = new MessageValidator();
        $validator->validate($data);
    }
}
