<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestSurvey;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class MotTestSurveyTest extends PHPUnit_Framework_TestCase
{
    public function testDefaultStateOnInstanceCreation()
    {
        $motTest = $this->createMotTest();

        $motTestSurvey = new MotTestSurvey($motTest);
        $this->assertEquals($motTest, $motTestSurvey->getMotTest());
        $this->assertInternalType('string', $motTestSurvey->getToken());
        $this->assertNotEmpty($motTestSurvey->getToken());
        $this->assertFalse($motTestSurvey->hasBeenPresented());
        $this->assertFalse($motTestSurvey->hasBeenSubmitted());
    }

    public function testChangeOfStateForSurveyHasBeenPresented()
    {
        $motTestSurvey = new MotTestSurvey($this->createMotTest());
        $this->assertFalse($motTestSurvey->hasBeenPresented());
        $motTestSurvey->setHasBeenPresented(true);
        $this->assertTrue($motTestSurvey->hasBeenPresented());
    }

    public function testChangeOfStateForSurveyHasBeenSubmitted()
    {
        $motTestSurvey = new MotTestSurvey($this->createMotTest());
        $this->assertFalse($motTestSurvey->hasBeenSubmitted());
        $motTestSurvey->setHasBeenSubmitted(true);
        $this->assertTrue($motTestSurvey->hasBeenSubmitted());
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|MotTest
     */
    private function createMotTest()
    {
        return $this
            ->getMockBuilder(MotTest::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
