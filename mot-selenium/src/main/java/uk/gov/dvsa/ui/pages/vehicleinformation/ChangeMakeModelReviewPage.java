package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.CountryOfRegistration;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeMakeModelReviewPage extends Page {

    public static final String PATH = "/change/review-make-and-model";
    private static final String PAGE_TITLE = "Review make and model changes";

    @FindBy(id = "submitUpdate") WebElement submit;

    public ChangeMakeModelReviewPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public VehicleInformationPage submit() {
        submit.click();
        return new VehicleInformationPage(driver);
    }
}
