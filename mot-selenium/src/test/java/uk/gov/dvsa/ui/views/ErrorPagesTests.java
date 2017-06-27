package uk.gov.dvsa.ui.views;

import org.testng.Assert;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.error.PageNotFoundPage;
import uk.gov.dvsa.ui.pages.error.YouDontHavePremissionPage;

import java.io.IOException;

public class ErrorPagesTests extends DslTest {

    @Test(groups = {"Regression"}, dataProvider = "pageNotFoundDataProvider")
    public void testUnhandled404ExceptionIsTranslatedToNotFoundPage(User areaOfficeUser) throws IOException {
        step("When I go to a page, that throws 404 exception from API ");
        PageNotFoundPage pageNotFoundPage = motUI.organisation.goToNonexistentAe(areaOfficeUser);

        step("Then I should see 404 not found error");
        Assert.assertTrue(pageNotFoundPage.isErrorMessageDisplayed());
    }

    @Test(groups = {"Regression"}, dataProvider = "youDontHavePermissionDataProvider")
    public void testUnhandled403ExceptionIsTranslatedToYouDontHavePermissionPage(User tester, String aeId) throws IOException {
        step("When I go to a page, that throws 403 exception from API ");
        YouDontHavePremissionPage youDontHavePremissionPage = motUI.organisation
            .goToAeWithoutPermission(tester, aeId);

        step("Then I should see 'you dont have premission page'");
        Assert.assertTrue(youDontHavePremissionPage.isErrorMessageDisplayed());
    }

    @Test(groups = {"Regression"}, dataProvider = "pageNotFoundDataProvider")
    public void testPageNotFoundErrorPage(User user) throws IOException {
        step("When I go to a page, that doesn't exist");
        PageNotFoundPage pageNotFound = pageNavigator
            .navigateToPage(user, "/some-page-that-for-sure-desnt-exist", PageNotFoundPage.class);

        step("Then I should see 404 not found error");
        Assert.assertTrue(pageNotFound.isErrorMessageDisplayed());
    }

    @DataProvider(name = "pageNotFoundDataProvider")
    public Object[][] pageNotFoundDataProvider() throws IOException {
        return new Object[][]{
            {motApi.user.createUserAsAreaOfficeOneUser("dv")}
        };
    }

    @DataProvider(name = "youDontHavePermissionDataProvider")
    public Object[][] youDontHavePermissionDataProvider() throws IOException {
        AeDetails ae = aeData.createNewAe("TestQuality AE", 100);
        AeDetails ae2 = aeData.createNewAe("TestQuality AE without permissions", 100);
        Site site = siteData.createNewSite(ae.getId(), "TestQuality Site");

        return new Object[][]{
            {motApi.user.createTester(site.getId()), ae2.getIdAsString()}
        };
    }
}
