package uk.gov.dvsa.ui.pages.changedriverlicence;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.ConfigHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.ProfilePage;
import uk.gov.dvsa.ui.pages.dvsa.UserSearchProfilePage;
import uk.gov.dvsa.ui.pages.profile.NewUserProfilePage;

public class RemoveDriverLicencePage extends Page {

    private static final String PAGE_TITLE = "Remove driving licence";
    @FindBy(id = "removeDrivingLicence") private WebElement removeDrivingLicenceButton;
    @FindBy(linkText = "Back") private WebElement backLink;
    @FindBy(className = "message--important") private WebElement warningMessage;

    public RemoveDriverLicencePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public void clickBackLink() {
        backLink.click();
    }

    public ProfilePage clickRemoveDrivingLicenceButton() {
        removeDrivingLicenceButton.click();
        return MotPageFactory.getProfilePageInstance(new NewUserProfilePage(driver), new UserSearchProfilePage(driver));
    }

    public String getWarningMessage() {
        return warningMessage.getText();
    }
}
