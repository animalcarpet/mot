package uk.gov.dvsa.ui.pages.mot.modal;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.mot.Advisory;
import uk.gov.dvsa.framework.config.Configurator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.mot.TestItemSelector;

public class PrsLocationModalPage extends Page {
    private WebElement modalTitle;
    private WebElement lateral;
    private WebElement longitudinal;
    private WebElement vertical;
    private WebElement description;
    private WebElement addButton;
    private WebElement cancelButton;

    @FindBy(className = "validation-summary") private WebElement errorMessages;

    @FindBy(id = "info-message") private WebElement infoMessage;

    private static final String PAGE_TITLE = "PRS location";
    private String rfrId = "";

    public PrsLocationModalPage(MotAppDriver driver, String rfrId) {
        super(driver);
        this.rfrId = rfrId;
        initElements();
        selfVerify();
    }

    private void initElements(){
        modalTitle = driver.findElement(By.id(prepareLocatorWithRfrId("modal-rfr-title-%s")));
        lateral = driver.findElement(By.id(prepareLocatorWithRfrId("lateral-dd-%s")));
        longitudinal = driver.findElement(By.id(prepareLocatorWithRfrId("longitudinal-dd-%s")));
        vertical = driver.findElement(By.id(prepareLocatorWithRfrId("vertical-dd-%s")));
        addButton = driver.findElement(By.id(prepareLocatorWithRfrId("rfr-submit-%s")));
        cancelButton = driver.findElement(By.id(prepareLocatorWithRfrId("rfr-cancel-%s")));
        description = driver.findElement(By.id(prepareLocatorWithRfrId("description-%s")));
    }

    @Override
    protected boolean selfVerify() {
        PageInteractionHelper.waitForElementToBeVisible(modalTitle, Configurator.defaultWebElementTimeout);
        return PageInteractionHelper.verifyTitle(modalTitle.getText(), PAGE_TITLE);
    }

    private String prepareLocatorWithRfrId(String id) {
        return String.format(id, rfrId);
    }

    public TestItemSelector addPrs() {
        FormDataHelper.selectFromDropDownByValue(lateral, String.valueOf(Advisory.Lateral.nearside));
        FormDataHelper.selectFromDropDownByValue(longitudinal, String.valueOf(Advisory.Longitudinal.front));
        FormDataHelper.selectFromDropDownByValue(vertical, String.valueOf(Advisory.Vertical.outer));
        FormDataHelper.enterText(description, "Horn Destroyed");

        addButton.click();
        PageInteractionHelper.refreshPage();
        return new TestItemSelector(driver);
    }
}
