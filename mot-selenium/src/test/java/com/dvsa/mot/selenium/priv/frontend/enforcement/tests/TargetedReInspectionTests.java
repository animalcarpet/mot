package com.dvsa.mot.selenium.priv.frontend.enforcement.tests;

import com.dvsa.mot.selenium.datasource.*;
import com.dvsa.mot.selenium.datasource.braketest.BrakeTestConfiguration4;
import com.dvsa.mot.selenium.datasource.braketest.BrakeTestConfigurationPageField;
import com.dvsa.mot.selenium.datasource.braketest.BrakeTestResults4;
import com.dvsa.mot.selenium.datasource.braketest.BrakeTestResultsPageField;
import com.dvsa.mot.selenium.datasource.enums.PageTitles;
import com.dvsa.mot.selenium.datasource.enums.VehicleClasses;
import com.dvsa.mot.selenium.framework.BaseTest;
import com.dvsa.mot.selenium.framework.Utilities;
import com.dvsa.mot.selenium.priv.frontend.enforcement.pages.*;
import com.dvsa.mot.selenium.priv.frontend.login.pages.LoginPage;
import com.dvsa.mot.selenium.priv.frontend.vehicletest.pages.MotTestPage;
import com.dvsa.mot.selenium.priv.frontend.vehicletest.pages.TestSummary;
import org.testng.Assert;
import org.testng.annotations.Test;

import java.io.IOException;
import java.util.Map;


public class TargetedReInspectionTests extends BaseTest {

    private void loginAsTesterAndDoMOTLogoutAndLoginAsEnfUser(Login login, Vehicle vehicle) {
        RunAClass4MotWithRfrs runMotTest = new RunAClass4MotWithRfrs(driver);
        runMotTest.runMotClass4TestWithSingleRfr(login, vehicle).clickLogout();
        Login enfTester = createVE();
        LoginPage.loginAs(driver, enfTester);
    }

    @Test(groups = "Regression") public void verifyVT20WelshCertificate() throws IOException {

        RunAClass4Mot runMotTest = new RunAClass4Mot(driver);
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        runMotTest.runWelshMotTestPassPrint(Login.LOGIN_MANYVTSTESTER, vehicle, Site.WELSH_GARAGE);

        TestSummary testSummaryPage = new TestSummary(driver);
        String motTestNumber = testSummaryPage.getMotTestNumber();
        testSummaryPage.clickFinishPrint();

        String fileName = testSummaryPage.generateNewVT20FileName();
        String pathNFileName = getErrorScreenshotPath() + "/" + fileName;
        Utilities.copyUrlBytesToFile(testSummaryPage.getPrintCertificateUrl(), driver,
                pathNFileName);

        String parsedText = Utilities.pdfToText(pathNFileName);

        VerifyWelshCertificateDetails ver = new VerifyWelshCertificateDetails();
        Assert.assertEquals(ver.getTitle(parsedText), Text.TEXT_VT20_WELSH_TITLE,
                "Verify VT20 Welsh title");
        Assert.assertTrue(ver.getVT20VT30TestNumber(parsedText).contains(motTestNumber),
                "Verify motTestNumber");
        Assert.assertTrue(ver.getVT20VT30Make(parsedText).contains(vehicle.getCarMake()),
                "Verify car make");
        Assert.assertTrue(ver.getVT20VT30VRM(parsedText).contains(vehicle.carReg),
                "Verify car registration");
        Assert.assertEquals(ver.getWelshCertificateTypes(parsedText), Text.TEXT_VT20_TYPE);

        String Colour = vehicle.primaryColour + " and " + vehicle.secondaryColour;
        Colour = Colour.trim();
        Assert.assertTrue(ver.getVinModelColour(parsedText).contains(vehicle.fullVIN),
                "Verify fullVIN");
        Assert.assertTrue(ver.getVinModelColour(parsedText).contains(vehicle.getCarModel()),
                "Verify car model");
        Assert.assertTrue(ver.getVinModelColour(parsedText).contains(Colour), "Verify car colour");
    }

    @Test(groups = "Regression") public void verifyVT30Certificate() throws IOException {
        RunAClass4Mot runMotTest = new RunAClass4Mot(driver);
        Login login = createTester();
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        runMotTest.runMotTestFailPrint(login, vehicle);
        TestSummary testSummaryPage = new TestSummary(driver);
        String motTestNumber = testSummaryPage.getMotTestNumber();
        testSummaryPage.clickFinishPrint();

        String fileName = testSummaryPage.generateNewVT30FileName();
        String pathNFileName = getErrorScreenshotPath() + "/" + fileName;
        Utilities.copyUrlBytesToFile(testSummaryPage.getPrintCertificateUrl(), driver,
                pathNFileName);

        String parsedText = Utilities.pdfToText(pathNFileName);

        VerifyCertificateDetails ver = new VerifyCertificateDetails();
        Assert.assertEquals(ver.getTitle(parsedText), Text.TEXT_VT30_TITLE, "Verify VT30 title");
        Assert.assertEquals(ver.getVT30TestNumber(parsedText), motTestNumber,
                "Verify MOT Test Number");
        Assert.assertEquals(ver.getVT30Model(parsedText), vehicle.getCarModel(),
                "Verify Car Model");
        Assert.assertEquals(ver.getVT30VRM(parsedText), vehicle.carReg, "Verify car registration");
        Assert.assertEquals(ver.getCertificateTypes(parsedText), Text.TEXT_VT30_TYPE);

        String Colour = vehicle.primaryColour + " and " + vehicle.secondaryColour;
        Colour = Colour.trim();
        Assert.assertTrue(
                ver.getVehicleDetailsFromCertificate(parsedText).contains(vehicle.fullVIN),
                "Verify fullVIN");
    }

