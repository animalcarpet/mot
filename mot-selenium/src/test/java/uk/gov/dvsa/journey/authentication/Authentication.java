package uk.gov.dvsa.journey.authentication;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.authentication.twofactorauth.RegisterCardPage;
import uk.gov.dvsa.ui.pages.authentication.twofactorauth.RegisterCardSuccessPage;
import uk.gov.dvsa.ui.pages.authentication.twofactorauth.TwoFactorPinEntryPage;
import uk.gov.dvsa.ui.pages.login.ForgottenPasswordConfirmationPage;
import uk.gov.dvsa.ui.pages.login.ForgottenPasswordUserIdPage;
import uk.gov.dvsa.ui.pages.login.LoginPage;

import java.io.IOException;

public class Authentication {
    public final SecurityCard securityCard;
    private PageNavigator pageNavigator;
    public ForgottenPasswordConfirmationPage resetPasswordViaForgotPasswordLink;

    public Authentication(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
        securityCard = new SecurityCard(pageNavigator);
    }

    public TwoFactorPinEntryPage gotoTwoFactorPinEntryPage(User user) throws IOException {

        pageNavigator.navigateToPage(user, TwoFactorPinEntryPage.PATH, TwoFactorPinEntryPage.class);

        return new TwoFactorPinEntryPage(pageNavigator.getDriver());
    }

    public void enterPinAndSubmit(String pin) {
        TwoFactorPinEntryPage tfpEntryPage = new TwoFactorPinEntryPage(pageNavigator.getDriver());
        tfpEntryPage.enterTwoFactorPin(pin);
        tfpEntryPage.clickSignIn();
    }

    public void loginWith2Fa(User user) throws IOException {
        LoginPage loginPage = pageNavigator.goToLoginPage();
        pageNavigator.getDriver().setUser(user);
        TwoFactorPinEntryPage pinPage = loginPage.login(user.getUsername(), user.getPassword(), TwoFactorPinEntryPage.class);
        pinPage.enterTwoFactorPin(user.getTwoFactorPin());
        pinPage.clickSignIn();
    }

    public void skipActivationOnRegisterCard() {
        new RegisterCardPage(pageNavigator.getDriver())
                .clickSkipActivationLink();
    }

    public boolean isValidationSummaryDisplayed() {
        return new RegisterCardPage(
                pageNavigator.getDriver())
                .isValidationSummaryBoxDisplayed();
    }

    public void registerAndSignInTwoFactorUser(User user) throws IOException {
        securityCard.activate2faCard(user, user.getSerialNumber(true), user.getTwoFactorPin(), RegisterCardSuccessPage.class)
                .continueToHomePage();
    }

    public ForgottenPasswordConfirmationPage resetPasswordViaForgotPasswordLink(User user) throws IOException {
        LoginPage loginPage = pageNavigator.goToLoginPage();
        ForgottenPasswordUserIdPage forgottenPasswordUserIdPage = loginPage.clickForgottenPasswordLink();
        forgottenPasswordUserIdPage.enterUserId(user.getUsername());

        return forgottenPasswordUserIdPage.continueToSecurityQuestionOnePage()
                .enterAnswer("Blah")
                .continueToQuestionTwoPage()
                .enterAnswer("Blah")
                .continueToConfirmationPage();
    }
}


