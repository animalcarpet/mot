<?php

namespace CoreTest\View\Helper;

use Core\View\Helper\GetReleaseTag;

/**
 * Class GetReleaseTagTest.
 */
class GetReleaseTagTest extends \PHPUnit_Framework_TestCase
{
    public function testNoReleaseTagValueIntoReleaseTagWillNotReturnException()
    {
        $releaseTagHelper = new GetReleaseTag(null);
        $this->assertEmpty($releaseTagHelper->getReleaseTag());
    }

    public function testConfigArrayWillNotReturnExceptionButReturnNull()
    {
        $exampleConfig = [
            'configA' => 'valueA',
            'configB' => 'valueB',
            'no_release' => 'none',
        ];

        $releaseTagHelper = new GetReleaseTag($exampleConfig);
        $this->assertNull($releaseTagHelper->getReleaseTag());
    }

    public function testNoReleaseTagValueWillNotReturnException()
    {
        $releaseTag = '';
        $releaseTagHelper = new GetReleaseTag($releaseTag);

        // This should return empty, and not null as it is passing through a type of variable (string)
        $this->assertEmpty($releaseTagHelper->getReleaseTag());
    }

    public function testValueOfReleaseTagWillReturnReleaseTag()
    {
        $releaseTag = '1.9.3';
        $releaseHelper = new GetReleaseTag($releaseTag);
        $this->assertEquals('1.9.3', $releaseHelper->getReleaseTag());
    }

    // Edgecase
    public function testValueOfReleaseTagIsArrayOrObjectWillNotReturnException()
    {
        $releaseTag = ['1.9.3'];
        $releaseHelper = new GetReleaseTag($releaseTag);
        $this->assertEmpty($releaseHelper->getReleaseTag());

        $releaseTag = new \stdClass();
        $releaseHelper = new GetReleaseTag($releaseTag);
        $this->assertEmpty($releaseHelper->getReleaseTag());
    }

    public function testNoReleaseTagAndNoRenderFooterBooleanReturnEmptyAndFalse()
    {
        $releaseTag = '';
        $releaseTagRender = false;

        $releaseHelper = new GetReleaseTag($releaseTag);
        $releaseHelper->setCanRenderReleaseTagName($releaseTagRender);

        $this->assertEmpty($releaseHelper->getReleaseTag());
        $this->assertFalse($releaseHelper->canRenderReleaseTagName());
    }

    public function testCanRenderReleaseTagFromConfigReturnTrue()
    {
        $releaseTag = null;
        $releaseTagRender = true;

        $releaseHelper = new GetReleaseTag($releaseTag);
        $releaseHelper->setCanRenderReleaseTagName($releaseTagRender);

        $this->assertTrue($releaseHelper->canRenderReleaseTagName());
    }

    public function testCannotRenderWillReturnEmptyStringWhenAttemptingToRender()
    {
        $releaseTag = '';
        $releaseTagRender = false;

        $releaseHelper = new GetReleaseTag($releaseTag);
        $releaseHelper->setCanRenderReleaseTagName($releaseTagRender);

        $this->assertEmpty($releaseHelper->getReleaseTag());
        $this->assertFalse($releaseHelper->canRenderReleaseTagName());
        $this->assertEmpty($releaseHelper->renderReleaseTag('<div></div>'));
    }

    public function testCanRenderWillReturnTemplateWithNoParameterWhenAttemptingToRender()
    {
        $releaseTag = '1.9.3';
        $releaseTagRender = true;

        $releaseHelper = new GetReleaseTag($releaseTag);
        $releaseHelper->setCanRenderReleaseTagName($releaseTagRender);

        $this->assertEquals('<div></div>', $releaseHelper->renderReleaseTag('<div></div>'));
    }

    public function testCanRenderWillReturnTemplateWithParameterWhenAttemptingToRender()
    {
        $releaseTag = '1.9.3';
        $releaseTagRender = true;

        $releaseHelper = new GetReleaseTag($releaseTag);
        $releaseHelper->setCanRenderReleaseTagName($releaseTagRender);

        $this->assertEquals('<div>1.9.3</div>', $releaseHelper->renderReleaseTag('<div>{release_tag_name}</div>'));
    }
}
