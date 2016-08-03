package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.specialnotices.SpecialNoticeAdminPage;
import uk.gov.dvsa.ui.pages.specialnotices.SpecialNoticeCreationPage;
import uk.gov.dvsa.ui.pages.specialnotices.SpecialNoticePage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class SpecialNoticeTests extends DslTest {

    private User schemeUser;
    private User recipient;
    private String specialNoticeTitle;

    @BeforeClass (alwaysRun = true)
    private void setupTestData() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        Site site = siteData.createNewSite(aeDetails.getId(), "SpecialNoticesInc");
        schemeUser = userData.createSchemeUser(false);
        recipient = userData.createTester(site.getId());
        specialNoticeTitle = "Changes to Mot Testing";
    }

    @Test (groups = {"BVT"})
    public void createAndBroadcastSpecialNotice() throws Exception{

        //Given that I am logged as a Scheme user and I am on the create special notices page
        SpecialNoticeCreationPage specialNoticeCreationPage =
            pageNavigator.navigateToPage(schemeUser, SpecialNoticeCreationPage.PATH, SpecialNoticeCreationPage.class);

        //When I create and publish a Special Notice
        SpecialNoticeAdminPage specialNoticeAdminPage =
            specialNoticeCreationPage.createSpecialNoticeSuccessfully(specialNoticeTitle)
                .publishSpecialNotice();

        //And I broadcast the Special Notice
        specialNoticeAdminPage.broadcastNotice(recipient.getUsername(), specialNoticeTitle);

        //Then the recipient should be able to read the Special Notice
        SpecialNoticePage specialNoticePage = pageNavigator.navigateToPage(recipient, SpecialNoticePage.PATH,
                SpecialNoticePage.class);
        assertThat("The recipient can successfully see the special notice",
            specialNoticePage.checkSpecialNoticeListForTitle(specialNoticeTitle), is(true));
    }
}
