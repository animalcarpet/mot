<?php

namespace DvsaEntities\EntityTrait;

/**
 * CommonIdentityTrait.
 */
trait CommonIdentityTrait
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @param $id
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setId($id)
    {
        if (null != $id && !is_numeric($id)) {
            throw new \InvalidArgumentException("Expected numeric id, got [$id]");
        }
        $this->id = (int) $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
