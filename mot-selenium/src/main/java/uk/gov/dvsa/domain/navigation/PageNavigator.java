package uk.gov.dvsa.domain.navigation;

import org.openqa.selenium.By;
import org.openqa.selenium.Cookie;
import org.openqa.selenium.WebElement;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.domain.model.vehicle.DvlaVehicle;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.service.CookieService;
import uk.gov.dvsa.framework.config.Configurator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.ConfigHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.*;
import uk.gov.dvsa.ui.pages.authentication.twofactorauth.RegisterCardPage;
import uk.gov.dvsa.ui.pages.dvsa.ManageRolesPage;
import uk.gov.dvsa.ui.pages.dvsa.UserSearchPage;
import uk.gov.dvsa.ui.pages.dvsa.UserSearchResultsPage;
import uk.gov.dvsa.ui.pages.login.LoginPage;
import uk.gov.dvsa.ui.pages.mot.*;
import uk.gov.dvsa.ui.pages.mot.certificates.DuplicateReplacementCertificateTestHistoryPage;
import uk.gov.dvsa.ui.pages.mot.retest.ConfirmVehicleRetestPage;
import uk.gov.dvsa.ui.pages.mot.retest.ReTestResultsEntryPage;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;
import uk.gov.dvsa.ui.pages.userregistration.CreateAnAccountPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.CreateNewVehicleRecordIdentificationPage;
import uk.gov.dvsa.ui.pages.vts.SearchForAVtsPage;
import uk.gov.dvsa.ui.pages.vts.SiteTestQualityPage;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

import java.io.IOException;
import java.net.URISyntaxException;

public class PageNavigator {
    private MotAppDriver driver;

    public void setDriver(MotAppDriver driver) {
        this.driver = driver;
    }

    public MotAppDriver getDriver() {
        return driver;
    }

    private Cookie getCookieForUser(User user) throws IOException {
        return CookieService.generateOpenAmLoginCookie(user);
    }

    public <T extends Page> T navigateToPage(User user, String path, Class<T> clazz) throws IOException {
        injectOpenAmCookieAndNavigateToPath(user, path);
        return MotPageFactory.newPage(driver, clazz);
    }

    public <T extends Page> T goToPageAsAuthorisedExaminer(User user, Class<T> clazz, String path, int aeId) throws URISyntaxException, IOException {
        injectOpenAmCookieAndNavigateToPath(user, String.format(path, aeId));
        return MotPageFactory.newPage(driver, clazz);
    }

    public <T extends Page> T goToVtsPage(User user, Class<T> clazz, String path, int siteId) throws URISyntaxException, IOException {
        injectOpenAmCookieAndNavigateToPath(user, String.format(path, siteId));
        return MotPageFactory.newPage(driver, clazz);
    }

    public StartTestConfirmationPage goToStartTestConfirmationPage(User user, Vehicle vehicle) throws URISyntaxException, IOException {
        injectOpenAmCookieAndNavigateToPath(user, VehicleSearchPage.PATH);

        return PageLocator.getVehicleSearchPage(driver).searchVehicle(vehicle).selectVehicle(StartTestConfirmationPage.class);
    }

    public StartTestConfirmationPage goToStartTestConfirmationPage(User user, DvlaVehicle dvlaVehicle) throws URISyntaxException, IOException {
        injectOpenAmCookieAndNavigateToPath(user, VehicleSearchPage.PATH);

        return ((VehicleSearchResultsPage) PageLocator.getVehicleSearchPage(driver).searchVehicle(
                dvlaVehicle.getRegistration(), dvlaVehicle.getVin(), true)).selectVehicle(StartTestConfirmationPage.class);
    }

    public TestResultsEntryGroupAPageInterface gotoTestResultsEntryPage(User user, Vehicle vehicle) throws URISyntaxException, IOException {
        injectOpenAmCookieAndNavigateToPath(user, VehicleSearchPage.PATH);

        navigateToPath(PageLocator.getVehicleSearchPage(driver).searchVehicle(vehicle).selectVehicle(
                StartTestConfirmationPage.class).clickStartMotTest().getMotTestPath());

        if (ConfigHelper.isTestResultEntryImprovementsEnabled()) {
            return new TestResultsEntryNewPage(driver);
        }
        return new TestResultsEntryPage(driver);
    }

    public TestResultsEntryNewPage gotoTestResultsEntryNewPage(User user, Vehicle vehicle) throws URISyntaxException, IOException {
        injectOpenAmCookieAndNavigateToPath(user, VehicleSearchPage.PATH);

        navigateToPath(PageLocator.getVehicleSearchPage(driver).searchVehicle(vehicle).selectVehicle(
                StartTestConfirmationPage.class).clickStartMotTest().getMotTestPath());

        return new TestResultsEntryNewPage(driver);
    }

