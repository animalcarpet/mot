package uk.gov.dvsa.framework.elements;

import org.openqa.selenium.SearchContext;
import org.openqa.selenium.support.pagefactory.ElementLocator;
import org.openqa.selenium.support.pagefactory.ElementLocatorFactory;

import java.lang.reflect.Field;

public class DvsaElementLocatorFactory implements ElementLocatorFactory {
    private final SearchContext searchContext;

    public DvsaElementLocatorFactory(SearchContext searchContext) {
        this.searchContext = searchContext;
    }

    @Override
    public ElementLocator createLocator(Field field) {
        return new DvsaElementLocator(searchContext, field);
    }
}
