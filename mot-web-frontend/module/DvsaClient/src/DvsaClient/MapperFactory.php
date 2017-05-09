<?php

namespace DvsaClient;

use DvsaClient\Mapper\AccountMapper;
use DvsaClient\Mapper\AuthorisedExaminerPrincipalMapper;
use DvsaClient\Mapper\DemoTestAssessmentMapper;
use DvsaClient\Mapper\EquipmentMapper;
use DvsaClient\Mapper\EquipmentModelMapper;
use DvsaClient\Mapper\EventMapper;
use DvsaClient\Mapper\MotTestInProgressMapper;
use DvsaClient\Mapper\MotTestLogMapper;
use DvsaClient\Mapper\OrganisationMapper;
use DvsaClient\Mapper\OrganisationPositionMapper;
use DvsaClient\Mapper\OrganisationRoleMapper;
use DvsaClient\Mapper\OrganisationSitesMapper;
use DvsaClient\Mapper\PersonMapper;
use DvsaClient\Mapper\RoleMapper;
use DvsaClient\Mapper\SecurityQuestionMapper;
use DvsaClient\Mapper\SiteMapper;
use DvsaClient\Mapper\SitePositionMapper;
use DvsaClient\Mapper\SiteRoleMapper;
use DvsaClient\Mapper\TesterQualificationStatusMapper;
use DvsaClient\Mapper\UserAdminMapper;
use DvsaClient\Mapper\UserMapper;
use DvsaClient\Mapper\VehicleMapper;
use DvsaClient\Mapper\VehicleTestingStationOpeningHoursMapper;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use Zend\Http\Client;

/**
 * Class MapperFactory.
 *
 * @property-read AccountMapper                                      $Account
 * @property-read DemoTestAssessmentMapper                           $DemoTestAssessment
 * @property-read EquipmentMapper                                    $Equipment
 * @property-read EquipmentModelMapper                               $EquipmentModel
 * @property-read EventMapper                                        $Event
 * @property-read MotTestInProgressMapper                            $MotTestInProgress
 * @property-read MotTestLogMapper                                   $MotTestLog
 * @property-read OrganisationMapper                                 $Organisation
 * @property-read OrganisationPositionMapper                         $OrganisationPosition
 * @property-read OrganisationRoleMapper                             $OrganisationRole
 * @property-read OrganisationSitesMapper                            $OrganisationSites
 * @property-read PersonMapper                                       $Person
 * @property-read AuthorisedExaminerPrincipalMapper                  $AuthorisedExaminerPrincipal
 * @property-read RoleMapper                                         $Role
 * @property-read SecurityQuestionMapper                             $SecurityQuestion
 * @property-read SitePositionMapper                                 $SitePosition
 * @property-read SiteRoleMapper                                     $SiteRole
 * @property-read TesterQualificationStatusMapper                    $TesterQualificationStatus
 * @property-read UserAdminMapper                                    $UserAdmin
 * @property-read UserMapper                                         $User
 * @property-read VehicleMapper                                      $Vehicle
 * @property-read SiteMapper                                         $Site
 * @property-read VehicleTestingStationOpeningHoursMapper            $VehicleTestingStationOpeningHours
 */
class MapperFactory
{
    const ORGANISATION = 'Organisation';
    const ORGANISATION_POSITION = 'OrganisationPosition';
    const ORGANISATION_ROLE = 'OrganisationRole';
    const ORGANISATION_SITE = 'OrganisationSites';
    const PERSON = 'Person';
    const AUTHORISED_EXAMINER_PRINCIPAL = 'AuthorisedExaminerPrincipal';
    const ROLE = 'Role';
    const SITE = 'Site';
    const SITE_ROLE = 'SiteRole';
    const USER = 'User';
    const VEHICLE = 'Vehicle';
    const EVENT = 'Event';
    const USER_ADMIN = 'UserAdmin';
    const ACCOUNT = 'Account';
    const SECURITY_QUESTION = 'SecurityQuestion';
    const MOT_TEST_LOG = 'MotTestLog';
    const EQUIPMENT = 'Equipment';
    const MOT_TEST_IN_PROGRESS = 'MotTestInProgress';

    protected $client;

    public function __construct(HttpRestJsonClient $client)
    {
        $this->client = $client;
    }

    public function __get($class)
    {
        $fullclass = __NAMESPACE__.'\\Mapper\\'.ucfirst($class).'Mapper';
        if (class_exists($fullclass)) {
            return new $fullclass($this->client);
        }

        throw new \RuntimeException('Class not found: '.$fullclass);
    }
}