    @Test(groups = "Regression") public void verifyVT30WelshCertificate() throws IOException {
        RunAClass4Mot runMotTest = new RunAClass4Mot(driver);
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        runMotTest.runWelshMotTestFailPrint(Login.LOGIN_MANYVTSTESTER, vehicle, Site.WELSH_GARAGE);
        TestSummary testSummaryPage = new TestSummary(driver);
        String motTestNumber = testSummaryPage.getMotTestNumber();
        testSummaryPage.clickFinishPrint();

        String fileName = testSummaryPage.generateNewVT30FileName();
        String pathNFileName = getErrorScreenshotPath() + "/" + fileName;
        Utilities.copyUrlBytesToFile(testSummaryPage.getPrintCertificateUrl(), driver,
                pathNFileName);

        String parsedText = Utilities.pdfToText(pathNFileName);

        VerifyWelshCertificateDetails ver = new VerifyWelshCertificateDetails();
        Assert.assertEquals(ver.getTitle(parsedText), Text.TEXT_VT30_WELSH_TITLE,
                "Verify VT30 Welsh title");
        Assert.assertEquals(ver.getVT20VT30TestNumber(parsedText), motTestNumber,
                "Verify motTestNumber");
        Assert.assertEquals(ver.getVT20VT30Make(parsedText), vehicle.getCarMake(),
                "Verify Car Make");
        Assert.assertEquals(ver.getVT20VT30VRM(parsedText), vehicle.carReg,
                "Verify Car Registration");
        Assert.assertEquals(ver.getWelshCertificateTypes(parsedText), Text.TEXT_VT30_TYPE);

        String Colour = vehicle.primaryColour + " and " + vehicle.secondaryColour;
        Colour = Colour.trim();
        Assert.assertTrue(ver.getVinModelColour(parsedText).contains(vehicle.fullVIN),
                "Verify fullVIN");
        Assert.assertTrue(ver.getVinModelColour(parsedText).contains(vehicle.getCarModel()),
                "Verify Car Model");
        Assert.assertTrue(ver.getVinModelColour(parsedText).contains(Colour), "Verify Car Colour");
    }

    @Test(groups = "Regression") public void verifyVT32Certificate() throws IOException {
        Login login = createTester();
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        RunAClass4MotWithRfrs runMotTest = new RunAClass4MotWithRfrs(driver);
        runMotTest.runMotClass4TestWithAdvisoryRfrs(login, vehicle);
        TestSummary testSummaryPage = new TestSummary(driver);
        String motTestNumber = testSummaryPage.getMotTestNumber();
        testSummaryPage.clickFinishPrint();

        String fileName = testSummaryPage.generateNewVT32FileName();
        String pathNFileName = getErrorScreenshotPath() + "/" + fileName;
        Utilities.copyUrlBytesToFile(testSummaryPage.getPrintCertificateUrl(), driver,
                pathNFileName);

        String parsedText = Utilities.pdfToText(pathNFileName);

        VerifyCertificateDetails ver = new VerifyCertificateDetails();
        Assert.assertEquals(ver.getTitle(parsedText), Text.TEXT_VT20_TITLE, "Verify VT20 title");
        Assert.assertTrue(ver.getVT32Title(parsedText).contains(Text.TEXT_VT32_TITLE),
                "Verify VT32 title");

        Assert.assertTrue(ver.getVT32TestNumber(parsedText).contains(motTestNumber),
                "Verify motTestNumber");
        Assert.assertTrue(ver.getVT32Make(parsedText).contains(vehicle.getCarMake()),
                "Verify car make");
        Assert.assertTrue(ver.getVT32VRM(parsedText).contains(vehicle.carReg),
                "Verify car registration");
        Assert.assertTrue(ver.getVT32VIN(parsedText).contains(vehicle.fullVIN), "Verify fullVIN");
        Assert.assertTrue(ver.getVT32Model(parsedText).contains(vehicle.getCarModel()),
                "Verify Car Model");
    }

