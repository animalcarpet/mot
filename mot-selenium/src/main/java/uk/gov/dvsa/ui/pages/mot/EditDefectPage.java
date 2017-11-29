package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.domain.model.mot.Defect;

public class EditDefectPage extends Page {

    private static final String PAGE_TITLE = "Edit ";
    private static final String BREADCRUMB_TEXT = "Edit ";

    private String defectType;

    @FindBy(id = "global-breadcrumb") private WebElement globalBreadcrumb;
    @FindBy(id = "failureDangerous") private WebElement failureDangerous;
    @FindBy(id = "submit-defect") private WebElement editDefectButton;
    @FindBy(id = "comment") private WebElement commentTextbox;
    @FindBy(xpath = "//*[@class='content-navigation']//a[contains(., 'Cancel')]") private WebElement cancelAndReturnLink;
    @FindBy(id = "locationLateral") private WebElement locationLateralDropdown;
    @FindBy(id = "locationLongitudinal") private WebElement locationLongitudinalDropdown;
    @FindBy(id = "locationVertical") private WebElement locationVerticalDropdown;

    public EditDefectPage(MotAppDriver driver, String defectType) {
        super(driver);
        this.defectType = defectType;
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE + defectType);
    }

    public <T extends Page> T cancelAndReturnToPage(Class<T> returnPage) {
        cancelAndReturnLink.click();
        return MotPageFactory.newPage(driver, returnPage);
    }

    public <T extends Page> T clickEditAndReturnToPage(Class<T> returnPage) {
        editDefectButton.click();
        return MotPageFactory.newPage(driver, returnPage);
    }

    public EditDefectPage clickIsDangerous(Defect defect) {
        failureDangerous.click();
        defect.setIsDangerous(true);
        return new EditDefectPage(driver, defect.getAddOrRemovalType());
    }

    public EditDefectPage unsetIsDangerous(Defect defect) {
        failureDangerous.click();
        defect.setIsDangerous(false);
        return new EditDefectPage(driver, defect.getAddOrRemovalType());
    }

    public boolean isDangerousChecked() {
        return failureDangerous.isSelected();
    }

    public EditDefectPage addComment(String comment) {
        FormDataHelper.enterText(commentTextbox, comment);
        return this;
    }

    public String getComment() {
        return commentTextbox.getText();
    }

    public EditDefectPage setLocationLateral(String locationLateral) {
        FormDataHelper.selectFromDropDownByVisibleText(locationLateralDropdown, locationLateral);
        return this;
    }

    public String getLocationLateral() {
        return FormDataHelper.getSelectedTextFromDropdown(locationLateralDropdown);
    }

    public EditDefectPage setLocationLongitudinal(String locationLongitudinal) {
        FormDataHelper.selectFromDropDownByVisibleText(locationLongitudinalDropdown, locationLongitudinal);
        return this;
    }

    public String getLocationLongitudinal() {
        return FormDataHelper.getSelectedTextFromDropdown(locationLongitudinalDropdown);
    }

    public EditDefectPage setLocationVertical(String locationVertical) {
        FormDataHelper.selectFromDropDownByVisibleText(locationVerticalDropdown, locationVertical);
        return this;
    }

    public String getLocationVertical() {
        return FormDataHelper.getSelectedTextFromDropdown(locationVerticalDropdown);
    }

    public boolean checkBreadcrumbExists() { return globalBreadcrumb.getText().contains(BREADCRUMB_TEXT + defectType); }

    public boolean checkRemoveButtonExists() {
        return editDefectButton.getText().contains(PAGE_TITLE + defectType);
    }
}
