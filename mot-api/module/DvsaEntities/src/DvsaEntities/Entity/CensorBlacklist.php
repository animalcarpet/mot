<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * CensorBlacklist
 *
 * @ORM\Table(name="censor_blacklist", uniqueConstraints={@ORM\UniqueConstraint(name="phrase", columns={"phrase"})})
 * @ORM\Entity(repositoryClass="\DvsaEntities\Repository\CensorBlacklistRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class CensorBlacklist
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="phrase", type="string", length=100, nullable=false)
     */
    private $phrase = '';

    /**
     * Set phrase
     *
     * @param string $phrase
     *
     * @return CensorBlacklist
     */
    public function setPhrase($phrase)
    {
        $this->phrase = $phrase;

        return $this;
    }

    /**
     * Get phrase
     *
     * @return string
     */
    public function getPhrase()
    {
        return $this->phrase;
    }
}
