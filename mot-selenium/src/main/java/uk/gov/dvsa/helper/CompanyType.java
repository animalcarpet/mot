package uk.gov.dvsa.helper;

public enum CompanyType {
    Company("RC", "Company"),
    Partnership("P", "Partnership"),
    SoleTrader("ST","Sole Trader"),
    PublicBody("PA","Public Body");

    private String name;
    private String value;

    CompanyType(String name, String value) {
        this.name = name;
        this.value = value;
    }

    public String getName() {
        return name;
    }

    public String getValue() {
        return value;
    }
}
