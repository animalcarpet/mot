package uk.gov.dvsa.domain.api.response;


public class Colour {

    private String code;

    private String name;

    public String getCode() {
        return code;
    }

    public Colour setCode(String code) {
        this.code = code;
        return this;
    }

    public String getName() {
        return name;
    }

    public Colour setName(String name) {
        this.name = name;
        return this;
    }

    @Override
    public String toString() {
        return "Colour{" +
            ", code='" + code + '\'' +
            ", name='" + name + '\'' +
            '}';
    }
}
