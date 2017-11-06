<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Service;

use Dvsa\Mot\Frontend\MotTestModule\Service\RfrCache;
use DvsaCommon\Cache\CacheKeyGenerator;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Zend\Cache\Storage\StorageInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use DvsaApplicationLogger\Log\Logger;

class RfrCacheTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_KEY = 'default_cache_key';
    const DEFAULT_CACHED_ITEM = ['abc' =>'def'];
    const DEFAULT_DATE = '2017-01-01';

    /** @var FeatureToggles | MockObj */
    private $fetureToggles;
    /** @var  StorageInterface | MockObj*/
    private $storage;
    /** @var  CacheKeyGenerator | MockObj */
    private $keyGenerator;
    /** @var  Logger | MockObj */
    private $logger;

    /** @var  RfrCache */
    private $sut;

    public function setUp()
    {
        $this->fetureToggles = XMock::of(FeatureToggles::class);
        $this->storage = XMock::of(StorageInterface::class);
        $this->keyGenerator = XMock::of(CacheKeyGenerator::class);
        $this->logger = XMock::of(Logger::class);

        $this->sut = new RfrCache(
            $this->storage,
            $this->keyGenerator,
            $this->fetureToggles,
            $this->logger
        );
    }

    /**
     * @param bool $value
     */
    private function withFeatureToggle($value = true)
    {
        $this->fetureToggles
            ->expects($this->once())
            ->method('isEnabled')
            ->with(FeatureToggle::RFR_CACHE)
            ->willReturn($value);
    }


    private function errorMessageShouldBeLogged()
    {
        $this->logger
            ->expects($this->once())
            ->method('warn')
            ->with($this->anything());
    }

    /**
     * @param string $key
     * @param bool $shouldThrow
     */
    private function withKeyGenerator(string $key = self::DEFAULT_KEY, bool $shouldThrow = false)
    {
        /** @var \PHPUnit_Framework_MockObject_Builder_InvocationMocker $mocker */
        $mocker = $this->keyGenerator
            ->expects($this->once())
            ->method('generateKey')
            ->with($this->anything());

        if ($shouldThrow){
            $mocker->willThrowException(new \InvalidArgumentException());
        }
        else {
            $mocker->willReturn($key);
        }

    }

    /**
     * @param $value
     * @param string $key
     * @param bool $shouldSucceed
     * @param bool $shouldThrow
     */
    private function withSetItemOnStorage(
        $value = self::DEFAULT_CACHED_ITEM,
        string $key = self::DEFAULT_KEY,
        bool $shouldSucceed = true,
        bool $shouldThrow = false
    )
    {
        /** @var \PHPUnit_Framework_MockObject_Builder_InvocationMocker $mocker */
        $mocker = $this->storage
            ->expects($this->once())
            ->method('setItem')
            ->with($key, $value);

        if ($shouldThrow){
            $mocker->willThrowException(new \Exception());
        }
        else {
            $mocker->willReturn($shouldSucceed);
        }
    }

    /**
     * @param string $key
     * @param bool $shouldSucceed
     * @param bool $shouldThrow
     */
    private function withGetItemOnStorage(
        string $key = self::DEFAULT_KEY,
        bool $shouldSucceed = true,
        bool $shouldThrow = false
    )
    {
        /** @var \PHPUnit_Framework_MockObject_Builder_InvocationMocker $mocker */
        $mocker = $this->storage
            ->expects($this->once())
            ->method('getItem')
            ->with($key);

        if ($shouldThrow){
            $mocker->willThrowException(new \Exception());
        }
        else {
            $mocker->willReturn($shouldSucceed ? self::DEFAULT_CACHED_ITEM : null);
        }
    }

    /**
     * @return array|null
     */
    private function callGetItemWithDefaultParams()
    {
        $vehicleClass = 1;
        $categoryId = 2;
        $isVe = false;
        $date = new \DateTime(self::DEFAULT_DATE);

        return $this->sut->getItem($vehicleClass, $categoryId, $isVe, $date);
    }

    /**
     * @return bool
     */
    private function callSetItemWithDefaultParams()
    {
        $vehicleClass = 1;
        $categoryId = 2;
        $isVe = false;
        $date = new \DateTime(self::DEFAULT_DATE);

        return $this->sut->setItem($vehicleClass, $categoryId, $isVe, $date, self::DEFAULT_CACHED_ITEM);
    }

    public function testGetItem_withValidInput_AndCacheHit_shouldReturnValue()
    {
        $this->withKeyGenerator();
        $this->withGetItemOnStorage();

        $result = $this->callGetItemWithDefaultParams();

        $this->assertEquals(self::DEFAULT_CACHED_ITEM, $result);
    }

    public function testGetItem_withValidInput_AndCacheMiss_shouldReturnNull()
    {
        $this->withKeyGenerator();
        $this->withGetItemOnStorage(self::DEFAULT_KEY, false);

        $result = $this->callGetItemWithDefaultParams();

        $this->assertNull($result);
    }

    public function testGetItem_withErrorOnKeyGenerator_shouldReturnNull()
    {
        $this->withKeyGenerator(self::DEFAULT_KEY, true);
        $this->errorMessageShouldBeLogged();

        $result = $this->callGetItemWithDefaultParams();

        $this->assertNull($result);
    }

    public function testGetItem_withErrorOnStorage_shouldReturnNull()
    {
        $this->withKeyGenerator();
        $this->withGetItemOnStorage(self::DEFAULT_KEY, false, true);
        $this->errorMessageShouldBeLogged();

        $result = $this->callGetItemWithDefaultParams();

        $this->assertNull($result);
    }

    public function testSetItem_withValidInput_shouldReturnTrue()
    {
        $this->withKeyGenerator();
        $this->withSetItemOnStorage();

        $result = $this->callSetItemWithDefaultParams();

        $this->assertTrue($result);
    }

    public function testSetItem_withErrorOnKeyGenerator_shouldReturnFalse()
    {
        $this->withKeyGenerator(self::DEFAULT_KEY, true);
        $this->errorMessageShouldBeLogged();

        $result = $this->callSetItemWithDefaultParams();

        $this->assertFalse($result);
    }


    public function testSetItem_withErrorOnStorage_shouldReturnFalse()
    {
        $this->withKeyGenerator();
        $this->withSetItemOnStorage(self::DEFAULT_CACHED_ITEM, self::DEFAULT_KEY, false, true);
        $this->errorMessageShouldBeLogged();

        $result = $this->callSetItemWithDefaultParams();

        $this->assertFalse($result);
    }

    /**
     * @dataProvider ftValueDP
     * @param $ftValue
     */
    public function testIsEnabled_shouldReturnTrue($ftValue)
    {
        $this->withFeatureToggle($ftValue);

        $result = $this->sut->isEnabled();

        $this->assertEquals($ftValue, $result);
    }

    public function ftValueDP()
    {
        return [
          [true],
          [false],
        ];
    }
}