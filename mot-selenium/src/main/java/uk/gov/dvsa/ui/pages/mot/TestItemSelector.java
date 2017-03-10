package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.mot.Fault;
import uk.gov.dvsa.framework.config.Configurator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.mot.modal.ManualAdvisoryModalPage;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.mot.modal.ManualReferenceModalPage;
import uk.gov.dvsa.ui.pages.mot.modal.PrsLocationModalPage;
import uk.gov.dvsa.ui.pages.mot.retest.ReTestResultsEntryPage;

import java.util.List;
import java.util.Random;

public class TestItemSelector extends Page {

    @FindBy(tagName = "a") private List<WebElement> links;

    @FindBy(className = "step") private WebElement stepInfo;

    @FindBy(id = "info-message") private WebElement infoMessage;

    @FindBy(linkText = "Manual") private WebElement manualButton;

    @FindBy(className = "modal-title") private WebElement rfrTitle;

    @FindBy(id = "reason-for-rejection-modal") private WebElement reasonsForRejection;

    @FindBy(id = "rfr-modal-close") private WebElement closeRFRList;

    @FindBy(id = "test-item-selector-btn-search") private WebElement searchButton;

    @FindBy(id = "mot-header-details-rfr-done") private WebElement doneButton;

    @FindBy(id = "manual-advisory") private WebElement manualAdvisory;

    @FindBy(id = "rfrCount") private WebElement rfrCount;

    @FindBy(css = ".rfr-list.row > li") private List<WebElement> rfrElements;

    private By searchForRfrField = By.id("rfr-search");
    private By prsButtons = By.cssSelector("[data-type='PRS']");
    private By rfrLink = By.xpath("//*[contains(@href, '#rfr-details-dialog-')]");
    private static final String PAGE_TITLE = "Add PRS, failures and advisories";
    private String mainWindowHandler;

    //Locators not created with Page factory
    By modalTitle = By.className("modal-title");
    By submitModal = By.name("submit");

    public TestItemSelector(MotAppDriver driver) {
        super(driver);
        PageInteractionHelper.refreshPage();
        PageInteractionHelper.waitForElementToBeVisible(manualAdvisory, Configurator.defaultWebElementTimeout);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ReTestResultsEntryPage clickRetestDone() {
        PageInteractionHelper.waitForElementToBeVisible(doneButton, Configurator.defaultFastWebElementTimeout);
        doneButton.click();
        return new ReTestResultsEntryPage(driver);
    }

    public ManualAdvisoryModalPage addManualAdvisory() {
        manualAdvisory.click();
        return new ManualAdvisoryModalPage(driver);
    }

    public TestItemSelector addPRS() {
        driver.findElement(searchForRfrField).sendKeys(Fault.HORN_CONTROL_MISSING.getDescription());
        searchButton.click();

        List<WebElement> prsButtonList = driver.findElements(prsButtons);
        int randomItemIndex = new Random().nextInt(prsButtonList.size());
        String locatorId = prsButtonList.get(randomItemIndex).getAttribute("data-rfrid");
        prsButtonList.get(randomItemIndex).click();

        PrsLocationModalPage modalPage = new PrsLocationModalPage(driver, locatorId);
        modalPage.addPrs();

        return this;
    }

    public TestItemSelector checkRFR() {
        driver.findElement(searchForRfrField).sendKeys(Fault.HORN_CONTROL_MISSING.getDescription());
        searchButton.click();

        WebElement firstReference = rfrElements.get(0);

        String referenceId = driver.findElements(prsButtons).get(0).getAttribute("data-rfrid");
        mainWindowHandler = driver.getWindowHandle();
        firstReference.findElement(rfrLink).click();
        ManualReferenceModalPage manualReferenceModalPage = new ManualReferenceModalPage(driver, referenceId);
        manualReferenceModalPage.clickOnExternalRfrLink();
        return this;
    }

    public boolean isNewWindowOpened() {
        for (String windowHandle : driver.getWindowHandles()) {
            if (!windowHandle.equals(mainWindowHandler)) {
                driver.switchTo().window(windowHandle);
                return driver.getCurrentUrl().contains(driver.getTitle());
            }
        }
        return false;
    }
}