    @Test(groups = "Regression") public void verifyWelshVT32Certificate() throws IOException {
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        RunAClass4MotWithRfrs runMotTest = new RunAClass4MotWithRfrs(driver);
        runMotTest.runWelshMotClass4TestWithAdvisoryRfrs(Login.LOGIN_MANYVTSTESTER, vehicle,
                Site.WELSH_GARAGE);
        TestSummary testSummaryPage = new TestSummary(driver);
        String motTestNumber = testSummaryPage.getMotTestNumber();
        testSummaryPage.clickFinishPrint();

        String fileName = testSummaryPage.generateNewVT32FileName();
        String pathNFileName = getErrorScreenshotPath() + "/" + fileName;
        Utilities.copyUrlBytesToFile(testSummaryPage.getPrintCertificateUrl(), driver,
                pathNFileName);

        String parsedText = Utilities.pdfToText(pathNFileName);

        VerifyCertificateDetails ver = new VerifyCertificateDetails();
        Assert.assertEquals(ver.getTitle(parsedText), Text.TEXT_VT20_WELSH_TITLE,
                "Verify Welsh VT20 title");
        Assert.assertEquals(ver.getVT32Title(parsedText), Text.TEXT_VT32_WELSH_TITLE,
                "Verify Welsh VT32 title");

        Assert.assertTrue(ver.getVT32TestNumber(parsedText).contains(motTestNumber),
                "Verify motTestNumber");
        Assert.assertTrue(ver.getWelshVT32Make(parsedText).contains(vehicle.getCarMake()),
                "Verify Car Make");
        Assert.assertTrue(ver.getVT32VRM(parsedText).contains(vehicle.carReg),
                "Verify Car Registration");
        Assert.assertTrue(ver.getWelshVT32VIN(parsedText).contains(vehicle.fullVIN),
                "Verify fullVIN");
        Assert.assertTrue(ver.getVT32Model(parsedText).contains(vehicle.getCarModel()),
                "Verify Car Model");
    }

    @Test(groups = {"VM-1327", "Sprint14", "Enf", "VM-2952", "Sprint25", "Enf", "VM-3125",
            "Sprint24", "Regression"}) public void noDocumentationIfReInspectionHasNoDifferences()
            throws IOException {
        Login login = createTester();
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        loginAsTesterAndDoMOTLogoutAndLoginAsEnfUser(login, vehicle);
        EnforcementHomePage homePage = new EnforcementHomePage(driver);
        homePage.goToVtsNumberEntryPage();
        EnforcementVTSSearchPage searchVTSPage = new EnforcementVTSSearchPage(driver);
        searchVTSPage.selectDropdown(Text.TEXT_ENF_REGISTRATION_SEARCH);
        searchVTSPage.enterSearchCriteria(vehicle.carReg);
        searchVTSPage.clickSearch();
        searchVTSPage.waitForViewLink();
        VtsRecentResultsPage resultsScreen = new VtsRecentResultsPage(driver);
        resultsScreen.selectSummaryLinkFromTable(login, vehicle);
        EnforcementMotTestSummaryPage summaryPage = new EnforcementMotTestSummaryPage(driver);
        summaryPage.startInspection();
        runReInspectionMotTestPass(BrakeTestConfiguration4.enforcement_CASE1(),
                BrakeTestResults4.enforcement_CASE1(),
                PageTitles.MOT_REINSPECTION_PAGE.getPageTitle());
        MotTestSummaryPage motTestSummaryPage = new MotTestSummaryPage(driver);
        Assert.assertEquals(motTestSummaryPage.getTestClass(), VehicleClasses.four.getId(),
                "Verify Vehicle Class");

        Assert.assertTrue(motTestSummaryPage.verifyexpiryDate(), "Verify Expiry Date is displayed");
        Assert.assertEquals(motTestSummaryPage.getExpiryDate(), Text.TEXT_NA.text,
                "Verify Expiry date");
        String motTestNumber = motTestSummaryPage.getMotTestNumber();
        motTestSummaryPage.clickFinishTest();
        EnforcementReInspectionTestCompletePage testCompletePage =
                new EnforcementReInspectionTestCompletePage(driver);

        String fileName = testCompletePage.generateNewVT32FileName();
        String pathNFileName = getErrorScreenshotPath() + "/" + fileName;
        Utilities.copyUrlBytesToFile(testCompletePage.getPrintCertificateUrl(), driver,
                pathNFileName);

        String parsedText = Utilities.pdfToText(pathNFileName);

        VerifyCertificateDetails ver = new VerifyCertificateDetails();
        Assert.assertTrue(ver.getVT32Title(parsedText).contains(Text.TEXT_VT32_TITLE),
                "Verify VT32 title");

        Assert.assertTrue(ver.getVT32TestNumber(parsedText).contains(motTestNumber),
                "Verify motTestNumber");
        Assert.assertEquals(ver.getVT32ReInspectionMake(parsedText), vehicle.getCarMake(),
                "Verify Car Make");
        Assert.assertTrue(ver.getVT32VRM(parsedText).contains(vehicle.carReg),
                "Verify Car Registration");
        Assert.assertEquals(ver.getVT32ReInspectionVIN(parsedText), vehicle.fullVIN,
                "Verify fullVIN");
        Assert.assertTrue(ver.getVT32Model(parsedText).contains(vehicle.getCarModel()),
                "Verify Car Model");

        Assert.assertTrue(testCompletePage.verifyCompareTestsButton(),
                "Verify CompareTests Button");
        testCompletePage.clickCompareTestsButton();
        testCompletePage.clickLogout();
    }

