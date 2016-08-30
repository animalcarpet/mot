package uk.gov.dvsa.ui.feature.journey.nomination;

import org.testng.annotations.Test;
import ru.yandex.qatools.allure.annotations.Description;
import ru.yandex.qatools.allure.annotations.Features;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.shared.role.OrganisationBusinessRoleCodes;
import uk.gov.dvsa.domain.shared.role.TradeRoles;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.Matchers.is;

@Description("This Journey does not include nominations for AEDM role")
@Features("Nomination notifications for nominees to accept or reject role")
public class AcceptNominationTests extends DslTest {

    @Test(testName = "2fa", groups = {"BVT"})
    void userCannotAcceptNominationWith2faOn() throws IOException {
        step("Given I have been nominated for a Site Manager role as non 2fa user");
        User not2faActiveUser = userData.createUserWithoutRole();
        motApi.nominations.nominateSiteRole(not2faActiveUser, siteData.createSite().getId(), TradeRoles.SITE_MANAGER);

        step("When I viewMostRecent my notifications");

        step("Then I cannot accept the nomination");
        assertThat("Accept Nomination Button is not displayed",
            motUI.nominations.viewMostRecent(not2faActiveUser).isAcceptButtonDisplayed(), is(false));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    void userCanAcceptSiteManagerNominationWith2faOn() throws IOException {
        step("Given I have been nominated for a Site Manager role as non 2fa user");
        User user = userData.createUserWithoutRole();
        motApi.nominations.nominateSiteRole(user, siteData.createSite().getId(), TradeRoles.SITE_MANAGER);

        step("When I order and activate the card from Site Manager nomination notification");
        motUI.nominations.orderAndActivateSecurityCard(user);
        String message = motUI.nominations.viewMostRecent(user).acceptNomination().getConfirmationText();

        step("Then I can accept my Site Manager nomination");

        assertThat("Nominated was accepted successfully",
                message, containsString("You have been assigned the role of 'Site manager'"));

    }

    @Test(testName = "2fa", groups = {"BVT"})
    void userCanAcceptAedNominationWith2faOn() throws IOException {
        step("Given I have been nominated for a AED role as non 2fa user");
        User user = userData.createUserWithoutRole();
        motApi.nominations.nominateOrganisationRoleWithRoleCode(user, aeData.createAeWithDefaultValues().getId(), OrganisationBusinessRoleCodes.AED);

        step("When I order and activate the card from AED nomination notification");
        motUI.nominations.orderAndActivateSecurityCard(user);
        String message = motUI.nominations.viewMostRecent(user).acceptNomination().getConfirmationText();

        step("Then I can accept my AED nomination");

        assertThat("Authorised Examiner Delegate Role Confirmation",
                message, containsString("You have been assigned the role of 'Authorised Examiner Delegate'"));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    void existingTradeUserCanAcceptSiteAdminNominationWithoutOrdering2faCard() throws IOException {
        step("Given I am a trade user nominated as a site admin as a non 2fa user");
        User nominee = userData.createTester(siteData.createSite().getId(), false);
        motApi.nominations.nominateSiteRole(nominee, siteData.createSite().getId(), TradeRoles.SITE_ADMIN);

        step("When I accept the nomination without ordering or activating a 2fa card");
        String message = motUI.nominations.viewMostRecent(nominee).acceptNomination().getConfirmationText();

        step("Then I am given the site admin role");
        assertThat("Site Admin Role Confirmation", message, containsString("You have been assigned the role of 'Site admin'"));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    void existingTradeUserCanAcceptAedNominationWithoutOrdering2faCard() throws IOException {
        step("Given I am trade user nominated for an AED role as a non 2fa user");
        User nominee = userData.createTester(siteData.createSite().getId(), false);
        motApi.nominations.nominateOrganisationRoleWithRoleCode(nominee, aeData.createAeWithDefaultValues().getId(), OrganisationBusinessRoleCodes.AED);

        step("When I accept the nomination without ordering or activating a 2fa card");
        String message = motUI.nominations.viewMostRecent(nominee).acceptNomination().getConfirmationText();

        step("Then I can accept my AED nomination");
        assertThat("Authorised Examiner Delegate Role Confirmation",
                message, containsString("You have been assigned the role of 'Authorised Examiner Delegate'"));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    void existingTradeUserCanAcceptSiteManagerNominationWithoutActivating2faCard() throws IOException {
        step("Given I am a trade user nominated as a site manager");
        User nominee = userData.createTester(siteData.createSite().getId(), false);
        motApi.nominations.nominateSiteRole(nominee, siteData.createSite().getId(), TradeRoles.SITE_MANAGER);

        step("When I accept the nomination without activating a 2fa card");
        motUI.authentication.securityCard.orderSecurityCardWithHomeAddress(nominee);
        String message = motUI.nominations.viewMostRecent(nominee).acceptNomination().getConfirmationText();

        step("Then I am given the site manager role");
        assertThat("Site Manager Role Confirmation", message, containsString("You have been assigned the role of 'Site manager'"));
    }

    @Test(groups = {"BVT"})
    void tradeUserAcceptTesterNomination() throws IOException {
        step("Given I am nominated as a tester");
        User nominee = userData.createTester(siteData.createSite().getId());
        motApi.nominations.nominateSiteRole(nominee,siteData.createSite().getId(), TradeRoles.TESTER);

        step("When I accept the nomination");
        String message = motUI.nominations.viewMostRecent(nominee).acceptNomination().getConfirmationText();

        step("Then I am given the tester role");
        assertThat("Tester Role Confirmation", message, containsString("You have been assigned the role of 'Tester'"));
    }

    @Test(groups = {"BVT"})
    void tradeUserAcceptSiteAdminNomination() throws IOException {
        step("Given I nominated as a site manager");
        User nominee = userData.createSiteAdmin(siteData.createSite().getId(), false);
        motApi.nominations.nominateSiteRole(nominee,siteData.createSite().getId(), TradeRoles.SITE_ADMIN);

        step("When I accept the nomination");
        String message = motUI.nominations.viewMostRecent(nominee).acceptNomination().getConfirmationText();

        step("Then I am given the site admin role");
        assertThat("Site Admin Role Confirmation", message, containsString("You have been assigned the role of 'Site admin'"));
    }

    @Test(groups = {"BVT"})
    void tradeUserAcceptSiteManagerNomination() throws IOException {
        step("Given I nominate a user as a site manager");
        User nominee = userData.createSiteManager(siteData.createSite().getId(), false);
        motApi.nominations.nominateSiteRole(nominee,siteData.createSite().getId(), TradeRoles.SITE_MANAGER);
        
        step("When I accept the nomination");
        String message = motUI.nominations.viewMostRecent(nominee).acceptNomination().getConfirmationText();

        step("Then I am given the site manager role");
        assertThat("Site Manager Role Confirmation", message, containsString("You have been assigned the role of 'Site manager'"));
    }

    @Test(testName = "non-2fa", groups = {"BVT"})
    void userAcceptAedNominationWith2faOff() throws IOException {
        step("Given I nominate a user as an aed");
        User nominee = userData.createUserWithoutRole();
        motApi.nominations.nominateOrganisationRoleWithRoleCode(nominee, aeData.createAeWithDefaultValues().getId(), OrganisationBusinessRoleCodes.AED);

        step("When I accept the nomination");
        String message = motUI.nominations.viewMostRecent(nominee).acceptNomination().getConfirmationText();

        step("Then I am given the aed role");
        assertThat("Authorised Examiner Delegate Role Confirmation",
            message, containsString("You have been assigned the role of 'Authorised Examiner Delegate'"));
    }
}
