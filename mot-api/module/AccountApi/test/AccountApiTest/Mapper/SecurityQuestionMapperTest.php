<?php

namespace AccountApiTest\Mapper;

use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaEntities\Entity\SecurityQuestion;
use AccountApi\Mapper\SecurityQuestionMapper;

/**
 * Class SecurityQuestionMapperTest.
 */
class SecurityQuestionMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testSecurityQuestionMapper()
    {
        $mapper = new SecurityQuestionMapper();

        $entity = new SecurityQuestion();
        $entity->setId(1);

        $result = $mapper->toDto($entity);
        $this->assertInstanceOf(SecurityQuestionDto::class, $result);
        $this->assertEquals(1, $result->getId());
    }
}