    @Test(groups = {"VM-136", "Sprint14", "Enf", "Regression"})
    public void selectTheCompareResultsOption() {
        Login login = createTester();
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        loginAsTesterAndDoMOTLogoutAndLoginAsEnfUser(login, vehicle);
        EnforcementHomePage homePage = new EnforcementHomePage(driver);
        homePage.goToVtsNumberEntryPage();
        EnforcementVTSSearchPage searchVTSPage = new EnforcementVTSSearchPage(driver);
        searchVTSPage.selectDropdown(Text.TEXT_ENF_REGISTRATION_SEARCH);
        searchVTSPage.enterSearchCriteria(vehicle.carReg);
        searchVTSPage.clickSearch();
        searchVTSPage.waitForViewLink();
        VtsRecentResultsPage resultsScreen = new VtsRecentResultsPage(driver);
        resultsScreen.selectSummaryLinkFromTable(login, vehicle);
        EnforcementMotTestSummaryPage summaryPage = new EnforcementMotTestSummaryPage(driver);
        summaryPage.startInspection();
        runReInspectionMotTestPass(BrakeTestConfiguration4.enforcement_CASE1(),
                BrakeTestResults4.enforcement_CASE1(),
                PageTitles.MOT_REINSPECTION_PAGE.getPageTitle());
        MotTestSummaryPage motTestSummaryPage = new MotTestSummaryPage(driver);

        motTestSummaryPage.clickFinishTest();
        motTestSummaryPage.clickCompareResults();
        motTestSummaryPage.clickLogout();
    }


    @Test(groups = {"VM-681", "Sprint15", "Enf", "Regression"})
    public void assignAScoreToASelectedTestResult() {
        Login login = createTester();
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        loginAsTesterAndDoMOTLogoutAndLoginAsEnfUser(login, vehicle);
        EnforcementHomePage homePage = new EnforcementHomePage(driver);
        homePage.goToVtsNumberEntryPage();
        EnforcementVTSSearchPage searchVTSPage = new EnforcementVTSSearchPage(driver);
        searchVTSPage.selectDropdown(Text.TEXT_ENF_REGISTRATION_SEARCH);
        searchVTSPage.enterSearchCriteria(vehicle.carReg);
        searchVTSPage.clickSearch();
        searchVTSPage.waitForViewLink();
        VtsRecentResultsPage resultsScreen = new VtsRecentResultsPage(driver);
        resultsScreen.selectSummaryLinkFromTable(login, vehicle);
        EnforcementMotTestSummaryPage enfSummaryPage = new EnforcementMotTestSummaryPage(driver);
        enfSummaryPage.startInspection();
        MotTestPage motTestPage =
                new MotTestPage(driver, PageTitles.MOT_REINSPECTION_PAGE.getPageTitle());
        motTestPage.enterOdometerValuesAndSubmit(Text.TEXT_VALID_ODOMETER_MILES,
                PageTitles.MOT_REINSPECTION_PAGE.getPageTitle())
                .addNewBrakeTest(BrakeTestConfiguration4.enforcement_CASE1(),
                        BrakeTestResults4.enforcement_CASE1(),
                        PageTitles.MOT_REINSPECTION_PAGE.getPageTitle()).createCertificate();
        MotTestSummaryPage motTestSummaryPage = new MotTestSummaryPage(driver);

        motTestSummaryPage.clickFinishTest();
        motTestSummaryPage.clickCompareResults();
        EnforcementTestComparisonPage comparisonPage = new EnforcementTestComparisonPage(driver);
        comparisonPage.selectDropdown(
                RunTargetedReInspection.xPathStringsUsedForComparrison.NTSCOREDROPDOWN,
                Text.TEXT_ENF_CUTDOWN_SCORE_FIVE.text);
        comparisonPage.clickLogout();
    }


