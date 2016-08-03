package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class TransactionHistoryPage extends Page {

    private static final String PAGE_TITLE = "Purchase history";

    @FindBy(xpath = "(//td[@class='numeric'])[1]") private WebElement adjustmentQuantity;
    @FindBy(id = "summaryLine") private WebElement numberOfTransactions;
    @FindBy(id = "today") private WebElement todayTransactionsLink;
    @FindBy(id = "last7Days") private WebElement last7DaysTransactionsLink;
    @FindBy(id = "last30Days") private WebElement last30DaysTransactionsLink;
    @FindBy(id = "transactionHistoryTable") private WebElement transactionsTable;
    @FindBy(id = "downloadFile") private WebElement downloadFiles;

    public TransactionHistoryPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public String getAdjustmentQuantity() {
        return adjustmentQuantity.getText();
    }

    public boolean isNumberOfTransactionsDisplayed() {
        return numberOfTransactions.isDisplayed();
    }

    public String getNumberOfTransactionsText() {
        return numberOfTransactions.getText();
    }

    public boolean isTransactionsTableDisplayed() {
        return driver.findElements(By.id("transactionHistoryTable")).size() > 0;
    }

    public boolean isDownloadFileOptionsDisplayed() {
        return downloadFiles.isDisplayed();
    }

    public TransactionHistoryPage clickTodayTransactionsLink() {
        todayTransactionsLink.click();
        return this;
    }

    public TransactionHistoryPage clickLast7DaysTransactionsLink() {
        last7DaysTransactionsLink.click();
        return this;
    }

    public TransactionHistoryPage clickLast30DaysTransactionsLink() {
        last30DaysTransactionsLink.click();
        return this;
    }
}
