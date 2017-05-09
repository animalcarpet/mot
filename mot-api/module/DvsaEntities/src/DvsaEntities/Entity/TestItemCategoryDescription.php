<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Translated test item category name and description.
 *
 * @ORM\Table(name="ti_category_language_content_map")
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class TestItemCategoryDescription extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var TestItemSelector
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\TestItemSelector", inversedBy="descriptions")
     * @ORM\JoinColumn(name="test_item_category_id", referencedColumnName="id")
     */
    private $testItemCategory;

    /**
     * @var Language
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Language", fetch="EAGER")
     * @ORM\JoinColumn(name="language_lookup_id", referencedColumnName="id")
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=100, nullable=true)
     */
    private $description;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param Language $language
     *
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return TestItemSelector
     */
    public function getTestItemCategory()
    {
        return $this->testItemCategory;
    }
}
