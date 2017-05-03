package uk.gov.dvsa.journey.authentication;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.framework.validation.ValidationSummary;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.authentication.securitycard.EnterSecurityCardAddressPage;
import uk.gov.dvsa.ui.pages.authentication.securitycard.OrderNewCardPage;
import uk.gov.dvsa.ui.pages.authentication.securitycard.ReviewSecurityCardAddressPage;
import uk.gov.dvsa.ui.pages.authentication.securitycard.card_order_report.CardOrderReportListPage;
import uk.gov.dvsa.ui.pages.authentication.securitycard.lost_or_forgotten.LostForgottenCardAlreadyOrderedPage;
import uk.gov.dvsa.ui.pages.authentication.securitycard.lost_or_forgotten.LostForgottenCardConfirmationPage;
import uk.gov.dvsa.ui.pages.authentication.securitycard.lost_or_forgotten.LostForgottenCardQuestionOnePage;
import uk.gov.dvsa.ui.pages.authentication.securitycard.lost_or_forgotten.LostForgottenCardSignInPage;
import uk.gov.dvsa.ui.pages.authentication.twofactorauth.RegisterCardPage;
import uk.gov.dvsa.ui.pages.authentication.twofactorauth.RegisterCardSuccessPage;
import uk.gov.dvsa.ui.pages.authentication.twofactorauth.TwoFactorPinEntryPage;
import uk.gov.dvsa.ui.pages.nominations.AlreadyActivatedCardPage;

import java.io.IOException;

public class SecurityCard {
    private PageNavigator pageNavigator;
    private boolean isCardDeactivationMessageDisplayed = false;

