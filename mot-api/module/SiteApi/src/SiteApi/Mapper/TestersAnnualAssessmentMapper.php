<?php


namespace SiteApi\Mapper;


use DvsaCommon\ApiClient\Site\Dto\GroupAssessmentListItem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TestersAnnualAssessmentMapper implements AutoWireableInterface
{
    /**
     * @param $userList
     * @return GroupAssessmentListItem[]
     */
    public function mapToDto($userList)
    {
        $dtoList = [];

        foreach ($userList as $userData) {
            $listItem = new GroupAssessmentListItem();
            $dateAwarded = $userData['dateAwarded'] ? new \Datetime($userData['dateAwarded']): null;
            $listItem->setDateAwarded($dateAwarded)
                ->setUserId($userData['id'])
                ->setUsername($userData['username'])
                ->setUserFirstName($userData['firstName'])
                ->setUserMiddleName($userData['middleName'])
                ->setUserFamilyName($userData['familyName']);

            $dtoList[] = $listItem;
        }

        return $dtoList;
    }
}