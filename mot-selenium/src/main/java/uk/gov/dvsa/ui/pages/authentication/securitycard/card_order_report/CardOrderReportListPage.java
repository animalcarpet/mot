package uk.gov.dvsa.ui.pages.authentication.securitycard.card_order_report;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

import java.util.List;

public class CardOrderReportListPage extends Page {

    private static final String PAGE_TITLE = "List of ordered security cards from the last 7 days";
    public static final String PATH = "/security-card-order-report-list";

    public CardOrderReportListPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }


    public boolean containsLinksToOrderReportFor7Days() {
        List<WebElement> reportRows = driver.findElements(By.xpath("//tr[starts-with(@id,'order-list-row')]"));
        return reportRows.size() == 7;
    }
}
