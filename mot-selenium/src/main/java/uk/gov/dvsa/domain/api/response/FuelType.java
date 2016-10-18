package uk.gov.dvsa.domain.api.response;


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

    @Override
    public String toString() {
        return "FuelType{" +
            ", code='" + code + '\'' +
            ", name='" + name + '\'' +
            '}';
    }
}