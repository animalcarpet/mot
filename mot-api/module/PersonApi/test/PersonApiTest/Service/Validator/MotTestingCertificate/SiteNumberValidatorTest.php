<?php

namespace PersonApiTest\Service\Validator\MotTestingCertificate;

use DvsaEntities\Entity\Site;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\SiteRepository;
use PersonApi\Service\Validator\MotTestingCertificate\SiteNumberValidator;
use DvsaCommonApi\Service\Exception\NotFoundException;

class SiteNumberValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testIsValidReturnsTrueWhenFindSite()
    {
        $siteRepository = XMock::of(SiteRepository::class);
        $siteRepository
            ->expects($this->any())
            ->method('getBySiteNumber')
            ->willReturn(new Site());

        $validator = new SiteNumberValidator($siteRepository);
        $isValid = $validator->isValid('V1234');

        $this->assertTrue($isValid);
        $this->assertEquals([], $validator->getMessages());
    }

    public function testIsValidReturnsFalseWhenFindSite()
    {
        $siteNumber = 'V1234';
        $siteRepository = XMock::of(SiteRepository::class);
        $siteRepository
            ->expects($this->any())
            ->method('getBySiteNumber')
            ->willThrowException(new NotFoundException('Some message'));

        $validator = new SiteNumberValidator($siteRepository);
        $isValid = $validator->isValid($siteNumber);

        $expectedMessage = str_replace('%value%', $siteNumber, SiteNumberValidator::ERROR_NOT_EXISTS);

        $this->assertFalse($isValid);
        $this->assertEquals([SiteNumberValidator::MSG_NOT_FOUND => $expectedMessage], $validator->getMessages());
    }
}
