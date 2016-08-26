package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
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
    @FindBy(xpath = "//*[@class='content-navigation']//a[contains(., 'Cancel')]") private WebElement cancelAndReturnLink;

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

    public boolean checkBreadcrumbExists() { return globalBreadcrumb.getText().contains(BREADCRUMB_TEXT + defectType); }

    public boolean checkRemoveButtonExists() {
        return editDefectButton.getText().contains(PAGE_TITLE + defectType);
    }
}