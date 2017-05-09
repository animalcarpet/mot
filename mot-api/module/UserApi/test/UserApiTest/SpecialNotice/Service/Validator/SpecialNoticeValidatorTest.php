<?php

namespace UserApiTest\SpecialNotice\Service;

use DvsaCommon\Constants\SpecialNoticeAudience;
use PHPUnit_Framework_TestCase;
use UserApi\SpecialNotice\Service\Validator\SpecialNoticeValidator;

/**
 * I'm building my professional career on comments.
 */
class SpecialNoticeValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\RequiredFieldException
     * @expectedExceptionMessage A required field is missing
     */
    public function testValidateWithMissingRequiredFieldsShouldThrowRequiredFieldException()
    {
        $this->executeValidate([]);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\InvalidFieldValueException
     * @expectedExceptionMessage Invalid field value
     */
    public function testValidateWithTargetRolesNotBeingAnArrayShouldThrowInvalidFieldValueException()
    {
        $input = $this->getInputData();
        $input['targetRoles'] = 'targetRoles';

        $this->executeValidate($input);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\InvalidFieldValueException
     * @expectedExceptionMessage Invalid field value
     */
    public function testValidateWithWrongRoleStringShouldThrowInvalidFieldValueException()
    {
        $input = $this->getInputData();
        $input['targetRoles'] = ['WRONG-ROLE'];

        $this->executeValidate($input);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\InvalidFieldValueException
     * @expectedExceptionMessage Invalid field value
     */
    public function testValidateWithIncorrectInternalPublishDateShouldThrowInvalidFieldValueException()
    {
        $input = $this->getInputData();
        $input['internalPublishDate'] = 'internalPublishDate';

        $this->executeValidate($input);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\InvalidFieldValueException
     * @expectedExceptionMessage Invalid field value
     */
    public function testValidateWithIncorrectExternalPublishDateShouldThrowInvalidFieldValueException()
    {
        $input = $this->getInputData();
        $input['externalPublishDate'] = 'externalPublishDate';

        $this->executeValidate($input);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\InvalidFieldValueException
     * @expectedExceptionMessage Invalid field value
     */
    public function testValidateWithAcknowledgementPeriodNotBeingNumericShouldThrowInvalidFieldValueException()
    {
        $input = $this->getInputData();
        $input['acknowledgementPeriod'] = 'acknowledgementPeriod';

        $this->executeValidate($input);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\InvalidFieldValueException
     * @expectedExceptionMessage Notice Text Markdown may not contain Javascript
     */
    public function testValidateWithNoticeTextMarkdownContainingJavascript()
    {
        $input = $this->getInputData();
        $input['noticeText'] = "[irm](javascript:prompt('PleaseEnterYourPasswordToContinue'))";

        $this->executeValidate($input);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\InvalidFieldValueException
     * @expectedExceptionMessage Notice Text Markdown may not contain Javascript
     */
    public function testValidateWithNoticeTextMarkdownContainingScriptTag()
    {
        $input = $this->getInputData();
        $input['noticeText'] = '<div=\"test\"><script>alert(document.cookie)<\/script><\/div>';

        $this->executeValidate($input);
    }

    /**
     * @param $input
     */
    private function executeValidate($input)
    {
        $validator = new SpecialNoticeValidator();

        $validator->validate($input);
    }

    /**
     * @return array
     */
    private function getInputData()
    {
        $input = [
            'noticeTitle' => 'noticeTitle',
            'internalPublishDate' => '2014-02-15',
            'externalPublishDate' => '2014-03-15',
            'acknowledgementPeriod' => 3,
            'noticeText' => 'noticeText',
            'targetRoles' => [SpecialNoticeAudience::DVSA],
        ];

        return $input;
    }
}
