<?php

namespace DashboardTest\Data;

use Dashboard\Data\ApiDashboardResource;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\HttpRestJson\Client;

/**
 * unit tests for ApiDashboardResource
 * test data (mock for \Dashboard\Data\ApiDashboardResource)
 */
class ApiDashboardResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiDashboardResource
     */
    private $resource;

    public function setUp()
    {
        $client = \DvsaCommonTest\TestUtils\XMock::of(Client::class);

        // get should be called only once and  cached
        $client->expects($this->once())->method('get')->will($this->returnValue(['data' => [rand()]]));

        $this->resource = new ApiDashboardResource($client);
    }

    public function testGetWithCachedData()
    {
        $result1 = $this->resource->get(1);
        $result2 = $this->resource->get(1);
        $this->assertSame($result1, $result2);
        $this->assertEquals($result1[0], $result2[0]);
    }

    public static function getTestDataForAedm($aeCount = 1, $data = null)
    {
        $user = self::getTestDataForUser($data);
        $user['hero'] = 'aedm';
        $user['authorisedExaminers'] = self::getAe($aeCount, $data);

        return $user;
    }

    private static $aeCounter = 1;
    public static function getAe($aeCount = 1, $data = null)
    {
        $aeList = [];

        while ($aeCount--) {
            $aeList[] = [
                'id'            => self::$aeCounter++,
                'reference'     => 'AE000001',
                'name'          => 'Coca Cola Motors',
                'tradingAs'     => 'Pepsi Bikes',
                'managerId'     => 1,
                'slots'         => isset($data['slots']) ? $data['slots'] : 0,
                'slotsWarnings' => 12,
                'sites'         => self::getVts(isset($data['vtsCount']) ? $data['vtsCount'] : 1),
                'position'      => OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
            ];
        }

        return $aeList;
    }

    private static $siteCounter = 1;
    public static function getVts($vtsCount = 1)
    {
        $vtsList = [];

        while ($vtsCount--) {
            $vtsList[] = [
                'id'         => self::$siteCounter++,
                'name'       => 'My Garage',
                'siteNumber' => 'V123443',
                'positions'  => [],
            ];
        }

        return $vtsList;
    }

    public static function getTestDataForUser($data = null)
    {
        return [
            'hero'                => 'user',
            'authorisedExaminers' => [],
            'specialNotice'       => [
                'unreadCount'    => isset($data['unreadCount']) ? $data['unreadCount'] : 1,
                'daysLeftToView' => isset($data['daysLeftToView']) ? $data['daysLeftToView'] : 3,
                'overdueCount'   => isset($data['overdueCount']) ? $data['overdueCount'] : 0,
            ],
            "overdueSpecialNotices" => array_combine(VehicleClassCode::getAll(), array_fill(0, count(VehicleClassCode::getAll()), 0)),
            'notifications'       => [],
            'unreadNotificationsCount' => 0,
            'sites'               => [],
            'inProgressTestNumber' => '123456789012',
            'inProgressDemoTestNumber' => '210987654321',
            'inProgressTestTypeCode' => MotTestTypeCode::NORMAL_TEST,
            'testedVehicleId' => 17,
            'isTechnicalAdvicePresent' => true,
        ];
    }

    public static function getTestDataForVehicleExaminer()
    {
        return [
            'hero' => 'vehicle-examiner',
            'authorisedExaminers' => [],
            'specialNotice'       => [
                'unreadCount'    => 1,
                'daysLeftToView' => 3,
                'overdueCount'   => 0,
            ],
            "overdueSpecialNotices" => array_combine(VehicleClassCode::getAll(), array_fill(0, count(VehicleClassCode::getAll()), 0)),
            'notifications'       => [],
            'unreadNotificationsCount' => 0,
            'sites'               => [],
            'inProgressTestNumber' => '123456789012',
            'inProgressDemoTestNumber' => null,
            'inProgressNonMotTestNumber' => '210987654321',
            'inProgressTestTypeCode' => MotTestTypeCode::TARGETED_REINSPECTION,
            'testedVehicleId' => 17,
            'isTechnicalAdvicePresent' => true,
        ];
    }
}
