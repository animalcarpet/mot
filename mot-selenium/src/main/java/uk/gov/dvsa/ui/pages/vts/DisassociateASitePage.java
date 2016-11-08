package uk.gov.dvsa.ui.pages.vts;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.AreaOfficerAuthorisedExaminerViewPage;
import uk.gov.dvsa.ui.pages.Page;

public class DisassociateASitePage extends Page {
    private static final String PAGE_TITLE = "Remove site association";

    @FindBy(id = "status") private WebElement statusDropDown;
    @FindBy(id = "submitAeSiteUnlink") private WebElement confirmAndRemoveThisAssociationButton;

    public DisassociateASitePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public AreaOfficerAuthorisedExaminerViewPage selectStatusAndDisassociateSite(String statusValue) {
        selectStatus(statusValue);
        clickOnConfirmAndRemoveThisAssociationButton();
        return new AreaOfficerAuthorisedExaminerViewPage(driver);
    }

    public DisassociateASitePage selectStatus(String statusValue) {
        FormDataHelper.selectFromDropDownByValue(statusDropDown, statusValue);
        return this;
    }

    public void clickOnConfirmAndRemoveThisAssociationButton() {
        confirmAndRemoveThisAssociationButton.click();
    }
}
