<?php

namespace CensorApiTest\Service;

use CensorApi\Service\CensorService;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\CensorBlacklist;
use DvsaEntities\Repository\CensorBlacklistRepository;

/**
 * Class CensorServiceTest.
 */
class CensorServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CensorService
     */
    private $censorService;

    /**
     * @var CensorBlacklistRepository
     */
    private $censorBlacklistRepositoryMock;

    public static function data()
    {
        return [
            [['text' => 'dirtyword', 'result' => true]], [['text' => 'dirtywords', 'result' => true]], [['text' => 'ddd1rtywords', 'result' => true]], [['text' => 'ddd1rtyw0rds', 'result' => true]], [['text' => 'dirts', 'result' => false]], [['text' => 'word', 'result' => false]], [['text' => 'notrelevant', 'result' => false]],
        ];
    }

    /**
     * @dataProvider data
     */
    public function testContainsProfanity($assumption)
    {
        $this->defineBadWords(
            [
                (new CensorBlacklist())->setPhrase('dirtyword'),
            ]
        );
        $res = $this->censorService->containsProfanity($assumption['text']);
        $this->assertEquals($res, $assumption['result']);
    }

    private function defineBadWords(array $badWords)
    {
        $this->censorBlacklistRepositoryMock->expects($this->any())
            ->method('getBlacklist')
            ->with()
            ->will($this->returnValue($badWords));
    }

    protected function setUp()
    {
        $this->censorBlacklistRepositoryMock = XMock::of(CensorBlacklistRepository::class);
        $this->censorService = new CensorService($this->censorBlacklistRepositoryMock);
        parent::setUp();
    }
}
