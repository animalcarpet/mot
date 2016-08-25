package uk.gov.dvsa.ui.feature.journey.nomination;

import org.testng.annotations.Test;
import ru.yandex.qatools.allure.annotations.Description;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.shared.role.TradeRoles;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;

@Description("Nomination notifications signposts nominees to next step")
public class NominationsTests extends DslTest {

    private static final int AUTHORISED_EXAMINER_DESIGNATED_MANAGER_ID = 1;

    @Test(testName = "2fa", groups = {"BVT"})
    void non2faUserCanOrderCardViaNominationsNotificationLink() throws IOException {
        step("Given I have been nominated for a Site Manager role as non 2fa user");
        User not2faActiveUser = userData.createUserWithoutRole();
        motApi.nominations.nominateSiteRole(not2faActiveUser, siteData.createSite().getId(), TradeRoles.SITE_MANAGER);

        step("And I have not ordered a card");
        step("When I order a card from Site Manager nomination notification");
        motUI.nominations.viewMostRecent(not2faActiveUser).clickOrderCard();
        String message = motUI.authentication.securityCard.orderSecurityCardWithHomeAddress(not2faActiveUser);

        step("Then the order is successful");
        assertThat("The card is ordered successfully", message, containsString("Your security card has been ordered"));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    void non2faUserCanActivateCardViaNominationNotificationsLink() throws IOException {
        step("Given I have been nominated for a Site Manager role as non 2fa user");
        User orderedCardUser = userData.createUserWithoutRole();
        motApi.nominations.nominateSiteRole(orderedCardUser, siteData.createSite().getId(), TradeRoles.SITE_MANAGER);

        step("And I have ordered a card");
        motUI.authentication.securityCard.orderSecurityCardWithHomeAddress(orderedCardUser);

        step("When I activate the card from Site Manager nomination notification");
        motUI.nominations.viewMostRecent(orderedCardUser).clickActivateCard();
        String message = motUI.authentication.securityCard.activate2faCard(orderedCardUser).getConfirmationText();

        step("Then my card is activated");
        assertThat("Activation Successful", message, containsString("Your security card has been activated"));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    void non2faUserCannotOrderCardTwiceViaNotificationsLink() throws IOException {
        step("Given I have previously ordered a card as non 2fa user from notification");
        User not2faActiveUser = userData.createUserWithoutRole();
        motApi.nominations.nominateSiteRole(not2faActiveUser, siteData.createSite().getId(), TradeRoles.SITE_MANAGER);
        motUI.authentication.securityCard.orderSecurityCardWithHomeAddress(not2faActiveUser);

        step("When I try to order another card from notification list");
        String message = motUI.nominations.viewOrderCardNotification(not2faActiveUser).clickOrderCardExpectingAlreadyOrderedText();

        step("Then I am redirected to already ordered card page");
        assertThat("Already ordered Page is Shown", message, containsString("You have already ordered a security card"));
    }

    @Test(testName ="2fa", groups = {"BVT"})
    void aedmNon2faUserNominatedWithNoCardDirectedToOrderACard() throws Exception {
        step("Given I am not 2fa and dont have a card");
        User not2faActiveUser = userData.createUserWithoutRole();
        step("I have been nominated for AEDM");
        motApi.nominations.nominateOrganisationRoleWithRoleId(not2faActiveUser, aeData.createAeWithDefaultValues().getId(), AUTHORISED_EXAMINER_DESIGNATED_MANAGER_ID);//replace with role id for AEDM

        step("When I am nominated, I am asked to order a card");
        //assert that order card notification is generated.
        String message = motUI.nominations.viewMostRecent(not2faActiveUser).getNotificationText();
        System.out.println(message);
        assertThat("Notification for AEDM - prompted to order a security card is shown", message, containsString("You need to order a security card."));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    void aedmNon2faUserNominatedWithCardDirectedToActivateCard() throws Exception {
        step("Given I am not 2fa and I am going to be nominated for AEDM");
        User orderedCardUser = userData.createUserWithoutRole();
        motApi.nominations.nominateOrganisationRoleWithRoleId(orderedCardUser, aeData.createAeWithDefaultValues().getId(), AUTHORISED_EXAMINER_DESIGNATED_MANAGER_ID);

        step("And I have ordered a card");
        motUI.authentication.securityCard.orderSecurityCardWithHomeAddress(orderedCardUser);

        step("When I am nominated, I recieve a notification to activate my card");
        String message = motUI.nominations.viewMostRecent(orderedCardUser).getNotificationText();
        assertThat("Notification for AEDM - prompted to activate a security card", message, containsString("once you have activated the card."));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    void aedmNominationNotificationsAreSentAfterCardActivation() throws Exception {
        step("Given I am not 2fa and I am going to be nominated for AEDM");
        User orderedCardUser = userData.createUserWithoutRole();

        step("And I have ordered a card and then been subsequently nominated");
        motApi.nominations.nominateOrganisationRoleWithRoleId(orderedCardUser, aeData.createAeWithDefaultValues().getId(), AUTHORISED_EXAMINER_DESIGNATED_MANAGER_ID);
        motUI.authentication.securityCard.orderSecurityCardWithHomeAddress(orderedCardUser);
        motUI.authentication.securityCard.activate2faCard(orderedCardUser);

        step("I recieve a notification telling me I have been nominated as AEDM");
        String message = motUI.nominations.viewMostRecent(orderedCardUser).getNotificationText();
        assertThat("Notification for AEDM - prompted to activate a security card", message, containsString("You have been assigned a role of Authorised Examiner Designated Manager"));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    public void user_Get_Notification_When_Csco_Orders_Card_For_Them() throws IOException {
        step("Given I have been nominated as a Site manager");
        User tradeUser = userData.createUserWithoutRole();
        motApi.nominations.nominateSiteRole(tradeUser, siteData.createSite().getId(), TradeRoles.SITE_MANAGER);

        step("When a CSCO orders a card on my behalf");
        motUI.authentication.securityCard.orderCardForTradeUserAsCSCO(userData.createCSCO(), tradeUser);

        step("Then I should see an activate notification on my homepage");
        motUI.nominations.viewActivateCardNotification(tradeUser);
    }

}
