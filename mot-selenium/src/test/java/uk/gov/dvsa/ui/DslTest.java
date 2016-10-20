package uk.gov.dvsa.ui;

import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Listeners;
import ru.yandex.qatools.allure.annotations.Step;
import uk.gov.dvsa.data.*;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.domain.workflow.VehicleReInspectionWorkflow;
import uk.gov.dvsa.framework.config.Configurator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.framework.config.webdriver.WebDriverConfigurator;
import uk.gov.dvsa.framework.listeners.TestListener;
import uk.gov.dvsa.shared.MotApi;
import uk.gov.dvsa.shared.MotUI;
import uk.gov.dvsa.helper.Utilities.Logger;

import java.text.SimpleDateFormat;
import java.util.Date;

@Listeners(TestListener.class)
public abstract class DslTest {

    protected String message;
    private MotAppDriver driver = null;
    protected AeData aeData = new AeData();
    protected SiteData siteData = new SiteData();
    protected FrontendData frontendData = new FrontendData();
    protected QualificationDetailsData qualificationDetailsData = new QualificationDetailsData();
    protected AnnualAssessmentCertificatesData annualAssessmentCertificatesData = new AnnualAssessmentCertificatesData();
    protected VehicleData vehicleData = new VehicleData();
    protected MotApi motApi = new MotApi();
    protected MotUI motUI;
    protected static SimpleDateFormat screenshotDateFormat =
            new SimpleDateFormat("yyyyMMdd-HHmmss");

    private static final ThreadLocal<WebDriverConfigurator> webDriverConfigurator =
            new ThreadLocal<>();

    protected PageNavigator pageNavigator = new PageNavigator();
     private VehicleReInspectionWorkflow vehicleReInspectionWorkflow = new VehicleReInspectionWorkflow();
    protected VehicleReInspectionWorkflow vehicleReinspectionWorkflow() {
        return vehicleReInspectionWorkflow;
    }

    /**
     * This is an allure report annotation
     * When used, the report will printout the content of String value
     * See uk.gov.dvsa.ui.views.EventHistoryViewTests for usage
     * */
    @Step("{0}")
    protected void step(String value){}

    @BeforeMethod(alwaysRun = true)
    public void setupBaseTest() {
        if (null == webDriverConfigurator.get()) {
            webDriverConfigurator.set(new WebDriverConfigurator());
        }

        driver = webDriverConfigurator.get().getDriver();
        pageNavigator.setDriver(driver);
        motUI = new MotUI(driver);
        vehicleReInspectionWorkflow.setDriver(driver);

        driver.setBaseUrl(Configurator.baseUrl());
    }

    @AfterMethod(alwaysRun = true)
    public void tearDown(ITestResult result) {
        if (result.isSuccess()) {
            if (null != driver) {
                driver.manage().deleteAllCookies();
            }
        } else {
            WebDriverConfigurator cachedDriver = webDriverConfigurator.get();

            // Take screenshot on test failure
            if (cachedDriver != null && result.getStatus() == ITestResult.FAILURE && Configurator.isErrorScreenshotEnabled()) {
                driver.takeScreenShot(result.getTestClass().getName().replace("uk.gov.dvsa.ui", "")
                                + "." + result.getName() + "_" + screenshotDateFormat.format(new Date())
                                + ".png", Configurator.getErrorScreenshotPath() + "/" + Configurator.getBuildNumber());
            }

            if (null != cachedDriver) {
                Logger.LogError("Tearing down webdriver because of test failure");
                cachedDriver.destroy();
                webDriverConfigurator.set(null);
            }
            driver = null;
        }
    }
}
