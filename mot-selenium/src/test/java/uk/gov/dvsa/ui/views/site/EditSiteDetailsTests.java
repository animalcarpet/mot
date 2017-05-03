package uk.gov.dvsa.ui.views.site;

import org.testng.Assert;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.site.Status;
import uk.gov.dvsa.domain.model.site.Type;
import uk.gov.dvsa.domain.model.vehicle.VehicleClass;
import uk.gov.dvsa.helper.enums.TimeFinder;
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

import static uk.gov.dvsa.helper.enums.BrakeTestConstants.BrakeTestType.*;
import static uk.gov.dvsa.helper.enums.DayFinder.*;
import static uk.gov.dvsa.helper.enums.TimeFinder.*;

public class EditSiteDetailsTests extends DslTest {

    private String onePersonTestLane;
    private String twoPersonTestLane;
    private Site site;
    private User areaOfficeUser;
    private User siteManager;
    private String newSiteName = "Tested Garage";

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        onePersonTestLane = generateTestLaneNumber();
        twoPersonTestLane = generateTestLaneNumber();
        site = siteData.createSite();
        areaOfficeUser = motApi.user.createUserAsAreaOfficeOneUser("dv");
        siteManager = motApi.user.createSiteManager(site.getId(), false);
    }

    @Test(groups = {"Regression", "VM-10407"})
    public void changeTestFacilitiesTest() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the change testing facilities page
        ChangeTestingFacilitiesPage changeTestingFacilitiesPage =
                pageNavigator.goToVtsPage(areaOfficeUser,
                        ChangeTestingFacilitiesPage.class,
                        ChangeTestingFacilitiesPage.PATH, site.getId());

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
        //Given I am logged in as AO1 & I navigate to the vehicle testing station page
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
        //Given I am logged in as AO1 & I navigate to the vehicle testing station page
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
        //Given I am logged in as AO1 & I navigate to the vehicle testing station page
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

    @Test(groups = {"Regression"})
    public void changeSiteDetailsTestingHours() throws IOException {
        step("Given I am logged in as Site Manager & I navigate to the vehicle testing station page");
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.goToVtsPage(siteManager, String.valueOf(site.getId()));

        step("When I navigate to the change testing hours page and I change the hours");
        VehicleTestingStationPage changeDetailsTestingHours =
                vehicleTestingStationPage.setTestingHoursForDay()
                        .setTestingHoursForDay(MONDAY, SEVEN_O_CLOCK, AM, TimeFinder.ELEVEN_O_CLOCK, AM, false)
                        .setTestingHoursForDay(TUESDAY, EIGHT_O_CLOCK, AM, TimeFinder.EIGHT_O_CLOCK, PM, false)
                        .setTestingHoursForDay(WEDNESDAY, EIGHT_O_CLOCK, AM, TimeFinder.NINE_O_CLOCK, PM, false)
                        .setTestingHoursForDay(THURSDAY, NINE_O_CLOCK, AM, TimeFinder.SIX_O_CLOCK, PM, false)
                        .setTestingHoursForDay(FRIDAY, TEN_O_CLOCK, AM, TimeFinder.FIVE_O_CLOCK, PM, false)
                        .setTestingHoursForDay(SATURDAY, ELEVEN_O_CLOCK, AM, TimeFinder.FOUR_O_CLOCK, PM, false)
                        .setTestingHoursForDay(SUNDAY, ONE_O_CLOCK, PM, TimeFinder.ELEVEN_O_CLOCK, PM, true)
                        .saveTestingHours();

        step("Then my changes are displayed on the testing station page");
        Assert.assertTrue(changeDetailsTestingHours.getMondayHours().equals("7:00am to 11:00am"),
                "Monday hours are not correct");
        Assert.assertTrue(changeDetailsTestingHours.getTuesdayHours().equals("8:00am to 8:00pm"),
                "Tuesday hours are not correct");
        Assert.assertTrue(changeDetailsTestingHours.getWednesdayHours().equals("8:00am to 9:00pm"),
                "Wednesday hours are not correct");
        Assert.assertTrue(changeDetailsTestingHours.getThursdayHours().equals("9:00am to 6:00pm"),
                "Thursday hours are not correct");
        Assert.assertTrue(changeDetailsTestingHours.getFridayHours().equals("10:00am to 5:00pm"),
                "Friday hours are not correct");
        Assert.assertTrue(changeDetailsTestingHours.getSaturdayHours().equals("11:00am to 4:00pm"),
                "Saturday hours are not correct");
        Assert.assertTrue(changeDetailsTestingHours.getSundayHours().equals("Closed"),
                "Sunday hours are not correct");
    }

    @Test(groups = {"Regression"})
    public void changeSiteDetailsDefaultTestSettings() throws IOException {
        step("Given I am logged in as Site Manager & I navigate to the vehicle testing station page");
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.goToVtsPage(siteManager, String.valueOf(site.getId()));

        step("When I navigate to the change default test settings page and I change the default brake test settings");
        VehicleTestingStationPage changeDetailsDefaultTestSettings =
                vehicleTestingStationPage.clickOnChangeDefaultTestSettingsLink()
                        .changeDefaultTestSettingsClass1And2(Gradient)
                        .changeDefaultTestSettingsClass3AndAbove(Roller, Plate)
                        .clickSaveTestDefaultsButton();

        step("Then my changes are displayed on the testing station page");
        Assert.assertTrue(changeDetailsDefaultTestSettings.getDefaultBrakeTestClass1And2().equals("Gradient"),
                "Class 1 & 2 Brake Defaults are not correct");
        Assert.assertTrue(changeDetailsDefaultTestSettings.getDefaultServiceBrakeTestClass3AndAbove().equals("Roller"),
                "Class 3 & Above Service Brake Defaults are not correct");
        Assert.assertTrue(changeDetailsDefaultTestSettings.getDefaultParkingBrakeTestClass3AndAbove().equals("Plate"),
                "Class 3 & Above Parking Brake Defaults are not correct");
    }

    private String generateTestLaneNumber() {
        return String.valueOf( 1 + new Random().nextInt(5));
    }
}
