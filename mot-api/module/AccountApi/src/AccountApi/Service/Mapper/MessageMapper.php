<?php

namespace AccountApi\Service\Mapper;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Account\MessageDto;
use DvsaCommon\Dto\Account\MessageTypeDto;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;
use DvsaEntities\Entity\Message;
use OrganisationApi\Service\Mapper\PersonMapper;

class MessageMapper extends AbstractApiMapper
{
    /**
     * @return MessageDto[]
     */
    public function manyToDto($messages)
    {
        return parent::manyToDto($messages);
    }

    /**
     * @param Message $message
     *
     * @return MessageDto
     *
     * @throws \RuntimeException
     */
    public function toDto($message)
    {
        $msgTypeEntity = $message->getMessageType();
        $msgTypeDto = new MessageTypeDto();
        $msgTypeDto
            ->setCode($msgTypeEntity->getCode())
            ->setName($msgTypeEntity->getName());

        $dto = new MessageDto();
        $dto
            ->setType($msgTypeDto)
            ->setPerson((new PersonMapper())->toDto($message->getPerson()))
            ->setToken($message->getToken())
            ->setIssuedDate(DateTimeApiFormat::dateTime($message->getIssueDate()))
            ->setExpiryDate(DateTimeApiFormat::dateTime($message->getExpiryDate()))
            ->setIsAcknowledged($message->isAcknowledged());

        return $dto;
    }
}
