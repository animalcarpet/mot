<?php
namespace Dvsa\Mot\Behat\Support\Data\Transformer;

use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;

trait UsernameToAuthenticatedUserTransformer
{
    /**
     * @Transform :user
     * @Transform :tester
     */
    public function castUsernameToAuthenticatedUser($username)
    {
        /** @var DataCollection $collection */
        $collection = SharedDataCollection::get(AuthenticatedUser::class);
        return $collection->get($username);
    }
}
