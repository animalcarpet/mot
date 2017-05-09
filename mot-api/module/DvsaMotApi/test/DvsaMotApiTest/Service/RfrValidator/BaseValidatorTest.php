<?php

namespace DvsaMotApiTest\Service\RfrValidator;

use PHPUnit_Framework_TestCase;
use DvsaMotApi\Service\RfrValidator\BaseValidator;

/**
 * Validators, Fixtures and Generators.. Documentation.
 *
 * We had the following requirements:
 * - validate the acceptance criteria in ticket VM1615
 * - unit test each validator using fixtures
 * - unit test all validators added together via fixtures
 * - unit test the overall service response
 * - Fitnesse tests against the service
 *
 * SERVICE:
 * -------
 * To enable this, the following classes were created:
 *
 * The BaseValidator which all validators derive from:
 * -validate() actually checks the values passed
 * -getError() if not validated, returns a \DvsaCommonApi\Error\Message
 *
 * The AbstractValidatorTest which has the following API:
 * - getValidator() returns the validator to test
 * - getFixtureName() gets a human readable name of the current validation
 * - getFixtures() returns the fixtures for the current validator
 * - testValidate() which actually tests the fixtures against the current validator
 *
 *
 * Also, because there are two types validators, one against rows of RFRs and
 * another against the result from the server (final justification etc). Then there are
 * two Result related classes that do the same as above.
 *
 * The BaseResultValidator extends BaseValidator with the same API.
 * The AbstractResultValidatorTest extends AbstractValidatorTest with the same API.
 *
 * SO, the fixtures for a validator get run against it. Simple..
 * They also get used in the CheckAllValidatorsTest class that merges all fixtures
 * together, performs some processing to make them usable in this new context, and
 * then executes them against the server as a whole.
 *
 * We did it like this to allow reuse of fixtures.
 *
 * FITNESSE:
 * --------
 *
 * We created a generator to convert CheckAllValidatorsTest->getAllFixtures()
 * into rows of Fitnesse data to be executed against the public service API.
 *
 * These classes can be found in DvsaMotApiTest\Service\Generator\
 * With a base class of FixtureStory which must be overridden and requiring
 * a output renderer of type FixtureTemplate.
 *
 * Because each Fitnesse test is different in stricture and requirements, these templates
 * can be created as reused separately to the data that feeds them.
 *
 * This worked really well and we were generating Fitnesse tests of 101 rows and testing all
 * possible data variations aganst the service when.. Fitnesse tests crawled to a halt.
 * On average Fitnesse takes about 4 seconds to run a single row.. Painfully slow..
 *
 * In the end we added a filter to our fixtures to deletrmine which ones should be allowed
 * to pass through to Fitnesse. Happy path won out as we have full coverage at the unit level.
 *
 * GENERATOR:
 * ---------
 * Because the generator isn't official, it is in its own branch, feel free to check int out if you
 * need too.
 *
 * feature/VM-1615-Fitnesse-generators-do-not-delete-or-merge
 */

/**
 * Class BaseValidatorTest.
 */
class BaseValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testContructParams()
    {
        new BaseValidator(0, array());
    }

    public function testFluentInterface()
    {
        $validator = new BaseValidator(2, array());
        $validator->setRfrId(1)
            ->setValues(array(1))
            ->setError('test');

        $this->assertEquals($validator->getRfrId(), 1);
        $this->assertEquals($validator->getMappedRfrId(), 2);
        $this->assertEquals($validator->getValues(), array(1));
        $this->assertEquals($validator->getError(), 'test');
    }

    public function testValidateDefault()
    {
        $validator = new BaseValidator(0, array());
        $this->assertEquals(false, $validator->validate());
    }
}
