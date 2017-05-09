<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace DvsaEntitiesTest\Repository;

use Doctrine\ORM\Mapping\ClassMetadata;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\ServiceException;
use DvsaCommonApi\Service\Exception\TooFewResultsException;
use DvsaCommonApi\Service\Exception\TooManyResultsException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\PersonSecurityAnswer;
use DvsaEntities\Entity\SecurityQuestion;
use DvsaEntities\Repository\SecurityQuestionRepository;

class SecurityQuestionRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $fetched
     * @param ServiceException $expectedException
     * @dataProvider findQuestionsByPersonIdExceptionsDataProvider
     */
    public function testFindQuestionsByPersonIdExceptions($fetched, $expectedException)
    {
        $this->setExpectedException(
            get_class($expectedException),
            $expectedException->getMessage()
        );

        $mockRepository = XMock::of(SecurityQuestionRepository::class);
        $mockRepository->expects($this->once())
            ->method('findBy')
            ->willReturn($fetched);

        $mockEntity = XMock::of(SecurityQuestion::class, ['getRepository']);
        $mockEntity->expects($this->once())
            ->method('getRepository')
            ->willReturn($mockRepository);

        $securityQuestionRepository = new SecurityQuestionRepository($mockEntity, new ClassMetadata(SecurityQuestion::class));

        $securityQuestionRepository->findQuestionsByPersonId(1);
    }

    public function findQuestionsByPersonIdExceptionsDataProvider()
    {
        $tooFewResult = [];
        $tooManyResult = [];

        for ($i = 1; $i <= SecurityQuestionRepository::EXPECTED_NUMBER_OF_QUESTIONS_PER_USER + 1; ++$i) {
            $mock = XMock::of(PersonSecurityAnswer::class);

            if ($i < SecurityQuestionRepository::EXPECTED_NUMBER_OF_QUESTIONS_PER_USER) {
                $tooFewResult[] = $mock;
            }
            $tooManyResult[] = $mock;
        }

        return [
            [
                'fetched' => null,
                'expectedException' => new NotFoundException('No question has been found'),
            ],
            [
                'fetched' => false,
                'expectedException' => new NotFoundException('No question has been found'),
            ],
            [
                'fetched' => 0,
                'expectedException' => new NotFoundException('No question has been found'),
            ],
            [
                'fetched' => [],
                'expectedException' => new NotFoundException('No question has been found'),
            ],
            [
                'fetched' => $tooFewResult,
                'expectedException' => new TooFewResultsException('Too few security questions have been fetched'),
            ],
            [
                'fetched' => $tooManyResult,
                'expectedException' => new TooManyResultsException('Too many security questions have been fetched'),
            ],
        ];
    }
}
