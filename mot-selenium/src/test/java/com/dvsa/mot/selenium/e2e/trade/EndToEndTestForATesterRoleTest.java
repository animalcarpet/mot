package com.dvsa.mot.selenium.e2e.trade;


import com.dvsa.mot.selenium.datasource.Login;
import com.dvsa.mot.selenium.datasource.Site;
import com.dvsa.mot.selenium.datasource.Text;
import com.dvsa.mot.selenium.datasource.Vehicle;
import com.dvsa.mot.selenium.datasource.braketest.*;
import com.dvsa.mot.selenium.framework.BaseTest;
import com.dvsa.mot.selenium.framework.RandomDataGenerator;
import com.dvsa.mot.selenium.framework.api.MotTestApi;
import com.dvsa.mot.selenium.priv.frontend.vehicletest.braketest.pages.BrakeTestSummaryPage;
import com.dvsa.mot.selenium.priv.frontend.vehicletest.pages.MOTRetestPage;
import com.dvsa.mot.selenium.priv.frontend.vehicletest.pages.TestSummary;
import com.dvsa.mot.selenium.priv.frontend.vehicletest.pages.VehicleSearchPage;
import com.google.common.collect.Lists;
import org.joda.time.DateTime;
import org.testng.Assert;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;

import java.util.Arrays;
import java.util.List;
import java.util.Map;
import java.util.UUID;

import static com.dvsa.mot.selenium.datasource.enums.ContingencyReasons.SYSTEM_OUTAGE;
import static com.dvsa.mot.selenium.priv.frontend.enforcement.pages.ContingencyTestPage.navigateHereFromLoginPage;
import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.is;
import static org.testng.Assert.assertEquals;
import static org.testng.Assert.assertNotNull;
import static org.testng.Assert.assertTrue;

public class EndToEndTestForATesterRoleTest extends BaseTest {


    @Test(dataProvider = "motTestDataForPassAndFailVehicles",
            groups = {"Regression", "VM-1030", "VM-1029", "E2E", "VM-7254", "short-vehicle", "VM-2268",
                    "VM-2269"})

    public void testTesterPerformingEndToEndMotTestForPassAndFailConditions(Vehicle vehicle,
            Map<BrakeTestConfigurationPageField, Object> configurationDetails,
            Map<BrakeTestResultsPageField, Object> resultsDetails, Map<String, String> expResult) {

        //Create unique tester object
        Login uniqueTester = createTester();

        //Create random odometer value
        String odometer = RandomDataGenerator.generateRandomNumber(5, UUID.randomUUID().hashCode());

        BrakeTestSummaryPage brakeTestSummaryPage = BrakeTestSummaryPage
                .navigateHereFromLoginPage(driver, uniqueTester, vehicle, configurationDetails,
                        resultsDetails);
        Assert.assertEquals(expResult, brakeTestSummaryPage.getResultsMap());
        brakeTestSummaryPage.clickDoneButton().enterOdometerValuesAndSubmit(odometer)
                .createCertificate();
        TestSummary testSummaryPage = new TestSummary(driver);

        Assert.assertTrue(testSummaryPage.motTestStatus(), "Assert MOT Test result not displayed");
        assertEquals(testSummaryPage.getRegNumber(), vehicle.carReg, "Assert Reg");
        assertEquals(testSummaryPage.getVin(), vehicle.fullVIN, "Assert VIN");
        assertTrue(testSummaryPage.getColour().contains(vehicle.primaryColour.getColourName()),
                "Assert Colour");
        assertEquals(testSummaryPage.getMake(), vehicle.make.getVehicleMake(), "Assert Make");
        assertEquals(testSummaryPage.getModel(), vehicle.model.getModelName(), "Assert Model");
        assertEquals(String.valueOf(testSummaryPage.getOdometerReading()), odometer);

        //Assert PRS/ RFR failures
        assertThat("There are 0 PRS failures",
                testSummaryPage.getPrsDetails().toUpperCase().contains("NONE RECORDED"), is(true));
        assertThat("There are 0 RFR failures",
                testSummaryPage.getPrsDetails().toUpperCase().contains("NONE RECORDED"), is(true));

        //Get MOT Test Number
        String motTestNumber = testSummaryPage.getMotTestNumber();

        //Assert MOT Test Number is not null
        assertNotNull(motTestNumber);

        testSummaryPage.clickFinishPrint(Text.TEXT_PASSCODE).clickDoneButton();
    }

