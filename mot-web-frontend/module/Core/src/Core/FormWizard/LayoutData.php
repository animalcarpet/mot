<?php
namespace Core\FormWizard;

class LayoutData
{
    private $pageTitle;
    private $pageSubTitle;
    private $pageLede;
    private $breadcrumbs = [];

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * @param string $pageTitle
     * @return LayoutData
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageSubTitle()
    {
        return $this->pageSubTitle;
    }

    /**
     * @param string $pageSubTitle
     * @return LayoutData
     */
    public function setPageSubTitle($pageSubTitle)
    {
        $this->pageSubTitle = $pageSubTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageLede()
    {
        return $this->pageLede;
    }

    /**
     * @param string $pageLede
     * @return LayoutData
     */
    public function setPageLede($pageLede)
    {
        $this->pageLede = $pageLede;
        return $this;
    }

    /**
     * @return array
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    /**
     * @param array $breadcrumbs
     * @return LayoutData
     */
    public function setBreadcrumbs(array $breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
        return $this;
    }
}