    @Test(groups = {"VM-1560", "Sprint15", "Enf", "Regression"})
    public void hoverOverScoreInformationIcon() {
        Login login = createTester();
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        loginAsTesterAndDoMOTLogoutAndLoginAsEnfUser(login, vehicle);
        EnforcementHomePage homePage = new EnforcementHomePage(driver);
        homePage.goToVtsNumberEntryPage();
        EnforcementVTSSearchPage searchVTSPage = new EnforcementVTSSearchPage(driver);
        searchVTSPage.selectDropdown(Text.TEXT_ENF_REGISTRATION_SEARCH);
        searchVTSPage.enterSearchCriteria(vehicle.carReg);
        searchVTSPage.clickSearch();
        searchVTSPage.waitForViewLink();
        VtsRecentResultsPage resultsScreen = new VtsRecentResultsPage(driver);
        resultsScreen.selectSummaryLinkFromTable(login, vehicle);
        EnforcementMotTestSummaryPage summaryPage = new EnforcementMotTestSummaryPage(driver);
        summaryPage.startInspection();
        runReInspectionMotTestPass(BrakeTestConfiguration4.enforcement_CASE1(),
                BrakeTestResults4.enforcement_CASE1(),
                PageTitles.MOT_REINSPECTION_PAGE.getPageTitle());
        MotTestSummaryPage motTestSummaryPage = new MotTestSummaryPage(driver);

        motTestSummaryPage.clickFinishTest();
        motTestSummaryPage.clickCompareResults();
        EnforcementTestComparisonPage comparison = new EnforcementTestComparisonPage(driver);
        Assert.assertTrue(comparison.selectScoreInformationIcon(), "Verify Score Information Icon");
        comparison.clickLogout();
    }

    @Test(groups = {"VM-1599", "Sprint15", "Enf", "Regression"})
    public void veSelectsScoreOfTesterTestedItem() {
        Login login = createTester();
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        loginAsTesterAndDoMOTLogoutAndLoginAsEnfUser(login, vehicle);
        EnforcementHomePage homePage = new EnforcementHomePage(driver);
        homePage.goToVtsNumberEntryPage();
        EnforcementVTSSearchPage searchVTSPage = new EnforcementVTSSearchPage(driver);
        searchVTSPage.selectDropdown(Text.TEXT_ENF_REGISTRATION_SEARCH);
        searchVTSPage.enterSearchCriteria(vehicle.carReg);
        searchVTSPage.clickSearch();
        searchVTSPage.waitForViewLink();
        VtsRecentResultsPage resultsScreen = new VtsRecentResultsPage(driver);
        resultsScreen.selectSummaryLinkFromTable(login, vehicle);
        EnforcementMotTestSummaryPage summaryPage = new EnforcementMotTestSummaryPage(driver);
        summaryPage.startInspection();
        MotTestPage motTestPage =
                new MotTestPage(driver, PageTitles.MOT_REINSPECTION_PAGE.getPageTitle());
        motTestPage.enterOdometerValuesAndSubmit(Text.TEXT_VALID_ODOMETER_MILES,
                PageTitles.MOT_REINSPECTION_PAGE.getPageTitle())
                .addNewBrakeTest(BrakeTestConfiguration4.enforcement_CASE1(),
                        BrakeTestResults4.enforcement_CASE1(),
                        PageTitles.MOT_REINSPECTION_PAGE.getPageTitle()).createCertificate();
        MotTestSummaryPage motTestSummaryPage = new MotTestSummaryPage(driver);

        motTestSummaryPage.clickFinishTest();
        motTestSummaryPage.clickCompareResults();
        EnforcementTestComparisonPage comparisonPage = new EnforcementTestComparisonPage(driver);
        comparisonPage.selectDropdown(
                RunTargetedReInspection.xPathStringsUsedForComparrison.NTSCOREDROPDOWN,
                Text.TEXT_ENF_CUTDOWN_SCORE_FIVE.text);
        Assert.assertTrue(comparisonPage.checkDropDownText(
                RunTargetedReInspection.xPathStringsUsedForComparrison.NTSCOREDROPDOWN,
                Text.TEXT_ENF_CUTDOWN_SCORE_FIVE.text), "Verify score selected");
        comparisonPage.clickLogout();
    }


