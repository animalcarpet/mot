<?php

namespace Dvsa\Mot\Frontend\PersonModule\Controller;

use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\TestQualityBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\PersonModule\Action\TestQualityAction;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\PersonProfileBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TestQualityInformationController extends AbstractDvsaActionController implements AutoWireableInterface
{
    private $contextProvider;
    private $testQualityBreadcrumbs;

    /** @var TestQualityAction $testQualityAction */
    private $testQualityAction;

    /** @var PersonProfileBreadcrumbs $personProfileBreadcrumbs */
    private $personProfileBreadcrumbs;

    /** @var MotIdentityProviderInterface identityProvider */
    private $identityProvider;

    public function __construct(
        TestQualityAction $testQualityAction,
        TestQualityBreadcrumbs $testQualityBreadcrumbs,
        ContextProvider $contextProvider,
        PersonProfileBreadcrumbs $personProfileBreadcrumbs,
        PersonProfileUrlGenerator $personProfileUrlGenerator,
        MotIdentityProviderInterface $identityProvider
    ) {
        $this->testQualityAction = $testQualityAction;
        $this->contextProvider = $contextProvider;
        $this->testQualityBreadcrumbs = $testQualityBreadcrumbs;
        $this->personProfileBreadcrumbs = $personProfileBreadcrumbs;
        $this->personProfileUrlGenerator = $personProfileUrlGenerator;
        $this->identityProvider = $identityProvider;
    }

    public function testQualityInformationAction()
    {
        $targetPersonId = (int) ($this->params()->fromRoute('id') ?: $this->identityProvider->getIdentity()->getUserId());
        $monthRange = (int) $this->params()->fromQuery('monthRange', 1);

        return $this->applyActionResult(
            $this->testQualityAction->execute(
                $targetPersonId,
                $monthRange,
                $this->url(),
                $this->params()->fromRoute()
            )
        );
    }
}
