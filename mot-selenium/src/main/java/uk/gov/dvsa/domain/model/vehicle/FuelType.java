package uk.gov.dvsa.domain.model.vehicle;


public class FuelType {

    private String code;

    private String name;

    public String getCode() {
        return code;
    }

    public FuelType setCode(String code) {
        this.code = code;
        return this;
    }

    public String getName() {
        return name;
    }

    public FuelType setName(String name) {
        this.name = name;
        return this;
    }
}