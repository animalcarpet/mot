<?php

namespace Application\Service;

use Zend\Session\Container;

/**
 * Class ContingencySessionManager.
 */
class ContingencySessionManager
{
    /**
     * @var Container
     */
    private $contingencySession;

    /**
     * Initalisation of the Contingency Session Manager.
     */
    public function __construct()
    {
        $this->contingencySession = new Container('contingencySession');
    }

    /**
     * This function save the dto and the contingency Id to the session.
     *
     * @param $data
     * @param $contingencyId
     */
    public function createContingencySession($data, $contingencyId)
    {
        $this->contingencySession->formData = $data;
        $this->contingencySession->contingencyId = $contingencyId;
    }

    /**
     * This function delete the contingency session.
     */
    public function deleteContingencySession()
    {
        $this->contingencySession->formData = null;
        $this->contingencySession->contingencyId = null;
    }

    /**
     * This function return the session.
     *
     * @return array|null
     */
    public function getContingencySession()
    {
        if ($this->contingencySession->contingencyId !== null) {
            return [
                'dto' => $this->contingencySession->formData,
                'contingencyId' => $this->contingencySession->contingencyId,
            ];
        }

        return null;
    }

    /**
     * This function return true if the test perform is a contingency.
     *
     * @return bool
     */
    public function isMotContingency()
    {
        return (bool) $this->contingencySession->contingencyId;
    }
}
