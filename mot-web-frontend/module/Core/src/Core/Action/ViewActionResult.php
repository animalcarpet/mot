<?php

namespace Core\Action;

use Core\ViewModel\Sidebar\SidebarInterface;

/**
 * Class ActionResult
 * @package Core\Action
 */
class ViewActionResult extends AbstractActionResult
{
    private $template;

    private $viewModel;

    /** @var SidebarInterface */
    private $sidebar;

    private $layout;

    public function __construct()
    {
        $this->layout = new ActionResultLayout();
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

    public function getViewModel()
    {
        return $this->viewModel;
    }

    public function setViewModel($viewModel)
    {
        $this->viewModel = $viewModel;
        return $this;
    }

    public function getSidebar()
    {
        return $this->sidebar;
    }

    public function setSidebar(SidebarInterface $sidebar)
    {
        $this->sidebar = $sidebar;
        return $this;
    }

    public function layout()
    {
        return $this->layout;
    }
}