    /*  Data Provider for pass brake test results */
    private Object[][] motTestPassForClasses1and7() {
        return new Object[][] {{createVehicle(Vehicle.VEHICLE_CLASS1_BALENO_2002),
                BrakeTestConfiguration1And2.brakeTestConfigClasses1And2_CASE1(),
                BrakeTestResults1And2.serviceBrakeControlPassA(),
                BrakeTestSummary1And2.serviceBrakeControlPassAResults},
                {createVehicle(Vehicle.VEHICLE_CLASS7_MERCEDESBENZ_2005),
                        BrakeTestConfiguration7.brakeTestConfigClass7_CASE4(),
                        BrakeTestResults7.passClass7(), BrakeTestSummary7.passResultsClass7}};
    }

    /* Data Provider for fail brake test results*/
    private Object[][] motTestFailForClasses2and4() {
        return new Object[][] {{createVehicle(Vehicle.VEHICLE_CLASS2_CAPPUCCINO_2012),
                BrakeTestConfiguration1And2.brakeTestConfigClasses1And2_CASE1(),
                BrakeTestResults1And2.serviceBrakeControlFailA(),
                BrakeTestSummary1And2.serviceBrakeControlFailAResults},
                {createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004),
                        BrakeTestConfiguration4.brakeTestConfigClass4_PlateAndPlate(),
                        BrakeTestResults4.allFailPlatePlate(),
                        BrakeTestSummary4.allFailResultsPlatePlate}};
    }

    /* Cascaded two Data Providers into one */
    @DataProvider(name = "motTestDataForPassAndFailVehicles")
    public Object[][] motTestDataForPassAndFailVehicles() {

        List<Object[]> result = Lists.newArrayList();
        result.addAll(Arrays.asList(motTestPassForClasses1and7()));
        result.addAll(Arrays.asList(motTestFailForClasses2and4()));
        return result.toArray(new Object[result.size()][]);
    }

    @Test(groups = {"VM-7254", "E2E"}) public void testTesterCanRecordContingencyTest() {

        // Fill the Contingency Test Entry form
        DateTime yesterday = DateTime.now().minusDays(1);
        navigateHereFromLoginPage(driver, Login.LOGIN_TESTER1).
                fillContingencyTestEntryForm(true, "12345A", yesterday.getDayOfMonth(), yesterday.getMonthOfYear(),
                        yesterday.getYear(), SYSTEM_OUTAGE);
        // Conduct a vehicle search
        VehicleSearchPage vehicleSearchPage = new VehicleSearchPage(driver);
        vehicleSearchPage.submitSearch(Vehicle.VEHICLE_CLASS4_CLIO_2004).confirmStartTest()
                .enterOdometerValues("45000").submitOdometer().addBrakeTest().
                enterBrakeConfigurationPageFields(
                        BrakeTestConfiguration7.brakeTestConfigClass7_Roller()).
                submit().enterBrakeResultsPageFields(BrakeTestResults7.passClass7()).
                submit().clickDoneButton().createCertificate().clickFinishPrint(Text.TEXT_PASSCODE);
        TestSummary testSummary = new TestSummary(driver);
        assertTrue(testSummary.printDocButtonExist(),
                "Check that the print button is displayed on page");
    }

    @DataProvider(name = "DP-VehicleClassesToTestViews")
    public Object[][] vehicleClassToTestRetestViews() {
        return new Object[][] {{createVehicle(Vehicle.VEHICLE_CLASS4_BMW_ALPINA_29),
                BrakeTestConfiguration4.brakeTestConfigClass4_CASE4_2Axles(),
                BrakeTestResults4.allPass()}, {createVehicle(Vehicle.VEHICLE_CLASS1_BALENO_2002),
                BrakeTestConfiguration1And2.brakeTestConfigClasses1And2_CASE1(),
                BrakeTestResults1And2.passControlsOver30()}};
    }

    @Test(groups = {"E2E","VM-1585", "VM-7268"}, dataProvider = "DP-VehicleClassesToTestViews")
    public void testVehicleRetestClassViews(Vehicle vehicle,
            Map<BrakeTestConfigurationPageField, Object> configEntries,
            Map<BrakeTestResultsPageField, Object> brakeTestEntries) {
        String motId = createMotTest(login, Site.POPULAR_GARAGES, vehicle, 29000,
                MotTestApi.TestOutcome.FAILED, DateTime.now().minusDays(6));
        MOTRetestPage.navigateHereFromLoginPage(driver, login, motId)
                .enterOdometerValuesAndSubmit("30000").addBrakeTest()
                .enterBrakeConfigurationPageFields(configEntries).submit()
                .enterBrakeResultsPageFields(brakeTestEntries).submit()
                .clickDoneExpectingMotRetestPage().createCertificate()
                .clickFinishPrint(Text.TEXT_PASSCODE).clickDoneButton();
    }

}
