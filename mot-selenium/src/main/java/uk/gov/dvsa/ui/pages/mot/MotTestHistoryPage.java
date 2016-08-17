package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.PageFactory;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class MotTestHistoryPage extends Page {

    private static final String PAGE_TITLE = "MOT Test History";
    private static final String TEST_SUMMARY_VIEW_LINK = "mot-%s";

    private void clickSummaryViewLinkElement(String testId) {
        clickElement(By.id(String.format(TEST_SUMMARY_VIEW_LINK, testId)));
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
        clickSummaryViewLinkElement(testId);
    }

    public <T extends Page> T selectMotTestFromTableById(String testId, Class<T> clazz){
        selectMotTestFromTableById(testId);

        return MotPageFactory.newPage(driver, clazz);
    }
}
