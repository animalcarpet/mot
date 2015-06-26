package uk.gov.dvsa.ui;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.service.ServiceLocator;
import uk.gov.dvsa.ui.pages.NotificationPage;
import uk.gov.dvsa.ui.pages.VehicleTestingStationPage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class AedmAssociatesTesterToOtherRoleTest extends BaseTest {

    private AeDetails aeDetails;
    private Site testSite;
    private User aedm;
    private User tester;
    private String vtsNumber;

    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        aeDetails = ServiceLocator.getAeService().createAe("New", 8);
        aedm = ServiceLocator.getUserService().createUserAsAedm(aeDetails.getId(), "My_Aedm", false);
        testSite = ServiceLocator.getSiteService().createSite(aeDetails.getId(), "Test_Site");
        tester = ServiceLocator.getUserService().createUserAsTester(testSite.getId());
        vtsNumber = String.valueOf(testSite.getId());
    }

    @Test (groups = { "BVT" }, description = "VM-9857 Journey 4 - AEDM Associates Tester or other Role")
    public void aedmAssociatesRoleToTesterAndTesterAccepts() throws IOException{

        //Given I'm on VTS Choose A Role page
        roleAssociationWorkflow().asAedmNavigateToVtsChooseARolePage(aedm, tester, vtsNumber);

        //When I assign tester role to the user
        VehicleTestingStationPage vehicleTestingStationPage = roleAssociationWorkflow().assignSiteAdminRoleToUser();

        assertThat(vehicleTestingStationPage.isValidationMessageSuccessDisplayed(), is(true));

        //And I log out as aedm
        pageNavigator().signOutAndGoToLoginPage();

        //And the tester accepts the role nomination
        roleAssociationWorkflow().navigateToHomePage(tester);
        roleAssociationWorkflow().acceptNomination();

        //Then Aedm gets notification that Tester is assigned to the role
        NotificationPage notificationPage = roleAssociationWorkflow().loginAndNavigateToLastReceivedNotification(aedm);

        assertThat(notificationPage.isNotificationStatusAccepted(), is(true));
    }

    @Test (groups = { "BVT" }, description = "VM-9857 Journey 4 - AEDM Associates Tester or other Role")
    public void aedmAssociatesRoleToTesterAndTesterRejects() throws IOException{

        //Given I'm on VTS Choose A Role page
        roleAssociationWorkflow().asAedmNavigateToVtsChooseARolePage(aedm, tester, vtsNumber);

        //When I assign site manager role to the user
        VehicleTestingStationPage vehicleTestingStationPage = roleAssociationWorkflow().assignSiteManagerRoleToUser();

        assertThat(vehicleTestingStationPage.isValidationMessageSuccessDisplayed(), is(true));

        //And I log out as aedm
        pageNavigator().signOutAndGoToLoginPage();

        //And the tester rejects the role nomination
        roleAssociationWorkflow().navigateToHomePage(tester);
        roleAssociationWorkflow().rejectNomination();

        //Then Aedm gets notification that Tester is assigned to the role
        NotificationPage notificationPage = roleAssociationWorkflow().loginAndNavigateToLastReceivedNotification(aedm);

        assertThat(notificationPage.isNotificationStatusAccepted(), is(false));
    }
}