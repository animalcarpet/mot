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

    public DvsaElementLocator(SearchContext searchContext, Field field) {
        this(searchContext, new Annotations(field));
    }

    public DvsaElementLocator(SearchContext searchContext, Annotations annotations) {
        this.searchContext = searchContext;
        this.by = annotations.buildBy();
    }


    public WebElement findElement() {
        WebElement element = DvsaWebElement.wrap(searchContext.findElement(by), new FindElementLocator(searchContext, by));
        return element;
    }

    public List<WebElement> findElements() {
        List<WebElement> elements = DvsaWebElement.wrap(searchContext.findElements(by), new FindElementLocator(searchContext, by));
        return elements;
    }

    @Override
    public String toString() {
        return this.getClass().getSimpleName() + " '" + by + "'";
    }
}