    @Test(groups = {"VM-995", "Sprint15", "Enf", "Regression"}) public void veSelectsCaseOutcome() {
        Login login = createTester();
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        loginAsTesterAndDoMOTLogoutAndLoginAsEnfUser(login, vehicle);
        EnforcementHomePage homePage = new EnforcementHomePage(driver);
        homePage.goToVtsNumberEntryPage();
        EnforcementVTSSearchPage searchVTSPage = new EnforcementVTSSearchPage(driver);
        searchVTSPage.selectDropdown(Text.TEXT_ENF_REGISTRATION_SEARCH);
        searchVTSPage.enterSearchCriteria(vehicle.carReg);
        searchVTSPage.clickSearch();
        searchVTSPage.waitForViewLink();
        VtsRecentResultsPage resultsScreen = new VtsRecentResultsPage(driver);
        resultsScreen.selectSummaryLinkFromTable(login, vehicle);
        EnforcementMotTestSummaryPage summaryPage = new EnforcementMotTestSummaryPage(driver);
        summaryPage.startInspection();
        MotTestPage motTestPage =
                new MotTestPage(driver, PageTitles.MOT_REINSPECTION_PAGE.getPageTitle());
        motTestPage.enterOdometerValuesAndSubmit(Text.TEXT_ENF_COMPLAINT_REFERENCE_NUMBER.text,
                PageTitles.MOT_REINSPECTION_PAGE.getPageTitle())
                .addNewBrakeTest(BrakeTestConfiguration4.enforcement_CASE1(),
                        BrakeTestResults4.enforcement_CASE1(),
                        PageTitles.MOT_REINSPECTION_PAGE.getPageTitle()).createCertificate();
        MotTestSummaryPage motTestSummaryPage = new MotTestSummaryPage(driver);

        motTestSummaryPage.clickFinishTest();
        motTestSummaryPage.clickCompareResults();
        EnforcementTestComparisonPage comparisonPage = new EnforcementTestComparisonPage(driver);
        comparisonPage.selectDropdown(
                RunTargetedReInspection.xPathStringsUsedForComparrison.NTSCOREDROPDOWN,
                Text.TEXT_ENF_CUTDOWN_SCORE_FIVE.text);
        Assert.assertEquals(comparisonPage.getOutcomeText(), Text.TEXT_ENF_NO_FURTHER_ACTION.text,
                "Verify Outcome based on score");
        comparisonPage.selectDropdown(
                RunTargetedReInspection.xPathStringsUsedForComparrison.NTSCOREDROPDOWN,
                Text.TEXT_ENF_CUT_DOWN_SCORE_TWENTY_NO_DEFECT.text);
        Assert.assertEquals(comparisonPage.getOutcomeText(),
                Text.TEXT_ENF_ADVISORY_WARNING_LETTER.text, "Verify Outcome based on score");
        comparisonPage.selectDropdown(
                RunTargetedReInspection.xPathStringsUsedForComparrison.NTSCOREDROPDOWN,
                Text.TEXT_ENF_CUT_DOWN_SCORE_THIRTY.text);
        Assert.assertEquals(comparisonPage.getOutcomeText(), Text.TEXT_ENF_COMPARISON_DAR.text,
                "Verify Outcome based on score");
        comparisonPage.selectDropdown(
                RunTargetedReInspection.xPathStringsUsedForComparrison.NTSCOREDROPDOWN,
                Text.TEXT_ENF_CUTDOWN_SCORE_FORTY.text);
        Assert.assertEquals(comparisonPage.getOutcomeText(), Text.TEXT_ENF_COMPARISON_DAR.text,
                "Verify Outcome based on score");

    }

    @Test(groups = {"VM-1612", "Sprint15", "Enf", "Regression"})
    public void recordVeTargetedReInspectionOutcomeSaveValues() {
        Login login = createTester();
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        loginAsTesterAndDoMOTLogoutAndLoginAsEnfUser(login, vehicle);
        EnforcementHomePage homePage = new EnforcementHomePage(driver);
        homePage.goToVtsNumberEntryPage();
        EnforcementVTSSearchPage searchVTSPage = new EnforcementVTSSearchPage(driver);
        searchVTSPage.selectDropdown(Text.TEXT_ENF_REGISTRATION_SEARCH);
        searchVTSPage.enterSearchCriteria(vehicle.carReg);
        searchVTSPage.clickSearch();
        searchVTSPage.waitForViewLink();
        VtsRecentResultsPage resultsScreen = new VtsRecentResultsPage(driver);
        resultsScreen.selectSummaryLinkFromTable(login, vehicle);
        EnforcementMotTestSummaryPage summaryPage = new EnforcementMotTestSummaryPage(driver);
        summaryPage.startInspection();
        MotTestPage motTestPage =
                new MotTestPage(driver, PageTitles.MOT_REINSPECTION_PAGE.getPageTitle());
        motTestPage.enterOdometerValuesAndSubmit(Text.TEXT_VALID_ODOMETER_MILES,
                PageTitles.MOT_REINSPECTION_PAGE.getPageTitle())
                .addNewBrakeTest(BrakeTestConfiguration4.enforcement_CASE1(),
                        BrakeTestResults4.enforcement_CASE1(),
                        PageTitles.MOT_REINSPECTION_PAGE.getPageTitle()).createCertificate();
        MotTestSummaryPage motTestSummaryPage = new MotTestSummaryPage(driver);

        motTestSummaryPage.clickFinishTest();
        motTestSummaryPage.clickCompareResults();
        EnforcementTestComparisonPage comparisonPage = new EnforcementTestComparisonPage(driver);
        comparisonPage.selectDropdown(
                RunTargetedReInspection.xPathStringsUsedForComparrison.NTSCOREDROPDOWN,
                Text.TEXT_ENF_CUTDOWN_SCORE_FIVE.text);
        comparisonPage.clickLogout();
    }

    /**
     * A method to run an MOT test using vehicle and test data from the data class.
     */
    public void runReInspectionMotTestPass(
            Map<BrakeTestConfigurationPageField, Object> configEntries,
            Map<BrakeTestResultsPageField, Object> resultEntries, String title) {
        MotTestPage motTestPage = new MotTestPage(driver, title);
        motTestPage.addMotTest("12345", configEntries, resultEntries, null, null, null, null, title)
                .createCertificate();

    }

