package uk.gov.dvsa.ui;

import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Listeners;
import ru.yandex.qatools.allure.annotations.Step;
import uk.gov.dvsa.data.*;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.domain.workflow.RoleAssociationWorkflow;
import uk.gov.dvsa.domain.workflow.VehicleReInspectionWorkflow;
import uk.gov.dvsa.framework.config.Configurator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.framework.config.webdriver.WebDriverConfigurator;
import uk.gov.dvsa.framework.listeners.TestListener;
import uk.gov.dvsa.helper.Utilities;
import uk.gov.dvsa.module.userprofile.AnnualAssessmentCertificates;
import uk.gov.dvsa.shared.MotApi;
import uk.gov.dvsa.shared.MotUI;

@Listeners(TestListener.class)
public abstract class DslTest {

    private MotAppDriver driver = null;
    protected AeData aeData = new AeData();
    protected SiteData siteData = new SiteData();
    protected UserData userData = new UserData();
    protected QualificationDetailsData qualificationDetailsData = new QualificationDetailsData();
    protected AnnualAssessmentCertificatesData annualAssessmentCertificatesData = new AnnualAssessmentCertificatesData();
    protected VehicleData vehicleData = new VehicleData();
    protected MotApi motApi = new MotApi();
    protected MotUI motUI;

    private static final ThreadLocal<WebDriverConfigurator> webDriverConfigurator =
            new ThreadLocal<>();

    protected PageNavigator pageNavigator = new PageNavigator();
    private RoleAssociationWorkflow roleAssociationWorkflow = new RoleAssociationWorkflow();
    private VehicleReInspectionWorkflow vehicleReInspectionWorkflow = new VehicleReInspectionWorkflow();

    protected RoleAssociationWorkflow roleAssociationWorkflow() {
        return roleAssociationWorkflow;
    }

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
        roleAssociationWorkflow.setDriver(driver);
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
            if (null != cachedDriver) {
                Utilities.Logger.LogError("Tearing down webdriver because of test failure");
                cachedDriver.destroy();
                webDriverConfigurator.set(null);
            }
            driver = null;
        }
    }
}
