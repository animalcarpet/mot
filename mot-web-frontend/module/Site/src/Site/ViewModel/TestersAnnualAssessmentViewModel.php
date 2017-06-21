<?php
namespace Site\ViewModel;


use Core\Routing\VtsRoutes;
use Report\Table\Table;
use Zend\View\Helper\Url;

class TestersAnnualAssessmentViewModel
{
    /** @var  Table */
    private $tableForGroupA;

    /** @var  Table */
    private $tableForGroupB;

    /** @var  int */
    private $vtsId;

    private $urlHelper;

    /** @var bool */
    private $canTestGroupA = false;

    /** @var bool */
    private $canTestGroupB = false;

    public function __construct(Url $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @return Table
     */
    public function getTableForGroupA()
    {
        return $this->tableForGroupA;
    }

    /**
     * @param Table $tableForGroupA
     * @return TestersAnnualAssessmentViewModel
     */
    public function setTableForGroupA($tableForGroupA)
    {
        $this->tableForGroupA = $tableForGroupA;
        return $this;
    }

    /**
     * @return Table
     */
    public function getTableForGroupB()
    {
        return $this->tableForGroupB;
    }

    /**
     * @param Table $tableForGroupB
     * @return TestersAnnualAssessmentViewModel
     */
    public function setTableForGroupB($tableForGroupB)
    {
        $this->tableForGroupB = $tableForGroupB;
        return $this;
    }

    /**
     * @return int
     */
    public function getVtsId()
    {
        return $this->vtsId;
    }

    /**
     * @param int $vtsId
     * @return TestersAnnualAssessmentViewModel
     */
    public function setVtsId($vtsId)
    {
        $this->vtsId = $vtsId;
        return $this;
    }

    public function getBackLink()
    {
        return VtsRoutes::of($this->urlHelper)->vts($this->vtsId);
    }

    /**
     * @return boolean
     */
    public function getCanTestGroupA()
    {
        return $this->canTestGroupA;
    }

    /**
     * @param boolean $canTestGroupA
     * @return TestersAnnualAssessmentViewModel
     */
    public function setCanTestGroupA($canTestGroupA)
    {
        $this->canTestGroupA = $canTestGroupA;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getCanTestGroupB()
    {
        return $this->canTestGroupB;
    }

    /**
     * @param boolean $canTestGroupB
     * @return TestersAnnualAssessmentViewModel
     */
    public function setCanTestGroupB($canTestGroupB)
    {
        $this->canTestGroupB = $canTestGroupB;
        return $this;
    }
}