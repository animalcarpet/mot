<?php

namespace DvsaMotTest\Service;

use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Exception\UnauthorisedException;

class OverdueSpecialNoticeAssertion
{
    const OVERDUE_SPECIAL_NOTICES_ERROR = 'Cannot perform a test. Test status will be active when unacknowledged notices have been read and confirmed';

    /**
     * @var array
     */
    private $overdueSpecialNotices;

    /**
     * @var array
     */
    private $authorisationsForTestingMot;

    /**
     * OverdueSpecialNoticeAssertion constructor.
     *
     * @param array $overdueSpecialNotices
     * @param array $authorisationsForTestingMot
     */
    public function __construct(array $overdueSpecialNotices, array $authorisationsForTestingMot)
    {
        $this->overdueSpecialNotices = $overdueSpecialNotices;
        $this->authorisationsForTestingMot = $authorisationsForTestingMot;
    }

    /**
     * @return bool
     */
    public function canPerformTest()
    {
        $result = false;
        foreach ($this->authorisationsForTestingMot as $authorisation) {
            $isQualified = ($authorisation['statusCode'] === AuthorisationForTestingMotStatusCode::QUALIFIED);
            $hasOverdueSpecialNotice = ((int) $this->overdueSpecialNotices[$authorisation['vehicleClassCode']] > 0) ? true : false;
            if ($isQualified && !$hasOverdueSpecialNotice) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @throws UnauthorisedException
     */
    public function assertPerformTest()
    {
        if (!$this->canPerformTest()) {
            throw new UnauthorisedException(self::OVERDUE_SPECIAL_NOTICES_ERROR);
        }
    }
}
