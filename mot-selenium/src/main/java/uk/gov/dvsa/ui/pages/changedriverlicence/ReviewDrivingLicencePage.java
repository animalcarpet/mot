package uk.gov.dvsa.ui.pages.changedriverlicence;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.profile.UserProfilePage;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;

public class ReviewDrivingLicencePage extends Page {

    private static final String PAGE_TITLE = "Review driving licence";
    @FindBy(id = "submitDrivingLicence") private WebElement changeDrivingLicenceButton;
    @FindBy(linkText = "Back") private WebElement backLink;
    @FindBy(id = "drivingLicenceNumber") private WebElement drivingLicenceNumber;

    public ReviewDrivingLicencePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public ChangeDrivingLicencePage clickBack() {
        backLink.click();
        return new ChangeDrivingLicencePage(driver);
    }

    public String getDrivingLicenceNumber() {
        return drivingLicenceNumber.getText();
    }

    public ProfilePage clickChangeDrivingLicenceButton() {
        changeDrivingLicenceButton.click();
        return new UserProfilePage(driver);
    }
}