    SecurityCard(final PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public final RegisterCardSuccessPage activate2faCard(final User user) throws IOException {
        return activate2faCard(user, user.getSerialNumber(true), user.getTwoFactorPin(), RegisterCardSuccessPage.class);
    }

    public String getAlreadyActivatedCardErrorMessage() {
        return new AlreadyActivatedCardPage(pageNavigator.getDriver()).getBannerTitleText();
    }

    public final RegisterCardPage activateInvalid2faCard(final User user, String serialNumber, String pin)
        throws IOException {
        return activate2faCard(user, serialNumber, pin, RegisterCardPage.class);
    }

    public void signInWithoutSecurityCardLandingOnHomePage(User user) throws IOException {
        signInWithoutSecurityCard(user).continueToHome();
    }

  public void signInExpectingFirstQuestionLostAndForgottenNoCard(User user) throws IOException {
            signInWithoutSecurityCard(user).continueToHome();
    }

    public void signInExpectingFirstQuestionLostAndForgottenCardOrdered(User user) throws IOException {
        signInWithoutSecurityCardAfterOrder(user).continueToHome();
    }

    public void signInWithoutSecurityCardAndOrderCard(User user) throws IOException {
        OrderNewCardPage orderNewCardPage = signInWithoutSecurityCard(user).orderSecurityCard();
        isCardDeactivationMessageDisplayed = orderNewCardPage.isCardDeactivationMessageDisplayed();

        orderNewCardPage.continueToAddressPage()
            .chooseHomeAddress()
            .submitAddress(ReviewSecurityCardAddressPage.class).orderSecurityCard();
    }

    public CardOrderReportListPage goToSecurityCardOrderReportList(User user) throws IOException {
        return pageNavigator.navigateToPage(user, CardOrderReportListPage.PATH, CardOrderReportListPage.class);
    }

    public final String orderSecurityCardWithCustomAddress(User user, String addressLine1, String townOrCity, String postcode) throws IOException {
        OrderNewCardPage orderNewCardPage = pageNavigator.navigateToPage(user, OrderNewCardPage.PATH, OrderNewCardPage.class);
        isCardDeactivationMessageDisplayed = orderNewCardPage.isCardDeactivationMessageDisplayed();

        ReviewSecurityCardAddressPage reviewPage = orderNewCardPage
                        .continueToAddressPage()
                        .chooseCustomAddress()
                        .fillAddressLine1(addressLine1)
                        .fillTownOrCity(townOrCity)
                        .fillPostcode(postcode)
                        .submitAddress(ReviewSecurityCardAddressPage.class);

        return reviewPage.orderSecurityCard().orderStatusMessage();
    }

    public EnterSecurityCardAddressPage orderSecurityCardWithInvalidAddress(User user, String addressLine1, String townOrCity, String postcode) throws IOException {
        EnterSecurityCardAddressPage addressPage = pageNavigator.navigateToPage(
                user, OrderNewCardPage.PATH, OrderNewCardPage.class)
                .continueToAddressPage();

        return addressPage.chooseCustomAddress()
                .fillAddressLine1(addressLine1)
                .fillTownOrCity(townOrCity)
                .fillPostcode(postcode)
                .submitAddress(EnterSecurityCardAddressPage.class);

    }

    public String orderSecurityCardWithVTSAddress(User user) throws IOException {
        return orderCardWithAddressType(user, OrderNewCardPage.PATH, "VTS");
    }

    public String orderSecurityCardWithHomeAddress(User user) throws IOException {
        return orderCardWithAddressType(user, OrderNewCardPage.PATH,  "Home");
    }

    public String orderCardForTradeUserAsCSCO(User csco, User tradeUser) throws IOException {
        String path = String.format(OrderNewCardPage.CSCO_PATH, tradeUser.getId());
        return orderCardWithAddressType(csco, path, "Home");
    }

        public boolean isValidationSummaryDisplayed() {
            return ValidationSummary.isValidationSummaryDisplayed(pageNavigator.getDriver());
    }

    public boolean isExistingCardDeactivationMessageDisplayed() {
        return isCardDeactivationMessageDisplayed;
    }

    public LostForgottenCardConfirmationPage signInWithoutSecurityCard(User user) throws IOException {
        LostForgottenCardSignInPage signInPage =
                pageNavigator
                        .navigateToPage(user, TwoFactorPinEntryPage.PATH, TwoFactorPinEntryPage.class)
                        .clickLostForgottenLink();

        return signInPage.continueToSecurityQuestionOnePage()
                .enterAnswer("Blah")
                .continueToQuestionTwoPage()
                .enterAnswer("Blah")
                .continueToConfirmationPage();
    }

    public LostForgottenCardConfirmationPage signInWithoutSecurityCardAfterOrder(User user) throws IOException {
       LostForgottenCardQuestionOnePage questionOnePage =
               pageNavigator.navigateToPage(user, LostForgottenCardAlreadyOrderedPage.PATH, LostForgottenCardQuestionOnePage.class);

        return questionOnePage
                .enterAnswer("Blah")
                .continueToQuestionTwoPage()
                .enterAnswer("Blah")
                .continueToConfirmationPage();
    }

    protected <T extends Page> T activate2faCard(User user, String serialNumber, String pin, Class<T> returnPage)
        throws IOException {
        RegisterCardPage registerCardPage = pageNavigator.navigateToPage(user, RegisterCardPage.PATH, RegisterCardPage.class);
        registerCardPage.enterSerialNumber(serialNumber);
        registerCardPage.enterPin(pin);

        registerCardPage.continueButton();
        return MotPageFactory.newPage(pageNavigator.getDriver(), returnPage);
    }

    private String orderCardWithAddressType(User user, String path, String addressType) throws IOException {
        OrderNewCardPage orderNewCardPage = pageNavigator.navigateToPage(user, path, OrderNewCardPage.class);
        isCardDeactivationMessageDisplayed = orderNewCardPage.isCardDeactivationMessageDisplayed();

        ReviewSecurityCardAddressPage reviewPage =
            addressType.equalsIgnoreCase("VTS") ? orderNewCardPage.continueToAddressPage().chooseVTSAddress()
                .submitAddress(ReviewSecurityCardAddressPage.class) : orderNewCardPage.continueToAddressPage().chooseHomeAddress()
                .submitAddress(ReviewSecurityCardAddressPage.class);

        return reviewPage.orderSecurityCard().orderStatusMessage();
    }
}
