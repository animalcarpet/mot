<?php

namespace PersonApiTest\Input\MotTestingCertificate;

use PersonApi\Input\MotTestingCertificate\SiteNumberInput;
use PersonApi\Service\Validator\MotTestingCertificate\SiteNumberValidator;
use PersonApiTest\Input\BaseInput;
use DvsaEntities\Entity\Site;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\SiteRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;

class SiteNumberInputTest extends BaseInput
{
    /**
     * @dataProvider getSites
     */
    public function testIsValidReturnsTrueForExistingSite(Site $site = null)
    {
        $siteRepository = XMock::of(SiteRepository::class);
        $siteRepository
            ->expects($this->any())
            ->method('getBySiteNumber')
            ->willReturn($site);

        $input = new SiteNumberInput($siteRepository);
        $input->setValue('V123');

        $this->assertTrue($input->isValid());
    }

    public function getSites()
    {
        return [
            [
                new Site(),
            ],
            [
                null,
            ],
        ];
    }

    public function testIsValidReturnsFalse()
    {
        $siteNumber = '1';

        $siteRepository = XMock::of(SiteRepository::class);
        $siteRepository
            ->expects($this->any())
            ->method('getBySiteNumber')
            ->willThrowException(new NotFoundException(''));

        $input = new SiteNumberInput($siteRepository);
        $input->setValue($siteNumber);

        $this->assertFalse($input->isValid($siteNumber));
        $messages = $input->getMessages();

        $expectedMessages = [SiteNumberValidator::MSG_NOT_FOUND => str_replace('%value%', $siteNumber, SiteNumberValidator::ERROR_NOT_EXISTS)];

        $this->assertCount(count($expectedMessages), $messages);
        $this->assertEquals($expectedMessages, $messages);
    }
}
