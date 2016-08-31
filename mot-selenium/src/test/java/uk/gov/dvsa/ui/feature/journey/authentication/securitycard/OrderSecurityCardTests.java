package uk.gov.dvsa.ui.feature.journey.authentication.securitycard;

import org.testng.annotations.Test;
import ru.yandex.qatools.allure.annotations.Features;
import ru.yandex.qatools.allure.annotations.Issue;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.Matchers.is;

public class OrderSecurityCardTests extends DslTest {

    @Test(testName = "2fa", groups = {"BVT"})
    public void orderNewSecurityCard() throws IOException {

        step("Given I am a 2fa authenticated user");
        User twoFactorUser = userData.createTester(siteData.createSite().getId());
        motUI.authentication.securityCard.activate2faCard(twoFactorUser).logOut(twoFactorUser);

        step("And I complete the sign in journey without a card");
        motUI.authentication.securityCard.signInWithoutSecurityCard(twoFactorUser);

        step("When I order a card with valid address");
        String message = motUI.authentication.securityCard.orderSecurityCardWithCustomAddress(
                twoFactorUser, "No. 10 Downing Street", "London", "EC1 5AA");

        step("Then my card is ordered successfully");
        assertThat("Card Order was Successful", message, containsString("Your security card has been ordered"));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    public void cscoOrderCardForTradeUser() throws IOException {
        step("Given I order a card for a trade user as CSCO");
        String result = motUI.authentication.securityCard
            .orderCardForTradeUserAsCSCO(userData.createCSCO(), userData.createUserWithoutRole());

        step("Then my card is ordered successfully");
        assertThat("Card Order was Successful", result, containsString("Your security card has been ordered"));
    }

    @Issue("BL-2738")
    @Features("New TESTER Sign In - PROMPT")
    @Test(testName = "2fa", groups = {"BVT"})
    public void orderSecurityCardPromptIsDisplayedForDemoTestUserWhoHasNotOrderedSecurityCard() throws IOException {
        step("Given I have a status of Demo Test Needed with no card ordered");
        User demoTester = userData.createUserWithoutRole();
        qualificationDetailsData.createQualificationCertificateForGroupA(
            demoTester, "1234123412341234", "2016-04-01", siteData.createSite().getSiteNumber());

        step("When I sign in to my account");
        String pageText = motUI.loginExpecting2faOrderPromtPage(demoTester).getText();

        step("Then I should a page prompt to order card");
        assertThat("Order Card prompt page is displayed", pageText,
            containsString("You need a security card to access the full MOT testing"));
    }

    @Issue("BL-2738")
    @Features("New TESTER Sign In - PROMPT")
    @Test(testName = "2fa", groups = {"BVT"})
    public void activateSecurityCardPromptIsDisplayedForDemoTestUserWhoHasOrderedSecurityCard() throws IOException {
        step("Given I have a status of Demo Test Needed with card ordered");
        User demoTester = userData.createUserWithoutRole();
        qualificationDetailsData.createQualificationCertificateForGroupA(
            demoTester, "1234123412341234", "2016-04-01", siteData.createSite().getSiteNumber()
        );
        motUI.authentication.securityCard.orderSecurityCardWithHomeAddress(demoTester);
        motUI.logout(demoTester);

        step("When I sign in to my account");
        String pageText = motUI.loginExpecting2faActivatePromtPage(demoTester).getText();

        step("Then I should a page prompt to activate card");
        assertThat("Activate Card prompt page is displayed", pageText,
            containsString("You can only access the full MOT testing service with an activated security card."));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    public void cardDeactivationMessageIsDisplayedForUserWithActiveCard() throws IOException {

        step("Given I am a 2fa authenticated user");
        User twoFactorUser = userData.createTester(siteData.createSite().getId());

        step("And I want to authenticate without a card");
        motUI.authentication.gotoTwoFactorPinEntryPage(twoFactorUser);

        step("And I complete the sign in journey");
        motUI.authentication.securityCard.signInWithoutSecurityCardAndOrderCard(twoFactorUser);

        step("Then the card deactivation message should be displayed");
        assertThat(motUI.authentication.securityCard.isExistingCardDeactivationMessageDisplayed(), is(true));
    }

    @Test(testName = "2fa", groups = {"BVT"} )
    public void validationSummaryIsDisplayedForIncorrectCustomAddress()
            throws IOException {

        step("GIVEN I am a 2fa authenticated user");
        User twoFactorUser = userData.createTester(siteData.createSite().getId());
        motUI.authentication.securityCard.activate2faCard(twoFactorUser).logOut(twoFactorUser);

        step("And I complete the sign in journey");
        motUI.authentication.securityCard.signInWithoutSecurityCard(twoFactorUser);

        step("WHEN I order a card with invalid address");
        motUI.authentication.securityCard.orderSecurityCardWithInvalidAddress(twoFactorUser, "", "", "ng1 6lp");

        step("THEN I should remain in enter address page");
        assertThat(motUI.authentication.securityCard.isValidationSummaryDisplayed(), is(true));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    public void orderNewSecurityCardWithVTSAddress() throws IOException {

        step("GIVEN I am a 2fa authenticated user");
        User twoFactorUser = userData.createTester(siteData.createSite().getId());
        motUI.authentication.securityCard.activate2faCard(twoFactorUser).logOut(twoFactorUser);

        step("And I complete the sign in journey");
        motUI.authentication.securityCard.signInWithoutSecurityCard(twoFactorUser);

        step("WHEN I order a card with valid VTS address");
        String message = motUI.authentication.securityCard.orderSecurityCardWithVTSAddress(twoFactorUser);

        step("THEN my card is ordered successfully");
        assertThat("Card Order was Successful", message, containsString("Your security card has been ordered"));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    public void orderNewSecurityCardWithHomeAddress() throws IOException {

        step("GIVEN I am a 2fa authenticated user");
        User twoFactorUser = userData.createTester(siteData.createSite().getId());
        motUI.authentication.securityCard.activate2faCard(twoFactorUser).logOut(twoFactorUser);

        step("And I complete the sign in journey without a card");
        motUI.authentication.securityCard.signInWithoutSecurityCard(twoFactorUser);

        step("WHEN I order a card with valid Home address");
        String message = motUI.authentication.securityCard.orderSecurityCardWithHomeAddress(twoFactorUser);

        step("THEN my card is ordered successfully");
        assertThat("Card Order was Successful", message, containsString("Your security card has been ordered"));
    }
}
