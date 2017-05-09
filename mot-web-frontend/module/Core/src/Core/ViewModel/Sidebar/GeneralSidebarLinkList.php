<?php

namespace Core\ViewModel\Sidebar;

use DvsaCommon\Utility\TypeCheck;

class GeneralSidebarLinkList implements GeneralSidebarItemInterface
{
    private $title;
    private $id;

    /** @var GeneralSidebarLink[] */
    private $links;

    public function __construct($title)
    {
        $this->title = $title;
    }

    public function addLink(GeneralSidebarLink $link)
    {
        TypeCheck::assertInstance($link, GeneralSidebarLink::class);
        $this->links[] = $link;
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function isEmpty()
    {
        return $this->links ? false : true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}
