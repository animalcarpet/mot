<?php

namespace Application\View\Helper;

use InvalidArgumentException;
use OutOfBoundsException;
use Zend\View\Helper\AbstractHtmlElement;

/**
 * Controller to render a page with a list of links to Manuals, guides, special notices and standards.
 * accessible by this->manualsHelper() in any *.phtml file.
 */
class ManualsAndGuidesHelper extends AbstractHtmlElement
{
    const PAGE_TITLE_INDEX_ACTION = 'MOT manuals and guide';

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $documents;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return static::PAGE_TITLE_INDEX_ACTION;
    }

    /**
     * @return array
     */
    private function processDocumentsConfig()
    {
        $this->documents = [];

        foreach (array_keys($this->config) as $k) {
            foreach (['name', 'url'] as $attr) {
                if (!isset($this->config[$k][$attr])) {
                    throw new OutOfBoundsException(sprintf('Attribute "%s" is missing in the documents configuration.',
                        $attr));
                }

                if (!is_string($this->config[$k][$attr])) {
                    throw new InvalidArgumentException(sprintf('Attribute "%s" in the documents configuration should be of string type, got "%s" instead',
                        $attr, gettype($this->config[$k][$attr])));
                }

                $this->documents[$k][$attr] = trim($this->config[$k][$attr]);
            }
        }

        return $this->documents;
    }

    /**
     * @return array
     */
    public function getDocuments()
    {
        if (empty($this->documents)) {
            $this->processDocumentsConfig();
        }

        return $this->documents;
    }
}
