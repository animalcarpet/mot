<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\View;


use Dvsa\Mot\Frontend\MotTestModule\View\FlashMessageBuilder;

class FlashMessageBuilderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param $type
     * @param $description
     * @param $expected
     *
     * @dataProvider defectAddedSuccessfullyProvider
     */
    public function testDefectAddedSuccessfully($type, $description, $expected)
    {
        $actual = FlashMessageBuilder::defectAddedSuccessfully($type, $description);
        $this->assertEquals($expected, $actual);
    }

    public function defectAddedSuccessfullyProvider()
    {
        return [
          ['Advisory', 'test description', '<strong>This Advisory has been added:</strong><br> test description'],
          ['Advisory', '<script></script>', '<strong>This Advisory has been added:</strong><br> &lt;script&gt;&lt;/script&gt;'],
          ['PRS', '<script></script>', '<strong>This PRS has been added:</strong><br> &lt;script&gt;&lt;/script&gt;'],
          ['Advisory', '<IMG SRC=javascript:alert("XSS")>', '<strong>This Advisory has been added:</strong><br> &lt;IMG SRC=javascript:alert(&quot;XSS&quot;)&gt;'],
        ];
    }

    /**
     * @param $description
     * @param $expected
     *
     * @dataProvider manualAdvisoryAddedSuccessfullyProvider
     */
    public function testManualAdvisoryAddedSuccessfully($description, $expected)
    {
        $actual = FlashMessageBuilder::manualAdvisoryAddedSuccessfully($description);
        $this->assertEquals($expected, $actual);
    }

    public function manualAdvisoryAddedSuccessfullyProvider()
    {
        return [
            ['test description', '<strong>This advisory has been added:</strong><br> test description'],
            ['<script></script>', '<strong>This advisory has been added:</strong><br> &lt;script&gt;&lt;/script&gt;'],
            ['<IMG SRC=javascript:alert("XSS")>', '<strong>This advisory has been added:</strong><br> &lt;IMG SRC=javascript:alert(&quot;XSS&quot;)&gt;'],
        ];
    }


    /**
     * @param $type
     * @param $description
     * @param $expected
     *
     * @dataProvider defectEditedSuccessfullyProvider
     */
    public function testDefectEditedSuccessfully($type, $description, $expected)
    {
        $actual = FlashMessageBuilder::defectEditedSuccessfully($type, $description);
        $this->assertEquals($expected, $actual);
    }


    public function defectEditedSuccessfullyProvider()
    {
        return [
            ['Advisory', 'test description', '<strong>This Advisory has been edited:</strong><br> test description'],
            ['Advisory', '<script></script>', '<strong>This Advisory has been edited:</strong><br> &lt;script&gt;&lt;/script&gt;'],
            ['PRS', '<script></script>', '<strong>This PRS has been edited:</strong><br> &lt;script&gt;&lt;/script&gt;'],
            ['Advisory', '<IMG SRC=javascript:alert("XSS")>', '<strong>This Advisory has been edited:</strong><br> &lt;IMG SRC=javascript:alert(&quot;XSS&quot;)&gt;'],
        ];
    }


    /**
     * @param $type
     * @param $description
     * @param $expected
     *
     * @dataProvider defectRemovedSuccessfullyProvider
     */
    public function testDefectRemovedSuccessfully($type, $description, $expected)
    {
        $actual = FlashMessageBuilder::defectRemovedSuccessfully($type, $description);
        $this->assertEquals($expected, $actual);
    }


    public function defectRemovedSuccessfullyProvider()
    {
        return [
            ['Advisory', 'test description', '<strong>This Advisory has been removed:</strong><br> test description'],
            ['Advisory', '<script></script>', '<strong>This Advisory has been removed:</strong><br> &lt;script&gt;&lt;/script&gt;'],
            ['PRS', '<script></script>', '<strong>This PRS has been removed:</strong><br> &lt;script&gt;&lt;/script&gt;'],
            ['Advisory', '<IMG SRC=javascript:alert("XSS")>', '<strong>This Advisory has been removed:</strong><br> &lt;IMG SRC=javascript:alert(&quot;XSS&quot;)&gt;'],
        ];
    }

    /**
     * @param $type
     * @param $description
     * @param $expected
     *
     * @dataProvider defectRepairedSuccessfullyProvider
     */
    public function testDefectRepairedSuccessfully($type, $description, $expected)
    {
        $actual = FlashMessageBuilder::defectRepairedSuccessfully($type, $description);
        $this->assertEquals($expected, $actual);
    }


    public function defectRepairedSuccessfullyProvider()
    {
        return [
            ['Advisory', 'test description', 'The Advisory <strong>test description</strong> has been repaired'],
            ['Advisory', '<script></script>', 'The Advisory <strong>&lt;script&gt;&lt;/script&gt;</strong> has been repaired'],
            ['PRS', '<script></script>', 'The PRS <strong>&lt;script&gt;&lt;/script&gt;</strong> has been repaired'],
            ['Advisory', '<IMG SRC=javascript:alert("XSS")>', 'The Advisory <strong>&lt;IMG SRC=javascript:alert(&quot;XSS&quot;)&gt;</strong> has been repaired'],
        ];
    }

    /**
     * @param $type
     * @param $description
     * @param $expected
     *
     * @dataProvider defectRepairedUnsuccessfullyProvider
     */
    public function testDefectRepairedUnsuccessfully($type, $description, $expected)
    {
        $actual = FlashMessageBuilder::defectRepairedUnsuccessfully($type, $description);
        $this->assertEquals($expected, $actual);
    }


    public function defectRepairedUnsuccessfullyProvider()
    {
        return [
            ['Advisory', 'test description', 'The Advisory <strong>test description</strong> has not been repaired. Try again.'],
            ['Advisory', '<script></script>', 'The Advisory <strong>&lt;script&gt;&lt;/script&gt;</strong> has not been repaired. Try again.'],
            ['PRS', '<script></script>', 'The PRS <strong>&lt;script&gt;&lt;/script&gt;</strong> has not been repaired. Try again.'],
            ['Advisory', '<IMG SRC=javascript:alert("XSS")>', 'The Advisory <strong>&lt;IMG SRC=javascript:alert(&quot;XSS&quot;)&gt;</strong> has not been repaired. Try again.'],
        ];
    }


    /**
     * @param $type
     * @param $description
     * @param $expected
     *
     * @dataProvider undoDefectRepairSuccessfullyProvider
     */
    public function testUndoDefectRepairSuccessfully($type, $description, $expected)
    {
        $actual = FlashMessageBuilder::undoDefectRepairSuccessfully($type, $description);
        $this->assertEquals($expected, $actual);
    }


    public function undoDefectRepairSuccessfullyProvider()
    {
        return [
            ['Advisory', 'test description', 'The Advisory <strong>test description</strong> has been added'],
            ['Advisory', '<script></script>', 'The Advisory <strong>&lt;script&gt;&lt;/script&gt;</strong> has been added'],
            ['PRS', '<script></script>', 'The PRS <strong>&lt;script&gt;&lt;/script&gt;</strong> has been added'],
            ['Advisory', '<IMG SRC=javascript:alert("XSS")>', 'The Advisory <strong>&lt;IMG SRC=javascript:alert(&quot;XSS&quot;)&gt;</strong> has been added'],
        ];
    }


    /**
     * @param $type
     * @param $description
     * @param $expected
     *
     * @dataProvider undoDefectRepairUnsuccessfullyProvider
     */
    public function testUndoDefectRepairUnsuccessfully($type, $description, $expected)
    {
        $actual = FlashMessageBuilder::undoDefectRepairUnsuccessfully($type, $description);
        $this->assertEquals($expected, $actual);
    }


    public function undoDefectRepairUnsuccessfullyProvider()
    {
        return [
            ['Advisory', 'test description', 'The Advisory <strong>test description</strong> has not been added. Try again.'],
            ['Advisory', '<script></script>', 'The Advisory <strong>&lt;script&gt;&lt;/script&gt;</strong> has not been added. Try again.'],
            ['PRS', '<script></script>', 'The PRS <strong>&lt;script&gt;&lt;/script&gt;</strong> has not been added. Try again.'],
            ['Advisory', '<IMG SRC=javascript:alert("XSS")>', 'The Advisory <strong>&lt;IMG SRC=javascript:alert(&quot;XSS&quot;)&gt;</strong> has not been added. Try again.'],
        ];
    }
}