    @Test(groups = {"VM-1755", "Sprint22", "Enf", "Test1", "Regression"}, enabled = false)
    public void verifyDefectCategory() {
        Login login = createTester();
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        loginAsTesterAndDoMOTLogoutAndLoginAsEnfUser(login, vehicle);
        EnforcementHomePage homePage = new EnforcementHomePage(driver);
        homePage.goToVtsNumberEntryPage();
        EnforcementVTSSearchPage searchVTSPage = new EnforcementVTSSearchPage(driver);
        searchVTSPage.selectDropdown(Text.TEXT_ENF_REGISTRATION_SEARCH);
        searchVTSPage.enterSearchCriteria(vehicle.carReg);
        searchVTSPage.clickSearch();
        searchVTSPage.waitForViewLink();
        VtsRecentResultsPage resultsScreen = new VtsRecentResultsPage(driver);
        resultsScreen.selectSummaryLinkFromTable(login, vehicle);
        EnforcementMotTestSummaryPage summaryPage = new EnforcementMotTestSummaryPage(driver);
        summaryPage.startInspection();
        MotTestPage motTestPage =
                new MotTestPage(driver, PageTitles.MOT_REINSPECTION_PAGE.getPageTitle());
        motTestPage.enterOdometerValuesAndSubmit(Text.TEXT_VALID_ODOMETER_MILES)
                .addNewBrakeTest(BrakeTestConfiguration4.enforcement_CASE1(),
                        BrakeTestResults4.enforcement_CASE1()).createCertificate();
        MotTestSummaryPage motTestSummaryPage = new MotTestSummaryPage(driver);

        motTestSummaryPage.clickFinishTest();
        motTestSummaryPage.clickCompareResults();
        EnforcementTestComparisonPage comparisonPage = new EnforcementTestComparisonPage(driver);
        comparisonPage.selectDropdown(
                RunTargetedReInspection.xPathStringsUsedForComparrison.NTSCOREDROPDOWN,
                Text.TEXT_ENF_CUT_DOWN_SCORE_TWENTY_NO_DEFECT.text);
        comparisonPage.selectDropdown(
                RunTargetedReInspection.xPathStringsUsedForComparrison.NTDEFECTDECISIONS,
                Text.TEXT_INCORRECT_DECISION.text);
        Assert.assertFalse(comparisonPage.verifyCategory(), "Verify category is disabled");
        Assert.assertTrue(comparisonPage.getCategoryText().equals(Text.TEXT_NOT_APPLICABLE),
                "Verify category text");
        comparisonPage.clickLogout();
    }

    @Test(groups = {"VM-1890", "Sprint25", "Enf", "Regression"})
    public void verifyResumeReInspectionAfterLogout() {
        Login login = createTester();
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        //loginAsTesterAndDoMOTLogoutAndLoginAsEnfUser(login, vehicle);
        RunAClass4MotWithRfrs runMotTest = new RunAClass4MotWithRfrs(driver);
        runMotTest.runMotClass4TestWithSingleRfr(login, vehicle).clickLogout();
        Login enfTester = createVE();
        LoginPage.loginAs(driver, enfTester);
        EnforcementHomePage homePage = new EnforcementHomePage(driver);
        homePage.goToVtsNumberEntryPage();
        EnforcementVTSSearchPage searchVTSPage = new EnforcementVTSSearchPage(driver);
        searchVTSPage.selectDropdown(Text.TEXT_ENF_REGISTRATION_SEARCH);
        searchVTSPage.enterSearchCriteria(vehicle.carReg);
        searchVTSPage.clickSearch();
        searchVTSPage.waitForViewLink();
        VtsRecentResultsPage resultsScreen = new VtsRecentResultsPage(driver);
        resultsScreen.selectSummaryLinkFromTable(login, vehicle);
        EnforcementMotTestSummaryPage summaryPage = new EnforcementMotTestSummaryPage(driver);
        summaryPage.startInspection();
        summaryPage.logout();
        LoginPage loginPage = new LoginPage(driver);
        loginPage.loginAsEnforcementUser(enfTester);
        homePage = new EnforcementHomePage(driver);
        homePage.clickResumeReInspection();

        MotTestPage motTestPage =
                new MotTestPage(driver, PageTitles.MOT_REINSPECTION_PAGE.getPageTitle());
        motTestPage.enterOdometerValuesAndSubmit(Text.TEXT_VALID_ODOMETER_MILES,
                PageTitles.MOT_REINSPECTION_PAGE.getPageTitle())
                .addNewBrakeTest(BrakeTestConfiguration4.enforcement_CASE1(),
                        BrakeTestResults4.enforcement_CASE1(),
                        PageTitles.MOT_REINSPECTION_PAGE.getPageTitle()).createCertificate();
        MotTestSummaryPage motTestSummaryPage = new MotTestSummaryPage(driver);

        motTestSummaryPage.clickFinishTest();
        motTestSummaryPage.clickCompareResults();
        EnforcementTestComparisonPage comparisonPage = new EnforcementTestComparisonPage(driver);
        comparisonPage.selectDropdown(
                RunTargetedReInspection.xPathStringsUsedForComparrison.NTSCOREDROPDOWN,
                Text.TEXT_ENF_CUTDOWN_SCORE_FIVE.text);
        comparisonPage.clickLogout();
    }

