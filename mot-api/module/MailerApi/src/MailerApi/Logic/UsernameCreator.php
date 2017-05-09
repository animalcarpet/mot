<?php

namespace MailerApi\Logic;

use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
use DvsaEntities\Entity\Person;

/**
 * All business logic for sending a username after creation is inside here.
 */
class UsernameCreator extends AbstractMailerLogic
{
    /**
     * @var Person
     */
    private $person;

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return Person
     *
     * @throws \Exception
     */
    private function getPerson()
    {
        if (!isset($this->person)) {
            throw new \Exception('No person object set');
        }

        return $this->person;
    }

    public function prepareSubject(array $data = [])
    {
        $templateData = $data;

        return $this->renderTemplate(
            'username',
            'create-subject',
            $templateData
        );
    }

    public function prepareMessage(array $data = [])
    {
        $templateData = [];
        $person = $this->getPerson();
        $data['userName'] = $person->getUserName();
        $data['firstName'] = $person->getFirstName();
        $data['signInUrl'] = $this->getBaseUrl().AccountUrlBuilderWeb::signIn();
        $templateData = array_merge($templateData, $data);

        return $this->renderTemplate(
            'username',
            'create',
            $templateData
        );
    }
}
