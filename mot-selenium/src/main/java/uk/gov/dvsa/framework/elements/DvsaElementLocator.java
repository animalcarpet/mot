package uk.gov.dvsa.framework.elements;

import org.openqa.selenium.By;
import org.openqa.selenium.SearchContext;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.pagefactory.Annotations;
import org.openqa.selenium.support.pagefactory.ElementLocator;

import java.lang.reflect.Field;
import java.util.List;

public class DvsaElementLocator implements ElementLocator{
    private final SearchContext searchContext;
    private final By by;

    protected DvsaElementLocator(SearchContext searchContext, Field field) {
        this(searchContext, new Annotations(field));
    }

    protected DvsaElementLocator(SearchContext searchContext, Annotations annotations) {
        this.searchContext = searchContext;
        this.by = annotations.buildBy();
    }


    public WebElement findElement() {
        return DvsaWebElement.wrap(searchContext.findElement(by), new FindElementLocator(searchContext, by));
    }

    public List<WebElement> findElements() {
        return DvsaWebElement.wrap(searchContext.findElements(by), new FindElementLocator(searchContext, by));
    }

    @Override
    public String toString() {
        return this.getClass().getSimpleName() + " '" + by + "'";
    }
}
