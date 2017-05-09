<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Step;

use Core\Step\AbstractStep;
use DvsaCommon\InputFilter\Event\OutcomeInputFilter;
use DvsaCommon\InputFilter\Event\RecordInputFilter;
use Event\Service\EventSessionService;
use Zend\InputFilter\InputFilter;

/**
 * Base class for AbstractEventStep.
 */
abstract class AbstractEventStep extends AbstractStep
{
    /**
     * The entity type, one of ae|site|person.
     *
     * @var string
     *
     * Expected values: "ae", "site", "person"
     */
    protected $entityType;

    /**
     * The id of the current entity we are creating an event for.
     *
     * @var int id of the entity
     */
    protected $entityId;

    /**
     * @param EventSessionService $sessionService
     */
    public function __construct(EventSessionService $sessionService, InputFilter $filter)
    {
        parent::__construct($sessionService, $filter);
    }

    /**
     * describes the steps progress in the add event process.
     *
     * Step 1 of 6
     * Step 2 of 6
     * etc
     *
     * @return string|null
     */
    public function getProgress()
    {
        return;
    }

    /**
     * @return array
     */
    public function routeParams()
    {
        return [
            'type' => $this->getEntityType(),
            'id' => $this->getEntityId(),
        ];
    }

    /**
     * @return array
     */
    protected function getFieldNameMapping()
    {
        $fieldNameMapping = [
            RecordInputFilter::FIELD_TYPE => 'Event',
            RecordInputFilter::FIELD_DATE => 'Date of event',
            OutcomeInputFilter::FIELD_OUTCOME => 'Event outcome',
        ];

        return $fieldNameMapping;
    }

    /**
     * @return mixed
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @param mixed $entityType
     */
    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param mixed $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }
}
