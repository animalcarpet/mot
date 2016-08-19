package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;

public abstract class AbstractDefectsBasketPage extends AbstractReasonsForRejectionPage {

    @FindBy(id = "toggleRFRList") private WebElement showDefectsLink;

    public AbstractDefectsBasketPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    public RemoveDefectPage navigateToRemoveDefectPage(Defect defect) {
        this.toggleShowDefectBasketLink();
        return super.navigateToRemoveDefectPage(defect);
    }

    @Override
    public boolean isDefectInReasonsForRejection(Defect defect) {
        this.toggleShowDefectBasketLink();
        return super.isDefectInReasonsForRejection(defect);
    }

    public void toggleShowDefectBasketLink() {
        showDefectsLink.click();
    }
}
