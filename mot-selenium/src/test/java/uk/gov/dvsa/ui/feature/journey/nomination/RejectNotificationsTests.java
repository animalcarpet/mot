package uk.gov.dvsa.ui.feature.journey.nomination;

import org.testng.annotations.Test;

import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.shared.role.OrganisationBusinessRoleCodes;
import uk.gov.dvsa.domain.shared.role.TradeRoles;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import ru.yandex.qatools.allure.annotations.Description;
import ru.yandex.qatools.allure.annotations.Features;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.Matchers.is;


@Description("This Journey does not include nominations for AEDM role")
@Features("Nomination notification(s) for nominees to accept or reject role")
public class RejectNotificationsTests extends DslTest {

    @Test(groups = {"nomination"})
    void non2faUserCannotRejectNominationWith2faOn() throws IOException {
        step("Given I have been nominated for a Site Manager role as non 2fa user");
        User not2faActiveUser = motApi.user.createUserWithoutRole();
        motApi.nominations.nominateSiteRole(not2faActiveUser, siteData.createSite().getId(), TradeRoles.SITE_MANAGER);

        step("When I viewMostRecent my notifications");

        step("Then I cannot reject the nomination");
        assertThat("Accept Nomination Button is not displayed",
                motUI.nominations.viewMostRecent(not2faActiveUser).isRejectButtonDisplayed(), is(false));
    }

    @Test(groups = {"nomination"})
    void userRejectsSiteManagerNominationWith2FAon() throws IOException {
        step("Given I have been nominated for a Site Manager role as non 2fa user");
        User user = motApi.user.createUserWithoutRole();
        motApi.nominations.nominateSiteRole(user, siteData.createSite().getId(), TradeRoles.SITE_MANAGER);

        step("When I order and activate the card from Site Manager nomination notification");
        motUI.nominations.orderAndActivateSecurityCard(user);
        String message = motUI.nominations.viewMostRecent(user).rejectNomination().getConfirmationText();

        step("Then I can reject my nomination");

        assertThat("Nominated was rejected successfully",
                message, containsString("You have rejected the role of Site manager"));
    }

    @Test(groups = {"nomination"})
    void userRejectsSiteAdminNominationWith2FAon() throws IOException {
        step("Given I have been nominated for a Site Admin role as non 2fa user");
        User user = motApi.user.createUserWithoutRole();
        motApi.nominations.nominateSiteRole(user, siteData.createSite().getId(), TradeRoles.SITE_ADMIN);

        step("When I order and activate the card from Site Admin nomination notification");
        motUI.nominations.orderAndActivateSecurityCard(user);
        String message = motUI.nominations.viewMostRecent(user).rejectNomination().getConfirmationText();

        step("Then I can reject my nomination");

        assertThat("Nominated was rejected successfully",
                message, containsString("You have rejected the role of Site admin"));
    }

    @Test(groups = {"nomination"})
    void userRejectsTesterNominationWith2FAon() throws IOException {
        step("Given I have been nominated for a Tester role as non 2fa user");
        User user = motApi.user.createUserWithoutRole();
        Site testSite = siteData.createSite();
        qualificationDetailsData.createQualificationCertificateForGroupA(
                user, "1234123412341234", "2016-04-01", testSite.getSiteNumber()
        );
        motApi.nominations.nominateSiteRole(user,testSite.getId(), TradeRoles.TESTER);

        step("When I order and activate the card from Tester nomination notification");
        motUI.nominations.orderAndActivateSecurityCard(user);
        String message = motUI.nominations.viewMostRecent(user).rejectNomination().getConfirmationText();

        step("Then I can reject my nomination");
        assertThat("Nominated was rejected successfully",
                message, containsString("You have rejected the role of Tester"));
    }

    @Test(groups = {"nomination"})
    void userRejectsAEDNominationWith2FAon() throws IOException {
        step("Given I have been nominated for an AED role as non 2fa user");
        User user = motApi.user.createUserWithoutRole();
        motApi.nominations.nominateOrganisationRoleWithRoleCode(user, aeData.createAeWithDefaultValues().getId(), OrganisationBusinessRoleCodes.AED);

        step("When I order and activate the card from AED nomination notification");
        motUI.nominations.orderAndActivateSecurityCard(user);
        String message = motUI.nominations.viewMostRecent(user).rejectNomination().getConfirmationText();

        step("Then I can reject my nomination");

        assertThat("Nominated was rejected successfully",
                message, containsString("You have rejected the role of Authorised Examiner Delegate"));
    }
}
