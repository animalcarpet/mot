<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTestReasonForCancel;
use Doctrine\ORM\EntityRepository;

class MotTestReasonForCancelRepository extends EntityRepository
{
    /**
     * @param int $id
     *
     * @return MotTestReasonForCancel
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $entity = $this->find($id);
        if ($entity === null) {
            throw new NotFoundException('Mot test reason for cancel', $id);
        }

        return $entity;
    }
}
