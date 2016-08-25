package uk.gov.dvsa.ui.pages.profile.qualificationdetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class QualificationDetailsConfirmationPage extends Page {
    public static final String PATH = "/your-profile/qualification-details/a/add/confirmation";

    @FindBy(id = "qualification-details-confirmation-header") private WebElement header;
    @FindBy(id = "order-card-section") private WebElement orderCardSection;
    @FindBy(css = "a[href*='security-card-order/new']") private WebElement orderCardLink;
    @FindBy(id = "return-to-qualification-details") private WebElement returnToQualificationDetails;


    public QualificationDetailsConfirmationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.isElementDisplayed(header);
    }

    public QualificationDetailsPage returnToQualificationDetailsPage() {
        returnToQualificationDetails.click();
        return new QualificationDetailsPage(driver);
    }

    public boolean isOrderCardLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(orderCardLink);
    }
}
