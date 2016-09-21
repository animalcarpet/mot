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

public class ManualAdvisoryModalPage extends Page {

    @FindBy(id = "modal-rfr-title-0") protected WebElement modalTitle;

    @FindBy(id = "lateral-dd-0") protected WebElement lateral;

    @FindBy(id = "longitudinal-dd-0") protected WebElement longitudinal;

    @FindBy(id = "vertical-dd-0") protected WebElement vertical;

    @FindBy(id = "description-0") protected WebElement description;

    @FindBy(css = "input[id=dangerous]") protected WebElement dangerousFailure;

    @FindBy(id = "rfr-submit-0") protected WebElement addButton;

    @FindBy(id = "rfr-cancel-0") protected WebElement cancelButton;

    private By errorMessages = By.className("validation-summary");

    @FindBy(id = "info-message") protected WebElement infoMessage;

    private static final String PAGE_TITLE = "Manual Advisory";

    private static final String PROFANITY_MESSAGE = "Profanity has been detected in the description of RFR";

    public ManualAdvisoryModalPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        PageInteractionHelper.waitForElementToBeVisible(modalTitle, Configurator.defaultWebElementTimeout);
        return PageInteractionHelper.verifyTitle(modalTitle.getText(), PAGE_TITLE);
    }

    public TestItemSelector addAdvisory(String description) {
        enterAdvisory(description);
        addButton.click();
        return new TestItemSelector(driver);
    }

    public ManualAdvisoryModalPage addAdvisoryWithProfaneDescription(String description){
        enterAdvisory(description);
        addButton.click();
        return this;
    }

    private void enterAdvisory(String description) {
        FormDataHelper.selectFromDropDownByValue(lateral, String.valueOf(Advisory.Lateral.nearside));
        FormDataHelper.selectFromDropDownByValue(longitudinal, String.valueOf(Advisory.Longitudinal.rear));
        FormDataHelper.selectFromDropDownByValue(vertical, String.valueOf(Advisory.Vertical.lower));

        FormDataHelper.enterText(this.description, description);
        FormDataHelper.selectInputBox(dangerousFailure);
    }

    public String getValidationMessage() {
        return driver.findElement(errorMessages).getText();
    }

    public boolean isProfanityWarningDisplayed() {
        return driver.findElement(errorMessages).getText().contains(PROFANITY_MESSAGE);
    }
}