    @Test(groups = {"VM-1890", "Sprint25", "Enf", "Regression"})
    public void verifyResumeReInspectionAfterNavigatedAway() {
        Login login = createTester();
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        loginAsTesterAndDoMOTLogoutAndLoginAsEnfUser(login, vehicle);
        EnforcementHomePage homePage = new EnforcementHomePage(driver);
        homePage.goToVtsNumberEntryPage();
        EnforcementVTSSearchPage searchVTSPage = new EnforcementVTSSearchPage(driver);
        searchVTSPage.selectDropdown(Text.TEXT_ENF_REGISTRATION_SEARCH);
        searchVTSPage.enterSearchCriteria(vehicle.carReg);
        searchVTSPage.clickSearch();
        searchVTSPage.waitForViewLink();
        VtsRecentResultsPage resultsScreen = new VtsRecentResultsPage(driver);
        resultsScreen.selectSummaryLinkFromTable(login, vehicle);
        EnforcementMotTestSummaryPage summaryPage = new EnforcementMotTestSummaryPage(driver);
        summaryPage.startInspection();
        driver.navigate().to("http://google.com");
        driver.navigate().to(baseUrl());
        homePage = new EnforcementHomePage(driver);
        homePage.clickResumeReInspection();

        MotTestPage motTestPage =
                new MotTestPage(driver, PageTitles.MOT_REINSPECTION_PAGE.getPageTitle());
        motTestPage.enterOdometerValuesAndSubmit(Text.TEXT_VALID_ODOMETER_MILES,
                PageTitles.MOT_REINSPECTION_PAGE.getPageTitle())
                .addNewBrakeTest(BrakeTestConfiguration4.enforcement_CASE1(),
                        BrakeTestResults4.enforcement_CASE1(),
                        PageTitles.MOT_REINSPECTION_PAGE.getPageTitle()).createCertificate();
        MotTestSummaryPage motTestSummaryPage = new MotTestSummaryPage(driver);

        motTestSummaryPage.clickFinishTest();
        motTestSummaryPage.clickCompareResults();
        EnforcementTestComparisonPage comparisonPage = new EnforcementTestComparisonPage(driver);
        comparisonPage.selectDropdown(
                RunTargetedReInspection.xPathStringsUsedForComparrison.NTSCOREDROPDOWN,
                Text.TEXT_ENF_CUTDOWN_SCORE_FIVE.text);
    }

    @Test(groups = {"VM-2952", "Sprint25", "Enf", "Regression"})
    public void verifyExpiryDateSingleRfr() {
        Login login = createTester();
        Vehicle vehicle = createVehicle(Vehicle.VEHICLE_CLASS4_CLIO_2004);
        loginAsTesterAndDoMOTLogoutAndLoginAsEnfUser(login, vehicle);
        EnforcementHomePage homePage = new EnforcementHomePage(driver);
        homePage.goToVtsNumberEntryPage();
        EnforcementVTSSearchPage searchVTSPage = new EnforcementVTSSearchPage(driver);
        searchVTSPage.selectDropdown(Text.TEXT_ENF_REGISTRATION_SEARCH);
        searchVTSPage.enterSearchCriteria(vehicle.carReg);
        searchVTSPage.clickSearch();
        searchVTSPage.waitForViewLink();
        VtsRecentResultsPage resultsScreen = new VtsRecentResultsPage(driver);
        resultsScreen.selectSummaryLinkFromTable(login, vehicle);
        EnforcementMotTestSummaryPage summaryPage = new EnforcementMotTestSummaryPage(driver);
        summaryPage.startInspection();

        RunAClass4MotWithRfrs runMotTest = new RunAClass4MotWithRfrs(driver);
        runMotTest.runReInspectionMotTestWithSingleRfr(BrakeTestConfiguration4.enforcement_CASE1(),
                BrakeTestResults4.enforcement_CASE1(),
                PageTitles.MOT_REINSPECTION_PAGE.getPageTitle());
        MotTestSummaryPage motTestSummaryPage = new MotTestSummaryPage(driver);
        Assert.assertFalse(motTestSummaryPage.verifyexpiryDate(),
                "Verify expiry date is displayed");

        motTestSummaryPage.clickFinishTest();
        motTestSummaryPage.clickCompareResults();
        EnforcementTestComparisonPage comparisonPage = new EnforcementTestComparisonPage(driver);
        comparisonPage.selectDropdown(
                RunTargetedReInspection.xPathStringsUsedForComparrison.NTSCOREDROPDOWN,
                Text.TEXT_ENF_CUTDOWN_SCORE_FIVE.text);
    }

}
