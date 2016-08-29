package uk.gov.dvsa.ui.feature.journey.authentication.securitycard;

import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.authentication.securitycard.card_order_report.CardOrderReportListPage;
import uk.gov.dvsa.ui.pages.events.EventsHistoryPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.MatcherAssert.assertThat;

public class SecurityCardOrderReportTests extends DslTest {

    @Test(testName = "2fa", groups = {"BVT"})
    public void catUserSeeReportLinksForLast7Days() throws IOException {

        step("Given I am Central Admin Team user");
        User catUser = userData.createCentralAdminTeamUser();

        step("When I visit security card order list");
        CardOrderReportListPage list = motUI.authentication.securityCard.goToSecurityCardOrderReportList(catUser);

        step("Then I should see report links for last 7 days");
        assertThat("I have seen report links for 7 days", list.containsLinksToOrderReportFor7Days(), is(true));
    }

    @Test(testName = "2fa", groups = {"Regression"})
    public void catUserCanSeeTheLinkToReportListInHomePage() throws IOException {

        step("Given I am Central Admin Team user");
        User catUser = userData.createCentralAdminTeamUser();

        step("When I open the home page");
        HomePage page = pageNavigator.gotoHomePage(catUser);

        step("Then I should see security card order list link");
        assertThat("Security card order list displayed", page.isSecurityCardOrderListLinkDisplayed(), is(true));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    public void cscoCanViewSecurityCardOrderEventForATradeUser() throws IOException, URISyntaxException {
        step("Given I order a card for a trade user as CSCO");
        User csco = userData.createCSCO();
        User tradeUser = userData.createUserWithoutRole();
        motUI.authentication.securityCard.orderCardForTradeUserAsCSCO(csco, tradeUser);

        step("When I view the event history for the Trade User");
        EventsHistoryPage eventsHistoryPage = pageNavigator.goToUserSearchedProfilePageViaUserSearch(csco,tradeUser).
                clickEventHistoryLink();

        step("Then a security card order event link displayed within the event history");
        assertThat("Security card order event link displayed",
                eventsHistoryPage.isSecurityOrderCardEventLinkDisplayed(), is(true));
    }

}

