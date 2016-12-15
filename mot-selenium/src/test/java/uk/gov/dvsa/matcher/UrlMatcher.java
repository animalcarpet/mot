package uk.gov.dvsa.matcher;

import org.hamcrest.Description;
import org.hamcrest.TypeSafeMatcher;
import uk.gov.dvsa.helper.PageInteractionHelper;

public class UrlMatcher extends TypeSafeMatcher<String> {
    private String relativeUrl;

    @Override
    protected boolean matchesSafely(String url) {
        relativeUrl = url;
        return PageInteractionHelper.getDriver().getCurrentUrl().contains(url);
    }

    @Override
    public void describeTo(Description description) {
        description.appendText("Current Url should contain: ").appendValue(relativeUrl);
    }

    @Override
    protected void describeMismatchSafely(String item, Description mismatchDescription) {
        mismatchDescription.appendText("Current url is: ").appendValue(PageInteractionHelper.getDriver().getCurrentUrl());
    }

    public static UrlMatcher isPresentInCurrentUrl() {
        return new UrlMatcher();
    }
}
