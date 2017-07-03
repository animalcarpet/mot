package uk.gov.dvsa.ui.views.organisation;

import org.testng.Assert;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.authorisedexaminer.AedmAuthorisedExaminerViewPage;
import uk.gov.dvsa.ui.pages.authorisedexaminer.AuthorisedExaminerViewPage;

import java.io.IOException;
import java.net.URISyntaxException;

public class OrganisationTestQualityViewTest extends DslTest {
    private AeDetails ae;
    private Site site;
    private User areaoffice1;

    @BeforeMethod(alwaysRun = true)
    private void setup() throws IOException {
        ae = aeData.createNewAe("TestQuality AE", 100);
        site = siteData.createNewSite(ae.getId(), "TestQuality Site");
        areaoffice1 = motApi.user.createAreaOfficeOne("AO1");
    }
    @Test(groups = {"Regression"}, description = "Verifies that user can view Test Quality for AE with correct navigation")
    public void viewAETestQuality() throws IOException, URISyntaxException {
        //Given that I'm logged in as AO1, I go to Remove site from AE page
        AuthorisedExaminerViewPage aeViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(areaoffice1, AedmAuthorisedExaminerViewPage.class, AedmAuthorisedExaminerViewPage.PATH, ae.getId());

        //When i will go by pages AE -> AE TQI -> VTS TQI -> AE TQI -> AE
        AuthorisedExaminerViewPage authorisedExaminerViewPage = aeViewPage
                .clickServiceReportsLink()
                .clickViewTQIButton(site.getId())
                .clickReturnButtonToAEPage()
                .clickReturnButton();

        //Then on AE Page will be "test quality information" link visible
        Assert.assertTrue(authorisedExaminerViewPage.isServiceReportsLinkDisplayed());
    }
}
