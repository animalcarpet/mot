<?php

namespace IntegrationApi\MotTestCommon\Mapper;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaEntities\Entity\Site;

/**
 * Class AbstractMotTestMapper.
 */
abstract class AbstractMotTestMapper
{
    protected function extractPhoneNumber(Site $vts)
    {
        $contact = $vts->getBusinessContact();

        if (!$contact) {
            return null;
        }

        $phone = $contact->getDetails()->getPrimaryPhone();

        if (!$phone) {
            return null;
        }

        return $phone->getNumber();
    }

    protected function returnFormattedDateOrNull($date)
    {
        return null === $date ? null : DateTimeApiFormat::date($date);
    }
}
