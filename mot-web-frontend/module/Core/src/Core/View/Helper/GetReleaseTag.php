<?php

namespace Core\View\Helper;

use Zend\View\Helper\AbstractHelper;

class GetReleaseTag extends AbstractHelper
{
    /**
     * @var string
     */
    protected $releaseTag;

    /**
     * @var bool
     */
    protected $canRenderTagName = false;

    /**
     * @var string
     */
    protected $releaseTagReplaceKey = '{release_tag_name}';

    public function __construct($releaseTag)
    {
        if (is_string($releaseTag)) {
            $this->releaseTag = $releaseTag;
        }
    }

    /**
     * @return string
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getReleaseTag()
    {
        return $this->releaseTag;
    }

    /**
     * @param $releaseTagTemplate
     *
     * @return string
     */
    public function renderReleaseTag($releaseTagTemplate)
    {
        if (!$this->canRenderReleaseTagName()) {
            return '';
        }

        return str_replace(
            $this->getReleaseTagReplaceKey(),
            $this->getReleaseTag(),
            $releaseTagTemplate
        );
    }

    /**
     * @return bool
     */
    public function canRenderReleaseTagName()
    {
        return $this->canRenderTagName;
    }

    /**
     * @param bool $can
     */
    public function setCanRenderReleaseTagName($can)
    {
        $this->canRenderTagName = $can;

        return $this;
    }

    /**
     * @return string
     */
    public function getReleaseTagReplaceKey()
    {
        return $this->releaseTagReplaceKey;
    }

    /**
     * @param string $key
     */
    public function setReleaseTagReplaceKey($key)
    {
        $this->releaseTagReplaceKey = $key;

        return $this;
    }
}
