package uk.gov.dvsa.shared;

import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.journey.*;
import uk.gov.dvsa.journey.authentication.Authentication;
import uk.gov.dvsa.journey.profile.Profile;
import uk.gov.dvsa.module.ReInspection;
import uk.gov.dvsa.ui.interfaces.TwoFactorPromptPage;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.authentication.securitycard.ActivateYourCardPromptPage;
import uk.gov.dvsa.ui.pages.authentication.securitycard.OrderYourCardPromptPage;
import uk.gov.dvsa.ui.pages.authentication.securitycard.lost_or_forgotten.LostForgottenCardAlreadyOrderedPage;
import uk.gov.dvsa.ui.pages.authentication.securitycard.lost_or_forgotten.LostForgottenCardQuestionOnePage;
import uk.gov.dvsa.ui.pages.events.EventsHistoryPage;
import uk.gov.dvsa.ui.pages.events.HistoryType;
import uk.gov.dvsa.ui.pages.login.LoginPage;
import uk.gov.dvsa.ui.pages.authentication.twofactorauth.RegisterCardInformationPage;
import uk.gov.dvsa.ui.pages.authentication.twofactorauth.TwoFactorPinEntryPage;

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
    public final Authentication authentication;
    public final DemoTestRequests demoTestRequests;
    public final Organisation organisation;
    public final ReInspection reInspection;
    public final ClaimAccount claimAccount;
    public final Nominations nominations;

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
        authentication = new Authentication(pageNavigator);
        demoTestRequests = new DemoTestRequests(pageNavigator);
        reInspection = new ReInspection(pageNavigator);
        organisation = new Organisation(pageNavigator);
        claimAccount = new ClaimAccount(pageNavigator);
        nominations = new Nominations(pageNavigator);
    }

    public void login(final User user) throws IOException {
        LoginPage loginPage = pageNavigator.goToLoginPage();
        pageNavigator.getDriver().setUser(user);
        homepage = loginPage.login(user.getUsername(), user.getPassword(), HomePage.class);
    }

    public LoginPage loginWithCustomCredentials(final User user, String username, String password) throws IOException {
        LoginPage loginPage = pageNavigator.goToLoginPage();
        pageNavigator.getDriver().setUser(user);
        return loginPage.login(username, password, LoginPage.class);
    }

    public RegisterCardInformationPage loginExpectingCardInformationPage(final User user) throws IOException {
        LoginPage loginPage = pageNavigator.goToLoginPage();
        pageNavigator.getDriver().setUser(user);
        return loginPage.login(user.getUsername(), user.getPassword(), RegisterCardInformationPage.class);
    }

    public TwoFactorPinEntryPage loginExpectingPinEntryPage(final User user) throws IOException {
        LoginPage loginPage = pageNavigator.goToLoginPage();
        pageNavigator.getDriver().setUser(user);
        return loginPage.login(user.getUsername(), user.getPassword(), TwoFactorPinEntryPage.class);
    }

    public boolean isLoginSuccessful(){
        return new HomePage(pageNavigator.getDriver()).isHeroSideBarDisplayed();
    }

    public void logout(User user) {
        pageNavigator.clickLogout(user);
    }

    public void showEventHistoryFor(HistoryType historyType, User user, AeDetails aeDetails) throws IOException {
        String path = String.format(EventsHistoryPage.PATH, historyType.toString(), aeDetails.getIdAsString());
        EventsHistoryPage eventsHistoryPage = pageNavigator.navigateToPage(user, path, EventsHistoryPage.class);
        eventHistory.setHistoryPage(eventsHistoryPage, aeDetails);
    }

    public boolean verifyLoginPageIsDisplayed() {
        return new LoginPage(pageNavigator.getDriver()).isSignInButtonPresent();
    }

    public TwoFactorPromptPage loginExpecting2faOrderPromtPage(User user) throws IOException {
        LoginPage loginPage = pageNavigator.goToLoginPage();
        pageNavigator.getDriver().setUser(user);
        return loginPage.login(user.getUsername(), user.getPassword(), OrderYourCardPromptPage.class);
    }

    public TwoFactorPromptPage loginExpecting2faActivatePromtPage(User user) throws IOException {
        LoginPage loginPage = pageNavigator.goToLoginPage();
        pageNavigator.getDriver().setUser(user);
        return loginPage.login(user.getUsername(), user.getPassword(), ActivateYourCardPromptPage.class);
    }

    public LostForgottenCardAlreadyOrderedPage loginExpecting2faAlreadyOrderedPage(User user) throws IOException {
        LoginPage loginPage = pageNavigator.goToLoginPage();
        pageNavigator.getDriver().setUser(user);
        return loginPage.login(user.getUsername(), user.getPassword(), LostForgottenCardAlreadyOrderedPage.class);
    }

    public LostForgottenCardQuestionOnePage loginExpecting2faSecurityQuestionOnePage(User user) throws IOException {
        LoginPage loginPage = pageNavigator.goToLoginPage();
        pageNavigator.getDriver().setUser(user);
        return loginPage.login(user.getUsername(), user.getPassword(), LostForgottenCardQuestionOnePage.class);
    }
}