    public DefectsPage gotoDefectsPageWithDefect(User user, Vehicle vehicle, Defect defect) throws URISyntaxException, IOException {
        return gotoTestResultsEntryNewPage(user, vehicle).clickAddDefectButton().navigateToDefectCategory(
                defect.getCategoryPath()).navigateToAddDefectPage(defect).clickAddDefectButton();
    }

    public DefectCategoriesPage gotoDefectCategoriesPageWithDefect(User user, Vehicle vehicle, Defect defect) throws URISyntaxException, IOException {
        return gotoTestResultsEntryNewPage(user, vehicle).clickAddDefectButton()
                .navigateToDefectCategory(defect.getCategoryPath())
                .navigateToAddDefectPage(defect)
                .clickAddDefectButton()
                .clickReturnToDefectCategoriesLink();
    }

    public TestResultsEntryNewPage gotoTestResultsPageWithDefect(User user, Vehicle vehicle, Defect defect) throws URISyntaxException, IOException {
        return gotoTestResultsEntryNewPage(user, vehicle).clickAddDefectButton().navigateToDefectCategory(
                defect.getCategoryPath()).navigateToAddDefectPage(defect).clickAddDefectButton().clickFinishAndReturnButton();
    }

    public SearchForADefectPage gotoSearchForADefectPage(User user, Vehicle vehicle) throws URISyntaxException, IOException {
        return gotoTestResultsEntryNewPage(user, vehicle).clickSearchForADefectButton();
    }

    public <T extends Page>T gotoReTestResultsEntryPage(User user, Vehicle vehicle) throws URISyntaxException, IOException {
        injectOpenAmCookieAndNavigateToPath(user, VehicleSearchPage.PATH);

        navigateToPath(searchForVehicleForRetest(vehicle).startRetest().getMotTestPath());

        if (ConfigHelper.isTestResultEntryImprovementsEnabled()) {
            return (T) new TestResultsEntryNewPage(driver);
        }
        return (T) new ReTestResultsEntryPage(driver);
    }

    private ConfirmVehicleRetestPage searchForVehicleForRetest(Vehicle vehicle) throws URISyntaxException {
        return PageLocator.getVehicleSearchPage(driver).searchVehicle(vehicle).selectVehicle(
                ConfirmVehicleRetestPage.class);
    }

    public ContingencyTestEntryPage gotoContingencyTestEntryPage(User user) throws URISyntaxException, IOException {
        injectOpenAmCookieAndNavigateToPath(user, ContingencyTestEntryPage.PATH);
        return new ContingencyTestEntryPage(driver);
    }

    public void signOutAndGoToLoginPage() {
        driver.manage().deleteAllCookies();
        driver.setBaseUrl(Configurator.baseUrl());
        driver.loadBaseUrl();
    }

    public HomePage gotoHomePage(User user) throws IOException {
        injectOpenAmCookieAndNavigateToPath(user, HomePage.PATH);

        return new HomePage(driver);
    }

    public CreateAnAccountPage goToCreateAnAccountPage() throws IOException {
        navigateToPath(CreateAnAccountPage.PATH);

        return new CreateAnAccountPage(driver);
    }

    public TestResultsEntryGroupAPageInterface gotoTrainingTestResultsEntryPage(User user, Vehicle vehicle) throws IOException, URISyntaxException {
        injectOpenAmCookieAndNavigateToPath(user, VehicleSearchPage.TRAINING_TEST_PATH);

        navigateToPath(new VehicleSearchPage(driver).searchVehicle(vehicle).selectVehicle(
                StartTestConfirmationPage.class).clickStartMotTest().getMotTestPath());

        if (ConfigHelper.isTestResultEntryImprovementsEnabled()) {
            return new TestResultsEntryNewPage(driver);
        }
        return new TestResultsEntryPage(driver);
    }

    public TestResultsEntryNewPage gotoTrainingTestResultsEntryNewPage(User user, Vehicle vehicle) throws IOException, URISyntaxException {
        injectOpenAmCookieAndNavigateToPath(user, VehicleSearchPage.TRAINING_TEST_PATH);

        navigateToPath(new VehicleSearchPage(driver).searchVehicle(vehicle).selectVehicle(
                StartTestConfirmationPage.class).clickStartMotTest().getMotTestPath());

        return new TestResultsEntryNewPage(driver);
    }

    public SearchForAVtsPage goToVtsSearchPage(User user) throws IOException {
        injectOpenAmCookieAndNavigateToPath(user, SearchForAVtsPage.PATH);

        return new SearchForAVtsPage(driver);
    }

    public VehicleTestingStationPage goToVtsPage(User user, String vtsId) throws IOException {
        injectOpenAmCookieAndNavigateToPath(user, String.format(VehicleTestingStationPage.PATH, vtsId));

        return new VehicleTestingStationPage(driver);
    }

