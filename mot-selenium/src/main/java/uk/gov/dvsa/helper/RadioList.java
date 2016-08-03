package uk.gov.dvsa.helper;

import org.openqa.selenium.WebElement;

import java.util.List;

public class RadioList {

    private List<WebElement> radioList;

    public RadioList(List<WebElement> radioList) {
        this.radioList = radioList;
    }

    public WebElement findSelected() {

        for (WebElement we : radioList) {
            if (we.isSelected()) {
                return we;
            }
        }
        return null;
    }

    public WebElement findByValue(String value) {
        for (WebElement we : radioList) {
            if (we.getAttribute("value").equalsIgnoreCase(value)) {
                return we;
            }
        }
        return null;
    }
}
