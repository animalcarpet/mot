package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.PageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;

public class MotTestHistoryPage extends Page {

    private static final String PAGE_TITLE = "MOT Test History";
    private static final String TEST_SUMMARY_VIEW_LINK = "mot-%s";

    private WebElement testSummaryViewLinkElement(String testId) {
        return driver.findElement(By.id(String.format(TEST_SUMMARY_VIEW_LINK, testId)));
    }

    private MotAppDriver driver;

    public MotTestHistoryPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
        this.driver = driver;
        PageFactory.initElements(driver, this);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public void selectMotTestFromTableById(String testId){
        testSummaryViewLinkElement(testId).click();
    }
}
