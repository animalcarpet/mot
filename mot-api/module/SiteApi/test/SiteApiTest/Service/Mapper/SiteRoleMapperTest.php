<?php

namespace SiteApiTest\Service\Mapper;

use DvsaCommon\Enum\SiteBusinessRoleCode as Role;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaEntities\Entity\SiteBusinessRole;
use SiteApi\Service\Mapper\SiteBusinessRoleMapper;

/**
 * Testing that mapper returns array of site roles.
 */
class SiteRoleMapperTest extends AbstractServiceTestCase
{
    /** @var $siteRoleMapper SiteBusinessRoleMapper */
    private $siteRoleMapper;

    public function setup()
    {
        $this->siteRoleMapper = new SiteBusinessRoleMapper();
    }

    public function testManyToArray()
    {
        $roles = $this->getInput();
        $result = $this->siteRoleMapper->manyToArray($roles);
        $this->assertEquals($this->getExpectedTestData(), $result);
    }

    public function getExpectedTestData()
    {
        return
            [
                '0' => Role::SITE_MANAGER,
                '1' => Role::SITE_ADMIN,
                '2' => Role::TESTER,
            ];
    }

    /* @return SiteBusinessRole[] */
    public function getInput()
    {
        $roles = [
            (new SiteBusinessRole())->setCode(Role::SITE_MANAGER),
            (new SiteBusinessRole())->setCode(Role::SITE_ADMIN),
            (new SiteBusinessRole())->setCode(Role::TESTER),
        ];

        return $roles;
    }
}
