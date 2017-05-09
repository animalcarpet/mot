<?php

namespace ReportTest\Table\Formatter;

use Core\ViewModel\Badge\Badge;
use DvsaCommonTest\TestUtils\TestCaseViewTrait;
use DvsaCommonTest\TestUtils\XMock;
use Organisation\Presenter\StatusPresenterData;
use Report\Table\ColumnOptions;
use Report\Table\Formatter\Status;
use Zend\Stdlib\Parameters;
use Zend\View\Renderer\PhpRenderer;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseViewTrait;

    public function testFormatForStatus()
    {
        /** @var PhpRenderer|MockObj $viewRendererMock */
        $viewRendererMock = $this->getMockBuilder(PhpRenderer::class)->disableOriginalConstructor()->getMock();

        $viewRendererMock->method('partial')->with($this->anything());

        // logical block: prepare parameters
        $column = new ColumnOptions();
        $sidebarBagde = new Badge('cssClass');
        $status = (new StatusPresenterData('status', $sidebarBagde));
        $column->setField('Vts status');

        $rowData = [
            'Vts status' => $status,
        ];

        //  logical block: call
        Status::format($rowData, $column, $viewRendererMock);
    }

    public function testFormatForInvalidArgument()
    {
        /** @var PhpRenderer|MockObj $viewRendererMock */
        $viewRendererMock = XMock::of(PhpRenderer::class);

        $viewRendererMock->expects(
            $this->any())->method('partial')->willReturnArgument('adf');

        // logical block: prepare parameters
        $column = new ColumnOptions();
        $column->setField('Vts status');

        $rowData = [
            'Vts status' => 'value',
        ];

        //  logical block: call
        $this->setExpectedException('\InvalidArgumentException', 'StatusPresenterData needed');
        Status::format($rowData, $column, $viewRendererMock);
    }
}
