package uk.gov.dvsa.framework.elements;

import org.openqa.selenium.By;
import org.openqa.selenium.SearchContext;
import org.openqa.selenium.WebElement;

public class FindElementLocator implements Locator {
    private SearchContext searchContext;
    private By by;

    public FindElementLocator(SearchContext searchContext, By by) {
        this.searchContext = searchContext;
        this.by = by;
    }

    @Override
    public WebElement locate() {
        return searchContext.findElement(by);
    }
}