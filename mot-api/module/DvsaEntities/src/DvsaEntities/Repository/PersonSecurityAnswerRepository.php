<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\PersonSecurityAnswer;

class PersonSecurityAnswerRepository extends AbstractMutableRepository
{
    /**
     * @param integer $personId
     * @param integer $questionId
     * @return null|PersonSecurityAnswer
     */
    public function getPersonAnswerForQuestion($personId, $questionId)
    {
        return $this->findOneBy(['person' => $personId, 'securityQuestion' => $questionId]);
    }
}
