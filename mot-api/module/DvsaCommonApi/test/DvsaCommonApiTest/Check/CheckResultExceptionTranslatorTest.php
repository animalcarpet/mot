<?php

namespace DvsaCommonApiTest\Check;

use Api\Check\CheckMessage;
use Api\Check\CheckResult;
use Api\Check\CheckResultExceptionTranslator;
use Api\Check\Severity;
use DvsaCommonApi\Service\Exception\DataValidationException;
use PHPUnit_Framework_TestCase;

/**
 * Class CheckResultExceptionTranslatorTest.
 */
class CheckResultExceptionTranslatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \DvsaCommonApi\Service\Exception\DataValidationException
     */
    public function testTryThrowDataValidationExceptionGivenErrorMessagesShouldThrowException()
    {
        CheckResultExceptionTranslator::tryThrowDataValidationException(CheckResult::with(CheckMessage::withError()));
    }

    public function testTryThrowDataValidationExceptionGivenMessagesOfDifferentSeverityShouldThrowAndOnlyIncludeMessagesOfRequiredSeverity()
    {
        $checkResult = CheckResult::with(
            CheckMessage::withError()->text('errorMessage'),
            CheckMessage::withInfo()->text('infoMessage'),
            CheckMessage::withWarn()->text('warnMessage')
        );
        try {
            CheckResultExceptionTranslator::tryThrowDataValidationException($checkResult, Severity::WARN);
        } catch (DataValidationException $e) {
            $this->assertCount(1, $e->getErrors(), 'Exception contains more errors than expected!');
            $this->assertEquals(
                'warnMessage',
                current($e->getErrors())['message'],
                'Exception does not contain expected message!'
            );
        }
    }
}
