<?php

namespace DvsaCommonTest\Date;

use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\RfrCurrentDateFaker;
use DvsaCommonTest\TestUtils\XMock;

class RfrCurrentDateFakerTest extends \PHPUnit_Framework_TestCase
{
    const CURRENT_DATE = '2018-01-01';

    /**
     * @var DateTimeHolder | \PHPUnit_Framework_MockObject_MockObject
     */
    private $dateHolder;

    /**
     * @var RfrCurrentDateFaker
     */
    private $sut;


    private function createSut($fakeDate = null)
    {
        $this->dateHolder = XMock::of(DateTimeHolder::class);
        $this->dateHolder->method('getCurrent')
            ->willReturn(new \DateTime(self::CURRENT_DATE));

        if($fakeDate !== null){
            try{
                $fakeDate = new \DateTime($fakeDate);
            } catch (\Exception $e) {
                $fakeDate = null;
            }
        }

        $this->sut  = new RfrCurrentDateFaker(
            $this->dateHolder,
            $fakeDate
        );
    }

    public function testGetCurrentDate_whenNoFakeDateIsProvided()
    {
        $this->createSut();

        $result = $this->sut->getCurrentDateTime();
        $expected = new \DateTime(self::CURRENT_DATE);

        $this->assertEquals($result, $expected);
    }


    /**
     * @dataProvider validFakeDateDP
     * @param $fakeDate
     */
    public function testGetCurrentDate_whenValidFakeDateIsProvided($fakeDate)
    {
        $this->createSut($fakeDate);

        $result = $this->sut->getCurrentDateTime();
        $expected = new \DateTime($fakeDate);

        $this->assertEquals($result, $expected);
    }

    public function validFakeDateDP()
    {
        return [
            ['2018-01-01'],
            ['2018-01-01 10:11:12'],
            ['2018/01/01 10:11:12'],
        ];
    }

    /**
     * @dataProvider invalidFakeDateDP
     * @param $fakeDate
     */
    public function testGetCurrentDate_whenInvalidFakeDateIsProvided($fakeDate)
    {
        $this->createSut($fakeDate);

        $result = $this->sut->getCurrentDateTime();
        $expected = new \DateTime(self::CURRENT_DATE);

        $this->assertEquals($result, $expected);
    }

    public function invalidFakeDateDP()
    {
        return [
            [' fasdfasdfa '],
            [' 20a8-0b '],
        ];
    }

}