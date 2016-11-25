<?php
namespace DvsaMotTestTest\View\VehicleSearchResult;

use DvsaApplicationLogger\Log\Logger;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Constants\VehicleSearchSource;
use DvsaMotTest\Model\VehicleSearchResult;
use DvsaMotTest\View\VehicleSearchResult\TrainingTestUrlTemplate;
use Zend\Mvc\Controller\Plugin\Url;
use InvalidArgumentException;

class TrainingTestUrlTemplateTest extends \PHPUnit_Framework_TestCase
{

    const VALIDATOR_VEHICLE_REQUIRED = 'Vehicle is required';
    const VALIDATOR_INVALID_PARAMETER_ERROR = 'Vehicle ID and/or Search parameters are missing';

    /** @var TrainingTestUrlTemplate */
    private $trainingTestUrlTemplate;

    /** @var bool */
    private $noRegistration;

    /** @var Logger */
    private $logger;

    public function setUp()
    {
        $urlHelper = new Url();
        $this->noRegistration = 0;

        $this->trainingTestUrlTemplate = new TrainingTestUrlTemplate($this->noRegistration, $urlHelper);

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $this->logger = $this
            ->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceManager->setService('Application\Logger', $this->logger);
    }

    public function testPassingNoVehicleArrayToGetUrlReturnsException()
    {
        $this->setExpectedException(InvalidArgumentException::class, self::VALIDATOR_VEHICLE_REQUIRED);

        $this->trainingTestUrlTemplate->getUrl([]);
    }

    public function testPassingNoRequiredVehicleParametersReturnsException()
    {
        $this->setExpectedException(InvalidArgumentException::class, self::VALIDATOR_INVALID_PARAMETER_ERROR);

        $this->trainingTestUrlTemplate->getUrl([ 'invalidKey' ]);
    }

    public function testPassingOneButNotAllRequiredVehicleParametersReturnsException()
    {
        $this->setExpectedException(InvalidArgumentException::class, self::VALIDATOR_INVALID_PARAMETER_ERROR);

        $vehicle = [
            'id' => '1',
            'invalidKey' => '2',
            'searchVrm' => 'test'
        ];

        $this->trainingTestUrlTemplate->getUrl($vehicle);
    }

    public function testPassingNoParametersToGetStartMotTestUrlReturnsException()
    {
        $this->setExpectedException(InvalidArgumentException::class, self::VALIDATOR_VEHICLE_REQUIRED);
        $this->getStartMotTestUrl([]);
    }

    public function testPassingToGetStartMotTestUrlNoRequiredVehicleParametersReturnsException()
    {
        $this->setExpectedException(InvalidArgumentException::class, self::VALIDATOR_INVALID_PARAMETER_ERROR);
        $this->getStartMotTestUrl([ 'invalidKey' ]);
    }

    public function testPassingOneButNotAllRequiredVehicleParametersToGetStartMotTestUrlReturnsException()
    {
        $this->setExpectedException(InvalidArgumentException::class, self::VALIDATOR_INVALID_PARAMETER_ERROR);

        $vehicle = [
            'id' => '1',
            'invalidKey' => '2',
            'searchVrm' => 'test'
        ];

        $this->getStartMotTestUrl($vehicle);
    }

    /**
     * @param array $searchParams
     * @return string
     * @throws \Exception
     */
    private function getStartMotTestUrl(array $searchParams)
    {
        $paramObfuscator = XMock::of(ParamObfuscator::class);
        $vehicleSearchSource = XMock::of(VehicleSearchSource::class);

        $vehicle = new VehicleSearchResult($paramObfuscator, $vehicleSearchSource, $this->logger);

        return $this->trainingTestUrlTemplate->getStartMotTestUrl($vehicle, $searchParams);
    }

}
