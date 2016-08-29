package uk.gov.dvsa.ui.pages.mot;

import java.util.List;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.apache.commons.lang3.StringUtils;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class SearchForADefectPage extends Page {

    private static final String PAGE_TITLE = "Search for a defect";
    private Pattern searchSummaryPattern = Pattern.compile("^Your search for \"[a-zA-Z0-9 ]+\" returned ([0-9]+) defects");

    @FindBy(id = "global-breadcrumb") private WebElement globalBreadcrumb;
    @FindBy(id = "search-main") private WebElement searchField;
    @FindBy(className = "search-bar__search-submit") private WebElement searchButton;
    @FindBy(className = "search-bar__results") private WebElement searchResultSummary;
    @FindBy(xpath = "//*[@class='search-bar__secondary-action']//a[contains(., 'defect categories')]") private WebElement defectCategoriesLink;
    @FindBy(css = ".content-navigation a") private WebElement finishAndReturnToMOTButton;
    @FindBy(xpath = "//*[@class='panel-indent']//a[contains(., 'Add a manual advisory')]") private WebElement addAManualAdvisory;
    @FindBy(css = "#defects-list li div strong.defect__title") private List<WebElement> searchResults;
    @FindBy(className = "pager") private WebElement pagination;
    @FindBy(className = "pager__prev") private WebElement paginationPrevious;
    @FindBy(className = "pager__next") private WebElement paginationNext;
    @FindBy(css = ".pager__list li a") private List<WebElement> paginationLinks;

    public SearchForADefectPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public SearchForADefectPage searchForDefect(String searchTerm) {
        FormDataHelper.enterText(searchField, searchTerm);
        searchButton.click();
        return this;
    }

    private boolean searchResultsContainsElement(String defectName) {
        for (WebElement result : searchResults) {
            if (result.getText().contains(defectName)) {
                return true;
            }
        }
        return false;
    }

    public DefectCategoriesPage clickDefectCategoriesLink() {
        defectCategoriesLink.click();
        return new DefectCategoriesPage(driver);
    }

    public TestResultsEntryNewPage clickFinishAndReturnToMOTTestButton() {
        finishAndReturnToMOTButton.click();
        return new TestResultsEntryNewPage(driver);
    }

    public boolean checkPageElementsDisplayed() {
        return globalBreadcrumb.getText().contains(PAGE_TITLE) && defectCategoriesLink.isDisplayed()
                && addAManualAdvisory.isDisplayed();
    }

    public boolean checkSearchSummaryCorrect(String searchTerm, String expectedCount) {
        return searchResultSummary.getText().contains(searchTerm) && searchResultSummary.getText().contains(expectedCount);
    }

    public boolean checkSearchResultsCorrect(String searchTerm, String expectedCount, String expectedSearchTerm) {
        return checkSearchSummaryCorrect(searchTerm, expectedCount) && searchResultsContainsElement(expectedSearchTerm);
    }

    public boolean isPaginationDisplayed() {
        return PageInteractionHelper.isElementDisplayed(pagination);
    }

    public boolean isPaginationPreviousDisplayed() {
        return PageInteractionHelper.isElementDisplayed(paginationPrevious);
    }

    public boolean isPaginationNextDisplayed() {
        return PageInteractionHelper.isElementDisplayed(paginationNext);
    }

    private int getSearchResultsCount() {
        if (StringUtils.isNotBlank(searchResultSummary.getText())) {
            Matcher matcher = searchSummaryPattern.matcher(searchResultSummary.getText());
            if (matcher.find()) {
                return Integer.parseInt(matcher.group(1));
            }
        }
        return -1;
    }

    public boolean doesPageLinkExist(int pageNumber) {
        //Check pagination links for this page number
        for (WebElement pageLink : paginationLinks) {
            if (Integer.parseInt(pageLink.getText()) == pageNumber) {
                return true;
            }
        }
        //Page link does not exist so we must already be on that page or we can't click it from here
        return false;
    }

    public SearchForADefectPage navigateToPage(int pageNumber) {
        //Click link to the requested page (if it is on the screen)
        for (WebElement pageLink : paginationLinks) {
            if (Integer.parseInt(pageLink.getText()) == pageNumber) {
                pageLink.click();
                return this;
            }
        }
        //Page link does not exist so we must already be on that page or we can't click it from here
        return this;
    }

    public SearchForADefectPage navigateToLastPage() {
        return navigateToPage((int) Math.ceil((float) getSearchResultsCount() / 10));
    }
}