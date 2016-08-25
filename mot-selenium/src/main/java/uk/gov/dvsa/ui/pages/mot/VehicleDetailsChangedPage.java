package uk.gov.dvsa.ui.pages.mot;


import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class VehicleDetailsChangedPage extends Page {

    public static final String PAGE_TITLE = "Vehicle Details Changed";

    @FindBy(id = "oneTimePassword") private WebElement oneTimePassword;
    @FindBy(id = "confirm_vehicle_changes") private WebElement confirmButton;
    @FindBy(id = "declarationStatement") private WebElement declarationElement;

    public VehicleDetailsChangedPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public boolean isDeclarationTextDisplayed() {
        return PageInteractionHelper.isElementDisplayed(declarationElement);
    }

    public boolean isPinBoxDisplayed() { return PageInteractionHelper.isElementDisplayed(oneTimePassword); }

    public String getDeclarationText() {
        return declarationElement.getText();
    }
}
