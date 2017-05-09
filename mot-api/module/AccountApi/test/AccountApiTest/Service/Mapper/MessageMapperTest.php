<?php

namespace AccountApiTest\Service\Mapper\Controller;

use AccountApi\Service\Mapper\MessageMapper;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Account\MessageDto;
use DvsaCommon\Dto\Account\MessageTypeDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaEntities\Entity\Message;
use DvsaEntities\Entity\MessageType;
use DvsaEntities\Entity\Person;

class MessageMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var MessageMapper */
    private $mapper;

    public function setUp()
    {
        $this->mapper = new MessageMapper();
    }

    public function testToDto()
    {
        $msg = $this->getTestEntity();

        $actual = $this->mapper->toDto($msg);

        $this->checkEntityWithDto($msg, $actual);
    }

    public function testManyToDto()
    {
        $msgs = [$this->getTestEntity()];

        $actual = $this->mapper->manyToDto($msgs);

        $this->assertCount(1, $actual);
        $this->checkEntityWithDto($msgs[0], $actual[0]);
    }

    private function getTestEntity()
    {
        $issuedDate = new \DateTime('2013-12-11 12:11:10');
        $expireDate = new \DateTime('2010-09-08 07:06:05');

        $person = new Person();
        $person
            ->setId(9999)
            ->setUsername('unit_userName');

        $msgType = new MessageType();
        $msgType->setCode('unit_msgTypeCode');

        $msg = new Message();
        $msg
            ->setId(8888)
            ->setToken('unit_token12345')
            ->setPerson($person)
            ->setMessageType($msgType)
            ->setIssueDate($issuedDate)
            ->setExpiryDate($expireDate)
            ->setIsAcknowledged(true);

        return $msg;
    }

    private function checkEntityWithDto(Message $entity, MessageDto $msgDto)
    {
        $this->assertInstanceOf(MessageDto::class, $msgDto);
        $this->assertEquals($entity->getToken(), $msgDto->getToken());
        $this->assertEquals(DateTimeApiFormat::dateTime($entity->getIssueDate()), $msgDto->getIssuedDate());
        $this->assertEquals(DateTimeApiFormat::dateTime($entity->getExpiryDate()), $msgDto->getExpiryDate());
        $this->assertSame($entity->isAcknowledged(), $msgDto->isAcknowledged());

        $msgType = $entity->getMessageType();
        $msgTypeDto = $msgDto->getType();
        $this->assertInstanceOf(MessageTypeDto::class, $msgTypeDto);
        $this->assertEquals($msgType->getCode(), $msgTypeDto->getCode());

        $person = $entity->getPerson();
        $msgPersonDto = $msgDto->getPerson();
        $this->assertInstanceOf(PersonDto::class, $msgDto->getPerson());
        $this->assertEquals($person->getId(), $msgPersonDto->getId());
        $this->assertEquals($person->getUsername(), $msgPersonDto->getUsername());
    }
}
