package uk.gov.dvsa.ui.pages.enforcement;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationPage;

public class MaskUnmaskConfirmationPage extends Page {

    private String bannerText;

    @FindBy(className = "banner__heading-large") private WebElement confirmationBanner;
    @FindBy(linkText = "Continue to vehicle record") private WebElement returnToVehiclePageLink;

    public MaskUnmaskConfirmationPage(MotAppDriver driver, String bannerTitle) {
        super(driver);
        this.bannerText = bannerTitle;
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.isElementDisplayed(confirmationBanner) && confirmationBanner.getText().contains(bannerText);
    }

    public VehicleInformationPage clickContinueToVehicleRecordLink() {
        returnToVehiclePageLink.click();
        return new VehicleInformationPage(driver);
    }
}
