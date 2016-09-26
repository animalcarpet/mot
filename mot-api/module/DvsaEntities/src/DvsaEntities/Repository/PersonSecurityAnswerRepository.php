<?php
namespace DvsaEntities\Repository;

class PersonSecurityAnswerRepository extends AbstractMutableRepository
{
    /**
     * @return PersonSecurityAnswer[]
     */
    public function findAll()
    {
        $questions = parent::findAll();

        return $questions;
    }
}
