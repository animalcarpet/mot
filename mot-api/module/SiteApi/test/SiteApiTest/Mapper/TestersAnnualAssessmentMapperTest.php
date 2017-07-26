<?php

namespace SiteApiTest\Mapper;

use DvsaCommon\ApiClient\Site\Dto\GroupAssessmentListItem;
use SiteApi\Mapper\TestersAnnualAssessmentMapper;

class TestersAnnualAssessmentMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider arrayResultDataProvider
     */
    public function testMapperMapsProperToDto($firstName, $middleName, $familyName, $username, $userId, $dateAwarded){
        $dto = (new GroupAssessmentListItem())
            ->setUserMiddleName($middleName)->setUserFamilyName($familyName)->setUserFirstName($firstName)
            ->setUsername($username)->setUserId($userId)
            ->setDateAwarded($dateAwarded ? new \DateTime($dateAwarded) : null);

        $mapper = new TestersAnnualAssessmentMapper();
        $dtos = $mapper->mapToDto([[
            'username' => $username,
            'firstName' => $firstName,
            'middleName' => $middleName,
            'familyName' => $familyName,
            'id' => $userId,
            'dateAwarded' => $dateAwarded,
        ]]);

        $this->assertEquals($dto, $dtos[0]);
    }

    public function arrayResultDataProvider()
    {
        return [
            ["first1", "middle1", "family1", "username1", 123123, "2001-01-01",],
            ["first2", "middle2", "family2", "username2", 22222, null,],
        ];
    }
}