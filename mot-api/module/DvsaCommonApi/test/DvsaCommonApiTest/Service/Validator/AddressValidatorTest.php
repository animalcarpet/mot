<?php

namespace DvsaCommonApiTest\Service\Validator;

use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonApi\Service\Validator\AddressValidator;

/**
 * I'm building my professional career on comments.
 */
class AddressValidatorTest extends AbstractServiceTestCase
{
    public function testValidatePassThrough()
    {
        $input = [
            'addressLine1' => 'addressLine1',
            'town' => 'town',
            'postcode' => 'postcode',
        ];

        $this->createAddressValidator()->validate($input);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function testValidateMissingRequiredFieldsShouldThrowException()
    {
        $this->createAddressValidator()->validate([]);
    }

    /**
     * @return AddressValidator
     */
    private function createAddressValidator()
    {
        return new AddressValidator();
    }
}
