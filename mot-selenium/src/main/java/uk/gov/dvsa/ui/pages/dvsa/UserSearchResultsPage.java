package uk.gov.dvsa.ui.pages.dvsa;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.profile.UserProfilePage;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;

import java.util.ArrayList;
import java.util.List;

public class UserSearchResultsPage extends Page {

    private static final String PAGE_TITLE = "User search";
    @FindBy(id = "results") private WebElement searchResults;
    @FindBy(id = "return_to_user_search") private WebElement backtoUserSearchLink;

    public UserSearchResultsPage(final MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public ProfilePage chooseUser(final int resultPosition) {
        getResultsList().get(resultPosition).click();
        return new UserProfilePage(driver);
    }

    public UserSearchPage clickBackToUserSearch() {
        backtoUserSearchLink.click();
        return new UserSearchPage(driver);
    }

    public List<String> getUserDetails() {
        List<String> userDetails = new ArrayList<>();
        userDetails.add(searchResults.findElement(By.xpath(".//*[@data-element='result-username']")).getText());
        userDetails.add(searchResults.findElement(By.xpath(".//*[@data-element='result-user-address']")).getText());
        userDetails.add(searchResults.findElement(By.xpath(".//*[@data-element='result-user-postcode']")).getText());
        return userDetails;
    }

    public String getDateOfBirth() {
        return searchResults.findElement(By.xpath(".//*[@data-element='result-user-dob']")).getText();
    }

    private List<WebElement> getResultsList() {
        return searchResults.findElements(By.cssSelector("a"));
    }
}

