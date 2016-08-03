package uk.gov.dvsa.ui.pages.vts;


import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

import java.util.List;

public class UserTestQualityPage extends Page {
    public static final String PATH = "/vehicle-testing-station/%s/test-quality/user/%s/group/%s";
    private static final String PAGE_TITLE = "Test Quality information";

    @FindBy(id="return-link")private WebElement returnLink;
    @FindBy(id="tester-test-count")private WebElement testerTestCount;
    @FindBy(id="tester-failure-percentage")private WebElement testerFailurePercentage;
    @FindBy(id="tester-average-duration")private WebElement testerAverageDuration;

    public UserTestQualityPage(MotAppDriver driver) {
        super(driver);
    }

    @Override protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public int getTestCount(){
        return Integer.parseInt(testerTestCount.getText());
    }

    public boolean isReturnLinkDisplayed()
    {
        return returnLink.isDisplayed();
    }

    public boolean testerAverageEquals(String rfrCategory, int testerAverageValue){
        List<WebElement> tableRows = driver.findElements(By.cssSelector("#tqi-component-average tbody tr"));

        for (WebElement row: tableRows){
            if(row.findElement(By.className("tqi-category-name")).getText().contains(rfrCategory)) {
                return row.findElement(By.className("tqi-tester-value")).getText().contains(Integer.toString(testerAverageValue));
            }
        }

        return false;
    }
}