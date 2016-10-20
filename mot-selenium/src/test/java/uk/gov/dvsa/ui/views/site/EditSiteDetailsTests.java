package uk.gov.dvsa.ui.views.site;

import org.testng.Assert;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.site.Status;
import uk.gov.dvsa.domain.model.site.Type;
import uk.gov.dvsa.domain.model.vehicle.VehicleClass;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.vts.ChangeTestingFacilitiesPage;
import uk.gov.dvsa.ui.pages.vts.ConfirmChangeDetails.ConfirmChangeDetailsClassesPage;
import uk.gov.dvsa.ui.pages.vts.ConfirmTestFacilitiesPage;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

import java.io.IOException;
import java.net.URISyntaxException;
import java.util.Random;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class EditSiteDetailsTests extends DslTest {

    private String onePersonTestLane;
    private String twoPersonTestLane;
    private Site site;
    private User areaOfficeUser;
    private String newSiteName = "Tested Garage";

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        onePersonTestLane = generateTestLaneNumber();
        twoPersonTestLane = generateTestLaneNumber();
        site = siteData.createSite();
        areaOfficeUser = motApi.user.createUserAsAreaOfficeOneUser("dv");
    }

    @Test(groups = {"Regression", "VM-10407"})
    public void changeTestFacilitiesTest() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the change testing facilities page
        ChangeTestingFacilitiesPage changeTestingFacilitiesPage =
                pageNavigator.goToVtsPage(areaOfficeUser, ChangeTestingFacilitiesPage.class, ChangeTestingFacilitiesPage.PATH, site.getId());

        //When I change the site testing facilities details for onePersonTestLane and twoPersonTestLane and submit the request
        ConfirmTestFacilitiesPage confirmTestFacilitiesPage = changeTestingFacilitiesPage
                .selectOnePersonTestLaneNumber(onePersonTestLane)
                .selectTwoPersonTestLaneNumber(twoPersonTestLane)
                .clickOnSaveTestFacilitiesButton();

        //And I confirm my site test facilities changes
        VehicleTestingStationPage finalVehicleTestingStationPage = confirmTestFacilitiesPage.clickOnConfirmButton();

        //Then my changes are displayed on the testing station page
        assertThat(finalVehicleTestingStationPage.verifyOnePersonTestLaneValueDisplayed(), is(onePersonTestLane));
        assertThat(finalVehicleTestingStationPage.verifyTwoPersonTestLaneValueDisplayed(), is(twoPersonTestLane));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeSiteDetailsClasses() throws IOException {
        //Given I am logged in as AO1 & I navigate to the vehicle testing station page
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.goToVtsPage(areaOfficeUser, String.valueOf(site.getId()));

        //When I navigate to change classes and I change data
        ConfirmChangeDetailsClassesPage confirmTestFacilitiesPage =
                vehicleTestingStationPage.clickOnChangeClassesLink()
                        .uncheckAllSelectedClasses()
                        .chooseOption(VehicleClass.three)
                        .clickConfirmationSubmitButton();

        //Then table contains changed classes
        Assert.assertTrue(confirmTestFacilitiesPage.getClasses().equals(VehicleClass.three.getCode()));

        //When I confirm my site classes changes
        VehicleTestingStationPage finalVehicleTestingStationPage = confirmTestFacilitiesPage.clickSubmitButton();

        //Then correct notification is displayed
        Assert.assertTrue(finalVehicleTestingStationPage.getValidationMessage().equals("Classes have been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeSiteDetailsType() throws IOException {
        //Given I am logged in as AO1 & I navigate to the vehicle testing station pag
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.goToVtsPage(areaOfficeUser, String.valueOf(site.getId()));

        //When I navigate to change type and I change data
        VehicleTestingStationPage finalVehicleTestingStationPage =
                vehicleTestingStationPage.clickOnChangeTypeLink()
                        .chooseOption(Type.AREAOFFICE)
                        .clickSubmitButton();

        //Then my changes are displayed on the testing station page
        //And notification is displayed
        Assert.assertTrue(finalVehicleTestingStationPage.getTypeValue().equals(Type.AREAOFFICE.getSiteType()));
        Assert.assertTrue(finalVehicleTestingStationPage.getValidationMessage().equals("Site type has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeSiteDetailsName() throws IOException {
        //Given I am logged in as AO1 & I navigate to the vehicle testing station pag
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.goToVtsPage(areaOfficeUser, String.valueOf(site.getId()));

        //When I navigate to change name and I change data
        VehicleTestingStationPage finalVehicleTestingStationPage =
                vehicleTestingStationPage.clickOnChangeNameLink()
                        .inputSiteDetailsName(newSiteName)
                        .clickSubmitButton();

        //Then my changes are displayed on the testing station page
        //And notification is displayed
        Assert.assertTrue(finalVehicleTestingStationPage.getNameValue().equals(newSiteName));
        Assert.assertTrue(finalVehicleTestingStationPage.getValidationMessage().equals("Site name has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeSiteDetailsStatus() throws IOException {
        //Given I am logged in as AO1 & I navigate to the vehicle testing station pag
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.goToVtsPage(areaOfficeUser, String.valueOf(site.getId()));

        //When I navigate to change status and I change data
        VehicleTestingStationPage finalVehicleTestingStationPage =
                vehicleTestingStationPage.clickOnChangeStatusLink()
                        .changeSiteStatus(Status.REJECTED)
                        .clickSubmitButton();

        //Then my changes are displayed on the testing station page
        //And notification is displayed
        Assert.assertTrue(finalVehicleTestingStationPage.getStatusValue().equals(Status.REJECTED.getText()));
        Assert.assertTrue(finalVehicleTestingStationPage.getValidationMessage().equals("Site status has been successfully changed."));
    }

    private String generateTestLaneNumber() {
        return String.valueOf( 1 + new Random().nextInt(5));
    }
}
