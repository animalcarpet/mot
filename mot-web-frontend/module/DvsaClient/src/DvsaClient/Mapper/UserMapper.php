<?php

namespace DvsaClient\Mapper;

use DvsaClient\Entity\User;
use DvsaCommon\UrlBuilder\UrlBuilder;

/**
 * Class UserMapper.
 */
class UserMapper extends Mapper
{
    protected $entityClass = User::class;

    public function fetchAllLinkedUsersForOrganisation($managerId, $offset = 0, $limit = 15)
    {
        $url = 'organisation/'.$managerId.'/position'.$this->getPaginationUrlString($offset, $limit);
        $usersAndRoles = $this->client->get($url);

        return $this->hydrateArrayOfEntities($usersAndRoles['data']);
    }

    public function fetchAll($offset = 0, $limit = 15)
    {
        //$url = 'api/user?offset=' . $offset . '&limit=' . $limit;
        $url = 'user';
        $users = $this->client->get($url);
        $stack = [];
        foreach ($users['data'] as $user) {
            $obj = new User();
            $this->getHydrator()->hydrate($user, $obj);
            $stack[] = $obj;
        }

        return $stack;
    }

    /**
     * @param $username
     *
     * @return User
     */
    public function getByUsername($username)
    {
        $user = $this->client->get(UrlBuilder::user($username));

        $obj = $this->doHydration($user['data']);

        return $obj;
    }
}
