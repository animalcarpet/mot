<?php

namespace Dvsa\Mot\Frontend\TestQualityInformation\Breadcrumbs;

use Core\Routing\ProfileRoutes;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Application\Data\ApiPersonalDetails;
use Dashboard\Model\PersonalDetails;
use Zend\View\Helper\Url;

class TesterTqiBreadcrumbs implements AutoWireableInterface
{
    private $apiPersonalDetails;
    private $contextProvider;
    private $url;

    public function __construct(
        ApiPersonalDetails $apiPersonalDetails,
        ContextProvider $contextProvider,
        Url $url
    ) {
        $this->apiPersonalDetails = $apiPersonalDetails;
        $this->contextProvider = $contextProvider;
        $this->url = $url;
    }

    public function getBreadcrumbs($testerId)
    {
        $breadcrumbs = [];
        if ($this->contextProvider->isYourProfileContext()) {
            $breadcrumbs['Your profile'] = ProfileRoutes::of($this->url)->yourProfile();
        } else {
            $personalDetails = new PersonalDetails($this
                ->apiPersonalDetails
                ->getPersonalDetailsData($testerId));

            $breadcrumbs[$personalDetails->getFullName()] = ProfileRoutes::of($this->url)->userSearch($testerId);
        }

        $breadcrumbs['Test quality information'] = null;

        return $breadcrumbs;
    }
}
