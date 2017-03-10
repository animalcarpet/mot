package uk.gov.dvsa.ui.pages;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.ui.pages.authorisedexaminer.AedmAuthorisedExaminerViewPage;
import uk.gov.dvsa.ui.pages.authorisedexaminer.AuthorisedExaminerViewPage;
import uk.gov.dvsa.ui.pages.braketest.BrakeTestConfigurationPage;
import uk.gov.dvsa.ui.pages.mot.*;
import uk.gov.dvsa.ui.pages.vts.*;

public abstract class PageLocator {

    public static VehicleSearchPage getVehicleSearchPage(MotAppDriver driver) {
        return new VehicleSearchPage(driver);
    }

    public static SearchForAUserPage getVtsSearchForAUserPage(MotAppDriver driver) {
        return new SearchForAUserPage(driver);
    }

    public static BrakeTestConfigurationPage getBrakeTestConfigurationPage(MotAppDriver driver) {
        return new BrakeTestConfigurationPage(driver);
    }

    public static ChooseARolePage getVtsChooseARolePage(MotAppDriver driver) {
        return new ChooseARolePage(driver);
    }

    public static SummaryAndConfirmationPage getVtsSummaryAndConfirmationPage(MotAppDriver driver) {
        return new SummaryAndConfirmationPage(driver);
    }
    
    public static MotTestSearchPage getMotTestSearchPage(MotAppDriver driver) {
        return new MotTestSearchPage(driver);
    }

    public static RetestVehicleSearchPage getRetestVehicleSearchPage(MotAppDriver driver) {
        return new RetestVehicleSearchPage(driver);
    }

    public static MotTestHistoryPage getMotTestHistoryPage(MotAppDriver driver) {
        return new MotTestHistoryPage(driver);
    }

    public static StartRetestConfirmationPage getStartRetestConfirmationPage(MotAppDriver driver) {
        return new StartRetestConfirmationPage(driver);
    }

    public static TestSummaryPage getTestSummaryPage(MotAppDriver driver) {
        return new TestSummaryPage(driver);
    }

    public static TestCompareResultsPage getTestCompareResultsPage(MotAppDriver driver) {
        return new TestCompareResultsPage(driver);
    }

    public static TestCompletePage getTestCompletePage(MotAppDriver driver) {
        return new TestCompletePage(driver);
    }

    public static HomePage getHomePage(MotAppDriver driver) {
        return new HomePage(driver);
    }

    public static SiteNotificationPage getNotificationPage(MotAppDriver driver) {
        return new SiteNotificationPage(driver);
    }

    public static SiteTestQualityPage getSiteTestQualityPage(MotAppDriver driver) {
        return new SiteTestQualityPage(driver);
    }

    public static ContingencyTestEntryPage getContingencyTestEntryPage(MotAppDriver driver) {
        return new ContingencyTestEntryPage(driver);
    }

    public static VehicleTestingStationPage getVehicleTestingStationPage(MotAppDriver driver) {
        return new VehicleTestingStationPage(driver);
    }

    public static AuthorisedExaminerViewPage getAuthorisedExaminerPage(MotAppDriver driver) {
        return new AedmAuthorisedExaminerViewPage(driver);
    }
}
