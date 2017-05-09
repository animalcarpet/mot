<?php

namespace Organisation\Form;

use DvsaClient\ViewModel\AbstractFormModel;
use Zend\Stdlib\Parameters;

class AeUnlinkSiteForm extends AbstractFormModel
{
    const FIELD_STATUS = 'status';

    /**
     * @var string
     */
    private $status;
    /**
     * @var string[]
     */
    private $statuses;

    public function fromPost(Parameters $data)
    {
        $this->status = $data->get(self::FIELD_STATUS);

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \string[]
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * @return $this
     */
    public function setStatuses(array $statuses)
    {
        $this->statuses = $statuses;

        return $this;
    }

    public function isValid()
    {
        return true;
    }
}
