package uk.gov.dvsa.tools.test;

public class TestMethod {
    private String name;
    private String description;

    public TestMethod(String name, String description) {
        this.name = name;
        this.description = description;
    }

    public String getName() {
        return name;
    }

    public String getDescription() {
        return description;
    }
}
