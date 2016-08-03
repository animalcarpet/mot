package uk.gov.dvsa.shared;

import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.module.*;
import uk.gov.dvsa.module.profile.Profile;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.events.EventsHistoryPage;
import uk.gov.dvsa.ui.pages.events.HistoryType;
import uk.gov.dvsa.ui.pages.login.LoginPage;

import java.io.IOException;

public class MotUI {

    private PageNavigator pageNavigator = new PageNavigator();
    private HomePage homepage;

    public final Retest retest;
    public final NormalTest normalTest;
    public final Register register;
    public final ManageRoles manageRoles;
    public final TestLog testLog;
    public final SearchUser searchUser;
    public final SearchSite searchSite;
    public final Profile profile;
    public final Site site;
    public final Certificate certificate;
    public final Contingency contingency;
    public final HelpDesk helpDesk;
    public final EventHistory eventHistory;
    public final DemoTestRequests demoTestRequests;
    public final ReInspection reInspection;

    public MotUI(MotAppDriver driver) {
        pageNavigator.setDriver(driver);
        retest = new Retest(pageNavigator);
        register = new Register(pageNavigator);
        normalTest = new NormalTest(pageNavigator);
        testLog = new TestLog(pageNavigator);
        manageRoles = new ManageRoles(pageNavigator);
        searchUser = new SearchUser(pageNavigator);
        searchSite = new SearchSite(pageNavigator);
        certificate = new Certificate(pageNavigator);
        profile = new Profile(pageNavigator);
        site = new Site(pageNavigator);
        contingency = new Contingency(pageNavigator);
        helpDesk = new HelpDesk(pageNavigator);
        eventHistory = new EventHistory();
        demoTestRequests = new DemoTestRequests(pageNavigator);
        reInspection = new ReInspection(pageNavigator);
    }

    public void login(final User user) throws IOException {
        LoginPage loginPage = pageNavigator.goToLoginPage();
        pageNavigator.getDriver().setUser(user);
        homepage = loginPage.login(user.getUsername(), user.getPassword(), HomePage.class);
    }

    public boolean isLoginSuccessful(){
        return homepage != null;
    }

    public void showEventHistoryFor(HistoryType historyType, User user, AeDetails aeDetails) throws IOException {
        String path = String.format(EventsHistoryPage.PATH, historyType.toString(), aeDetails.getIdAsString());
        EventsHistoryPage eventsHistoryPage = pageNavigator.navigateToPage(user, path, EventsHistoryPage.class);
        eventHistory.setHistoryPage(eventsHistoryPage, aeDetails);
    }
}
