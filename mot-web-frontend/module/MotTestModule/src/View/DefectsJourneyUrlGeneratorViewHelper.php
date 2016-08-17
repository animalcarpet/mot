<?php

namespace Dvsa\Mot\Frontend\MotTestModule\View;

use Zend\View\Helper\AbstractHelper;
use Dvsa\Mot\Frontend\MotTestModule\Exception\RouteNotAllowedInContextException;

/**
 * Makes the DefectsJourneyUrlGenerator service available in the view layer.
 */
class DefectsJourneyUrlGeneratorViewHelper extends AbstractHelper
{
    /**
     * @var DefectsJourneyUrlGenerator
     */
    private $defectsJourneyUrlGenerator;
    /**
     * @var DefectsJourneyContextProvider
     */
    private $contextProvider;

    /**
     * DefectsJourneyUrlGeneratorViewHelper constructor.
     *
     * @param DefectsJourneyUrlGenerator $defectsJourneyUrlGenerator
     * @param DefectsJourneyContextProvider $contextProvider
     */
    public function __construct(
        DefectsJourneyUrlGenerator $defectsJourneyUrlGenerator,
        DefectsJourneyContextProvider $contextProvider
    )
    {
        $this->defectsJourneyUrlGenerator = $defectsJourneyUrlGenerator;
        $this->contextProvider = $contextProvider;
    }

    /**
     * @return $this
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Get defects journey context for current location
     *
     * @return string
     */
    public function getContext()
    {
        return $this->contextProvider->getContext();
    }

    /**
     * Get url to addDefect action
     *
     * @param int|string $defectId
     * @param string $defectType
     * @return string
     *
     * @throws RouteNotAllowedInContextException
     */
    public function toAddDefect($defectId, $defectType)
    {
        return $this->defectsJourneyUrlGenerator->toAddDefect($defectId, $defectType);
    }

    /**
     * @return string
     * @throws RouteNotAllowedInContextException
     */
    public function toAddManualAdvisory()
    {
        return $this->defectsJourneyUrlGenerator->toAddManualAdvisory();
    }

    /**
     * @param int|string $observedDefectId
     * @return string
     * @throws RouteNotAllowedInContextException
     */
    public function toEditDefect($observedDefectId)
    {
        return $this->defectsJourneyUrlGenerator->toEditDefect($observedDefectId);
    }

    /**
     * @param int|string $observedDefectId
     * @return string
     * @throws RouteNotAllowedInContextException
     */
    public function toRemoveDefect($observedDefectId)
    {
        return $this->defectsJourneyUrlGenerator->toRemoveDefect($observedDefectId);
    }

    /**
     * get "back" url from add/add manual advisory/edit/remove defect actions
     *
     * @return string
     * @throws RouteNotAllowedInContextException
     */
    public function goBack()
    {
        return $this->defectsJourneyUrlGenerator->goBack();
    }
}