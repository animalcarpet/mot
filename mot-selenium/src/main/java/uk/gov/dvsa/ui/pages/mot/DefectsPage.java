package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class DefectsPage extends Page {

    private static final String PAGE_TITLE = "Defects";

    @FindBy(css = "#defects-list .defect") private WebElement defects;
    @FindBy(id = "submit-defect") private WebElement addDefect;
    @FindBy(id = "validation-message--success") private WebElement validationMessage;
    @FindBy(id = "toggleRFRList") private WebElement toggleDefectBasketLink;
    @FindBy(css = "#rfrList ol li.defect") private WebElement reasonsForRejectionList;
    @FindBy(css = "nav.content-navigation a.button") private WebElement finishAndReturnToMOTTestButton;
    @FindBy(xpath = ".//*[@id='rfrList']//*[@class='defect__title']") private WebElement reasonForRejectionTitle;
    @FindBy(xpath = "//*[@id='rfrList']//a[contains(., 'Remove')]") private WebElement removeDefectLink;

    public DefectsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public boolean defectsAreDisplayed() {
        return defects.isDisplayed();
    }

    public DefectsPage navigateToAddDefectPage(Defect defect) {
        WebElement reasonForRejection = defects.findElement(By.xpath(String.format(
                "//*[contains(text(), '%s')]/ancestor::li[contains(@class, 'defect')]", defect.getDefectName())));
        reasonForRejection.findElement(By.linkText(defect.getDefectType())).click();
        return this;
    }

    public RemoveDefectPage navigateToRemoveDefectPage(Defect defect) {
        this.toggleShowDefectBasketLink();
        removeDefectLink.click();

        return new RemoveDefectPage(driver, defect.getAddOrRemovalType());
    }

    public boolean isDefectInReasonsForRejection(Defect defect) {
        this.toggleShowDefectBasketLink();
        return reasonForRejectionTitle.isDisplayed() && reasonForRejectionTitle.getText().contains(defect.getDefectName());
    }

    public DefectsPage clickAddDefectButton() {
        addDefect.click();
        return this;
    }

    public TestResultsEntryNewPage clickFinishAndReturnButton() {
        finishAndReturnToMOTTestButton.click();
        return new TestResultsEntryNewPage(driver);
    }

    public void toggleShowDefectBasketLink() {
        toggleDefectBasketLink.click();
    }

    public boolean isToggleShowDefectBasketDisplayed() {
        return toggleDefectBasketLink.isDisplayed();
    }

    public boolean isDefectAddedSuccessMessageDisplayed(Defect defect) {
        return validationMessage.getText().equals(
                String.format("This %s has been added:\n%s", defect.getAddOrRemovalType(), defect.getAddOrRemoveName()));
    }

    public boolean isDefectRemovedSuccessMessageDisplayed(Defect defect) {
        return validationMessage.getText().equals(
                String.format("This %s has been removed:\n%s", defect.getAddOrRemovalType(), defect.getAddOrRemoveName()));
    }
}