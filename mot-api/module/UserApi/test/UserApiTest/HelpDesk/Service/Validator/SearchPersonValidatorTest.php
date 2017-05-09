<?php

namespace UserApiTest\HelpDesk\Service\Validator;

use DvsaCommon\Model\SearchPersonModel;
use DvsaCommon\Validator\EmailAddressValidator;
use UserApi\HelpDesk\Service\Validator\SearchPersonValidator;

/**
 * Class PersonalDetailsValidatorTest.
 */
class SearchPersonValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testUsernameNotEmpty()
    {
        $model = new SearchPersonModel('username', null, null, null, null, null, null);
        (new SearchPersonValidator())->validate($model);
    }

    public function testFirstNameNotEmpty()
    {
        $model = new SearchPersonModel(null, 'First name', null, null, null, null, null);
        (new SearchPersonValidator())->validate($model);
    }

    public function testLastNameNotEmpty()
    {
        $model = new SearchPersonModel(null, null, 'Last name', null, null, null, null);
        (new SearchPersonValidator())->validate($model);
    }

    public function testDateOfBirthNotEmpty()
    {
        $model = new SearchPersonModel(null, null, null, '1980-10-10', null, null, null);
        (new SearchPersonValidator())->validate($model);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateIncorrectDateOfBirthThrowsException()
    {
        $model = new SearchPersonModel(null, null, null, '2000-03-1x', null, null, null);
        (new SearchPersonValidator())->validate($model);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateIncorrectDateOfBirthInTheFutureThrowsException()
    {
        $model = new SearchPersonModel(null, null, null, '2100-03-10', null, null, null);
        (new SearchPersonValidator())->validate($model);
    }

    public function testPostcodeNotEmpty()
    {
        $model = new SearchPersonModel(null, null, null, null, null, 'CM3 7YH', null);
        (new SearchPersonValidator())->validate($model);
    }

    public function testEmailNotEmpty()
    {
        $model = new SearchPersonModel(null, null, null, null, null, null, 'searchpersonvalidatortest@'.EmailAddressValidator::TEST_DOMAIN);
        (new SearchPersonValidator())->validate($model);
    }

    public function testAllFieldsNotEmpty()
    {
        $model = new SearchPersonModel('username', 'First name', 'Last name', '1970-12-12', 'Stoke Gifford', 'CM3 7YH', 'searchpersonvalidatortest@'.EmailAddressValidator::TEST_DOMAIN);
        (new SearchPersonValidator())->validate($model);
    }

    /** @expectedException \DvsaCommonApi\Service\Exception\BadRequestException */
    public function testAllFieldsEmptyStrings()
    {
        $model = new SearchPersonModel('', '', '', '', '', '', '');
        (new SearchPersonValidator())->validate($model);
    }

    /** @expectedException \DvsaCommonApi\Service\Exception\BadRequestException */
    public function testAllFieldsNull()
    {
        $model = new SearchPersonModel(null, null, null, null, null, null, null);
        (new SearchPersonValidator())->validate($model);
    }

    // this test is here only to find out how this class would work for that input data. It's not business requirement
    // Exception thrown because the date of birth value is not a valid format
    /** @expectedException \DvsaCommonApi\Service\Exception\BadRequestException */
    public function testAllFieldsZeros()
    {
        $model = new SearchPersonModel(0, 0, 0, 0, 0, 0, 0);
        (new SearchPersonValidator())->validate($model);
    }

    // this test is here only to find out how this class would work for that input data. It's not business requirement
    // Exception thrown because the date of birth value is not a valid format
    /** @expectedException \DvsaCommonApi\Service\Exception\BadRequestException */
    public function testAllFieldsStringZeros()
    {
        $model = new SearchPersonModel('0', '0', '0', '0', '0', '0', '0');
        (new SearchPersonValidator())->validate($model);
    }

    // this test is here only to find out how this class would work for that input data. It's not business requirement
    /** @expectedException \DvsaCommonApi\Service\Exception\BadRequestException */
    public function testAllFieldsFalse()
    {
        $model = new SearchPersonModel(false, false, false, false, false, false, false);
        (new SearchPersonValidator())->validate($model);
    }
}