    public LoginPage goToLoginPage() throws IOException {
        driver.loadBaseUrl();
        return new LoginPage(driver);
    }

    public ManageRolesPage goToManageRolesPageViaUserSearch(User loggedUser, User searchedUser) throws IOException, URISyntaxException {
        return goToUserSearchedProfilePageViaUserSearch(loggedUser, searchedUser).clickManageRolesLink();
    }

    public ProfilePage goToUserSearchedProfilePageViaUserSearch(User loggedUser, User searchedUser) throws IOException, URISyntaxException {
        return navigateToPage(loggedUser, UserSearchPage.PATH, UserSearchPage.class).searchForUserByUsername(searchedUser.getUsername())
                .clickSearchButton(UserSearchResultsPage.class).chooseUser(0);
    }

    public CreateNewVehicleRecordIdentificationPage gotoCreateNewVehicleRecordIdentificationPage(User user) throws IOException {
        injectOpenAmCookieAndNavigateToPath(user, VehicleSearchPage.PATH);

        return PageLocator.getVehicleSearchPage(driver).searchVehicle("", "", false).createNewVehicle();
    }

    public DuplicateReplacementCertificateTestHistoryPage gotoDuplicateReplacementCertificateTestHistoryPage(User user, Vehicle vehicle) throws IOException {
        injectOpenAmCookieAndNavigateToPath(user, String.format("/replacement-certificate-vehicle-search?registration=%s&vin=%s",
                vehicle.getDvsaRegistration(), vehicle.getVin()));

        new VehicleSearchPage(driver).searchVehicle(vehicle).selectVehicle(DuplicateReplacementCertificateTestHistoryPage.class);
        return new DuplicateReplacementCertificateTestHistoryPage(driver);
    }

    public RefuseToTestPage gotoRefuseToTestPage(User user, Vehicle vehicle) throws IOException {
        injectOpenAmCookieAndNavigateToPath(user, VehicleSearchPage.PATH);

        return PageLocator.getVehicleSearchPage(driver).searchVehicle(vehicle).selectVehicle(
                StartTestConfirmationPage.class).refuseToTestVehicle();
    }

    public RegisterCardPage goToRegisterCardPage(User user) throws IOException
    {
        injectOpenAmCookieAndNavigateToPath(user, RegisterCardPage.PATH);
        return new RegisterCardPage(driver);
    }

    public SiteTestQualityPage gotoSiteTestQualityPage(User user, Site site) throws IOException {
        String path = String.format(SiteTestQualityPage.PATH, site.getId());
        injectOpenAmCookieAndNavigateToPath(user, path);
        SiteTestQualityPage siteTestQualityPage = PageLocator.getSiteTestQualityPage(driver);

        return siteTestQualityPage;
    }

    private void navigateToPath(String path) {
        driver.navigateToPath(path);
    }


    private boolean userAlreadyHaveASession(User user){
        return driver.userHasSession(user);
    }

    private void injectOpenAmCookieAndNavigateToPath(User user, String path) throws IOException {
        if (userAlreadyHaveASession(user)) {
            navigateToPath(path);
        } else {
            driver.setUser(user);
            addCookieToBrowser(user);
            navigateToPath(path);
        }
    }

    private void addCookieToBrowser(User user) throws IOException {
        driver.manage().deleteAllCookies();
        driver.loadBaseUrl();
        driver.manage().addCookie(getCookieForUser(user));
    }

    public TestSummaryPage getTestSummaryPage(User tester, Vehicle vehicle) throws URISyntaxException, IOException {
        TestResultsEntryPageInterface testResultsEntryPage = getTestResultsEntryPage(tester, vehicle);
        TestSummaryPage testSummaryPage = testResultsEntryPage.completeTestDetailsWithPassValues().clickReviewTestButton();

        return testSummaryPage;
    }

    private TestResultsEntryPageInterface getTestResultsEntryPage(User tester, Vehicle vehicle) throws URISyntaxException, IOException {
        if (ConfigHelper.isTestResultEntryImprovementsEnabled()) {
            return gotoTestResultsEntryNewPage(tester, vehicle);
        }
        return gotoTestResultsEntryPage(tester, vehicle);
    }

    public final void clickLogout(User user) {
        WebElement logOutLink = driver.findElement(By.id("logout"));
        if (PageInteractionHelper.isElementDisplayed(logOutLink)) {
            logOutLink.click();
            driver.removeUser(user);
        }
    }

    private Cookie getCookieByName(String cookieName) {
        return driver.manage().getCookieNamed(cookieName);
    }

    public Cookie getCurrentTokenCookie() {
        return getCookieByName(CookieService.TOKEN_COOKIE_NAME);
    }

    public Cookie getCurrentSessionCookie() {
        return getCookieByName(CookieService.SESSION_COOKIE_NAME);
    }
}
