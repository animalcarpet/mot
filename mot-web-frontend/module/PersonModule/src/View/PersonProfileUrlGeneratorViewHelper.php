<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModule\View;

use Zend\View\Helper\AbstractHelper;

/**
 * Makes the PersonProfileUrlGenerator service available in the view layer.
 */
class PersonProfileUrlGeneratorViewHelper extends AbstractHelper
{
    /**
     * @var PersonProfileUrlGenerator
     */
    private $personProfileUrlGenerator;

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    /**
     * PersonProfileUrlGeneratorViewHelper constructor.
     *
     * @param PersonProfileUrlGenerator $personProfileUrlGenerator
     * @param ContextProvider           $contextProvider
     */
    public function __construct(PersonProfileUrlGenerator $personProfileUrlGenerator, ContextProvider $contextProvider)
    {
        $this->personProfileUrlGenerator = $personProfileUrlGenerator;
        $this->contextProvider = $contextProvider;
    }

    /**
     * @return string
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->contextProvider->getContext();
    }

    /**
     * @return string
     */
    public function toPersonProfile()
    {
        return $this->personProfileUrlGenerator->toPersonProfile();
    }

    /**
     * @param string     $subRouteName
     * @param array      $params       Parameters to use in url generation, if any
     * @param array|bool $options      RouteInterface-specific options to use in url generation, if any
     *
     * @return string
     */
    public function fromPersonProfile($subRouteName, $params = [], $options = [])
    {
        return $this->personProfileUrlGenerator->fromPersonProfile($subRouteName, $params, $options);
    }
}
