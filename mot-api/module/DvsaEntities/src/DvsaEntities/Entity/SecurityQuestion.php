<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * SecurityQuestion.
 *
 * @ORM\Table(
 *  name="security_question",
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\SecurityQuestionRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class SecurityQuestion extends Entity
{
    use CommonIdentityTrait;

    /**
     * @ORM\Column(name="question_text", type="string", length=80, nullable=false)
     */
    private $text;

    /**
     * @ORM\Column(name="question_group", type="integer", nullable=false)
     */
    private $group;

    /**
     * @ORM\Column(name="display_order", type="integer", nullable=false)
     */
    private $displayOrder;

    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getText()
    {
        return $this->text;
    }
}
