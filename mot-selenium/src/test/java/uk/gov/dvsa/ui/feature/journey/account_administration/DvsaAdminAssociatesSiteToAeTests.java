package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.VtsDisassociateStatus;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.authorisedexaminer.AedmAuthorisedExaminerViewPage;
import uk.gov.dvsa.ui.pages.authorisedexaminer.AuthorisedExaminerViewPage;
import uk.gov.dvsa.ui.pages.vts.AssociateASitePage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.CoreMatchers.not;
import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.StringContains.containsString;


public class DvsaAdminAssociatesSiteToAeTests extends DslTest {

    private AeDetails aeDetailsDefault;
    private AeDetails aeDetailsNew;
    private Site siteWithAe;
    private Site siteWithoutAe;

    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        aeDetailsDefault = aeData.createNewAe("DefaultAeDetailsSite", 8);
        siteWithAe = siteData.createNewSite(aeDetailsDefault.getId(), "Test_Site");
        aeDetailsNew = aeData.createNewAe("NewAeDetailsSite", 11);
        siteWithoutAe = siteData.createSiteWithoutAe("New_Test_Site");
    }

    @Test(groups = {"Regression"}, description = "VM-11112")
    public void AO1RemovesSiteFromAE() throws IOException, URISyntaxException {

        //Given that I'm logged in as AO1, I go to Remove site from AE page
        AuthorisedExaminerViewPage authorisedExaminerViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(userData.createAreaOfficeOne("AO1"),
                        AedmAuthorisedExaminerViewPage.class,
                        AedmAuthorisedExaminerViewPage.PATH,
                        aeDetailsDefault.getId()
                );

        //And I remove site from AE
        authorisedExaminerViewPage
                .clickRemoveSiteLink("1")
                .selectStatusAndDisassociateSite(VtsDisassociateStatus.SURRENDERED.getValue());

        //Site is disassociated from the AE so I cannot see it on AE details page anymore
        assertThat(authorisedExaminerViewPage.getSiteContentText(), not(containsString(siteWithAe.getSiteNumber())));
    }

    @Test(groups = {"Regression"}, description = "VM-2526")
    public void AO1AssociatesSiteToAE() throws IOException, URISyntaxException {

        //Given that I'm logged in as AO1, I go to Associate a site page
        AssociateASitePage associateASitePage = pageNavigator
                .goToPageAsAuthorisedExaminer(userData.createAreaOfficeOne("AO1"),
                        AssociateASitePage.class, AssociateASitePage.PATH,
                        aeDetailsNew.getId()
                );

        //And I associate site to AE
        AuthorisedExaminerViewPage authorisedExaminerViewPage = associateASitePage
                .searchForSiteNumberAndAssociate(siteWithoutAe.getSiteNumber());

        //Then site is associated to AE and I can see it on AE details page
        assertThat(authorisedExaminerViewPage.getSiteContentText(), containsString(siteWithoutAe.getSiteNumber()));
    }
}
