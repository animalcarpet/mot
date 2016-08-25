<?php

namespace Core\Action;

class ActionResultLayout
{
    private $pageLede;

    private $pageTitle;

    private $pageSubTitle;

    private $pageTertiaryTitle;

    private $template;

    private $breadcrumbs = [];

    public function getPageLede()
    {
        return $this->pageLede;
    }

    public function setPageLede($pageLede)
    {
        $this->pageLede = $pageLede;
        return $this;
    }

    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
        return $this;
    }

    public function getPageSubTitle()
    {
        return $this->pageSubTitle;
    }

    public function setPageSubTitle($pageSubTitle)
    {
        $this->pageSubTitle = $pageSubTitle;
        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function setBreadcrumbs(array $breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
        return $this;
    }

    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    public function getPageTertiaryTitle()
    {
        return $this->pageTertiaryTitle;
    }

    public function setPageTertiaryTitle($pageTertiaryTitle)
    {
        $this->pageTertiaryTitle = $pageTertiaryTitle;
        return $this;
    }
}
