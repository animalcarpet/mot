package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.ui.pages.Page;

public abstract class AbstractReasonsForRejectionPage extends Page {

    @FindBy(id = "validation-message--success") protected WebElement validationMessage;
    @FindBy(xpath = ".//*[@id='rfrList']//*[@class='defect__title']") protected WebElement reasonForRejectionTitle;
    @FindBy(xpath = "//*[@id='rfrList']//a[contains(., 'Remove')]") protected WebElement removeDefectLink;

    public AbstractReasonsForRejectionPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    public RemoveDefectPage navigateToRemoveDefectPage(Defect defect) {
        removeDefectLink.click();
        return new RemoveDefectPage(driver, defect.getAddOrRemovalType());
    }

    public boolean isDefectInReasonsForRejection(Defect defect) {
        return reasonForRejectionTitle.isDisplayed() && reasonForRejectionTitle.getText().contains(defect.getDefectName());
    }

    public boolean isDefectRemovedSuccessMessageDisplayed(Defect defect) {
        return validationMessage.getText().equals(
                String.format("This %s has been removed:\n%s", defect.getAddOrRemovalType(), defect.getAddOrRemoveName()));
    }
}
