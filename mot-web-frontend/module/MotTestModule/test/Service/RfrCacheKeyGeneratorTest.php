<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Service;


use Dvsa\Mot\Frontend\MotTestModule\Service\RfrCacheKeyGenerator;

class RfrCacheKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_DATE = '2017-01-01';

    /** @var RfrCacheKeyGenerator */
    private $sut;

    public function setUp()
    {
        $this->sut = new RfrCacheKeyGenerator();
    }

    /**
     * @dataProvider validInputDP
     */
    public function testGenerateKey_withValidInput_shouldGenerateKey(...$args)
    {
        list($vehicleClass, $categoryId, $isVe, $date) = $args;

        $expected = sprintf(
            "%s_%s_%s_%s",
            $vehicleClass,
            $categoryId,
            true === $isVe ? RfrCacheKeyGenerator::IS_VE_MARKER : RfrCacheKeyGenerator::IS_TESTER_MARKER,
            self::DEFAULT_DATE
        );

        $result = $this->sut->generateKey(...$args);

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider validInputDP
     */
    public function testCreateFrom_withValidInput_shouldGenerateKey(...$args)
    {
        list($vehicleClass, $categoryId, $isVe, $date) = $args;

        $expected = sprintf(
            "%s_%s_%s_%s",
            $vehicleClass,
            $categoryId,
            true === $isVe ? RfrCacheKeyGenerator::IS_VE_MARKER : RfrCacheKeyGenerator::IS_TESTER_MARKER,
            self::DEFAULT_DATE
        );

        $result = $this->sut->createFrom(...$args);

        $this->assertEquals($expected, $result);
    }

    public function validInputDP()
    {
        return [
            [ 1, 2, false, new \DateTime(self::DEFAULT_DATE)],
            [ 2, 4, true, new \DateTime(self::DEFAULT_DATE)],
        ];
    }

    /**
     * @dataProvider invalidInputDP
     * @expectedException \InvalidArgumentException
     */
    public function testGenerateKey_withInvalidInput_shouldThrowException(...$args)
    {
        $this->sut->generateKey(...$args);
    }

    public function invalidInputDP()
    {
        return [
            [],
            [ null],
            [ null, null],
            [ null, null, null],
            [ null, null, null, null],
            [ '1', 2, false, new \DateTime(self::DEFAULT_DATE) ],
            [ 1, '2', false, new \DateTime(self::DEFAULT_DATE) ],
            [ 1, 2, 'false', new \DateTime(self::DEFAULT_DATE) ],
            [ 1, 2, false, self::DEFAULT_DATE ],
            [ '1', '2', 'false', self::DEFAULT_DATE ],
        ];
    }

}