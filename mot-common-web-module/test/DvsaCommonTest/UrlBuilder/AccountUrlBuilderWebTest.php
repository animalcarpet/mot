<?php

namespace DvsaCommonTest\UrlBuilder;

use DvsaCommon\UrlBuilder\AbstractUrlBuilder;
use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;

class AccountUrlBuilderWebTest extends \PHPUnit_Framework_TestCase
{
    const PERSON_ID = 1;
    const QUESTION_ID = 1;
    const TOKEN = 'unit_token1234';

    public function test()
    {
        $base = '/forgotten-password';
        $this->checkUrl(AccountUrlBuilderWeb::forgottenPassword(), $base);
        $this->checkUrl(AccountUrlBuilderWeb::forgottenPasswordAuthenticated(), $base . '/authenticated');
        $this->checkUrl(AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated(), $base . '/not-authenticated');
        $this->checkUrl(AccountUrlBuilderWeb::forgottenPasswordConfirmation(), $base . '/confirmation-email');
        $this->checkUrl(
            AccountUrlBuilderWeb::forgottenPasswordSecurityQuestion(self::PERSON_ID, self::QUESTION_ID),
            $base . '/security-question/' . self::PERSON_ID . '/' . self::QUESTION_ID
        );
        $this->checkUrl(
            AccountUrlBuilderWeb::resetPasswordByToken(self::TOKEN),
            $base . '/reset/' . self::TOKEN
        );
        $this->checkUrl(AccountUrlBuilderWeb::forgottenPasswordEmailNotFound(), $base . '/email-not-found');

        $base = '/account';
        $this->checkUrl(AccountUrlBuilderWeb::account(), $base);

        $base = '/account/claim';
        $this->checkUrl(AccountUrlBuilderWeb::claimEmailAndPassword(), $base .'/confirm-email-and-password');
        $this->checkUrl(AccountUrlBuilderWeb::claimSecurityQuestions(), $base .'/set-security-question');
        $this->checkUrl(AccountUrlBuilderWeb::claimReset(), $base .'/reset');
    }

    private function checkUrl(AbstractUrlBuilder $urlBuilder, $expectUrl)
    {
        $this->assertEquals($expectUrl, $urlBuilder->toString());
        $this->assertInstanceOf(AccountUrlBuilderWeb::class, $urlBuilder);
    }
}
