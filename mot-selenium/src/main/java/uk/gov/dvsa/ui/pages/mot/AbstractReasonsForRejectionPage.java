package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public abstract class AbstractReasonsForRejectionPage extends Page {

    @FindBy(id = "validation-message--success") protected WebElement validationMessage;
    @FindBy(className = "validation-summary") protected WebElement validationSummary;
    @FindBy(xpath = ".//*[@id='rfrList']//*[@class='defect__title']") protected WebElement reasonForRejectionTitle;
    @FindBy(xpath = "//*[@id='rfrList']//a[contains(., 'Remove')]") protected WebElement removeDefectLink;
    @FindBy(xpath = "//*[@id='rfrList']//a[contains(., 'Edit')]") protected WebElement editDefectLink;
    @FindBy(xpath = "//*[@id='rfrList']//*[@value='Undo']") protected WebElement undoLink;
    @FindBy(id="rfrList") private WebElement rfrList;
    protected String defectSelector = "//*[contains(text(), '%s')]/ancestor::li[contains(@class, 'defect')]";
    protected String repairedButton = defectSelector + "//*[@value='Mark as repaired']";

    public AbstractReasonsForRejectionPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    public RemoveDefectPage navigateToRemoveDefectPage(Defect defect) {
        removeDefectLink.click();
        return new RemoveDefectPage(driver, defect.getAddOrRemovalType());
    }

    public EditDefectPage navigateToEditDefectPage(Defect defect) {
        editDefectLink.click();
        return new EditDefectPage(driver, defect.getAddOrRemovalType());
    }

    public <T extends Page> T repairDefect(String defect, Class<T> clazz) {
        driver.findElement(By.xpath(String.format(repairedButton, defect))).click();
        PageInteractionHelper.waitForAjaxToComplete();
        return MotPageFactory.newPage(driver, clazz);
    }

    public <T extends Page> T undoRepairDefect(Class<T> clazz) {
        undoLink.click();
        PageInteractionHelper.waitForAjaxToComplete();
        return MotPageFactory.newPage(driver, clazz);
    }

    public boolean isDefectInReasonsForRejection(Defect defect) {
        return reasonForRejectionTitle.isDisplayed() && reasonForRejectionTitle.getText().contains(defect.getDefectName());
    }

    public boolean isDefectRemovedSuccessMessageDisplayed(Defect defect) {
        return validationMessage.getText().equals(
                String.format("This %s has been removed:\n%s", defect.getAddOrRemovalType(), defect.getAddOrRemoveName()));
    }

    public boolean isDefectAddedSuccessMessageDisplayed(Defect defect) {
        return validationMessage.getText().equals(
                String.format("This %s has been added:\n%s", defect.getAddOrRemovalType(), defect.getAddOrRemoveName()));
    }

    public boolean isManualAdvisoryDefectSuccessMessageDisplayed(Defect defect) {
        return validationMessage.getText().equals(
                String.format("This %s has been added:\n%s", defect.getAddOrRemovalType(), defect.getDescription()));
    }

    public boolean isDefectRepairedSuccessMessageDisplayed(String defectName) {
        return validationMessage.getText().equals(
                String.format("The failure %s has been repaired", defectName));
    }

    public boolean isAdvisoryRepairedSuccessMessageDisplayed(String defectName) {
        return validationMessage.getText().equals(
                String.format("The advisory %s has been removed", defectName));
    }

    public boolean isManualAdvisoryDefectFailureMessageDisplayed() {
        return validationSummary.getText().contains(
                "Manual advisory description - you must give a description"
        );
    }

    public boolean isDefectEditSuccessMessageDisplayed(Defect defect) {
        return validationMessage.getText().equals(
                String.format("This %s has been edited:\n%s", defect.getAddOrRemovalType(), defect.getAddOrRemoveName()));
    }

    public boolean isDefectDangerous(Defect defect) {
        return isDangerousShownForDefect(defect) && defect.getIsDangerous();
    }

    public boolean isUndoLinkDisplayed() {
        PageInteractionHelper.waitForAjaxToComplete();
        return PageInteractionHelper.isElementDisplayed(undoLink);
    }

    public boolean isMarkAsRepairedButtonDisplayed(String defect) {
        return PageInteractionHelper.isElementDisplayed(By.xpath(String.format(repairedButton, defect)));
    }

    private boolean isDangerousShownForDefect(Defect defect) {
        return PageInteractionHelper.isElementDisplayed(By.xpath(String.format(
                "//*[@id='rfrList']//h4[contains(text(),'%s')]/../strong[contains(@class,'defect__is-dangerous')]",
                defect.getAddOrRemoveName())));
    }
}
