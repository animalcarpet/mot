package uk.gov.dvsa.framework.elements;

import org.openqa.selenium.By;
import org.openqa.selenium.SearchContext;
import org.openqa.selenium.WebElement;

public class FindElementsLocator implements Locator {
    private SearchContext searchContext;
    private By by;
    private int index;

    public FindElementsLocator(SearchContext searchContext, By by, int index) {
        this.searchContext = searchContext;
        this.by = by;
        this.index = index;
    }
    @Override
    public WebElement locate() {
        return searchContext.findElements(by).get(index);
    }
}