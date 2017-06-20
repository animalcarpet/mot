package uk.gov.dvsa.domain.api.response;


public class WeightSource {

    private String code;

    private String name;

    public String getCode() {
        return code;
    }

    public WeightSource setCode(String code) {
        this.code = code;
        return this;
    }

    public String getName() {
        return name;
    }

    public WeightSource setName(String name) {
        this.name = name;
        return this;
    }

    @Override
    public String toString() {
        return "WeightSource{" +
                ", code='" + code + '\'' +
                ", name='" + name + '\'' +
                '}';
    }
}