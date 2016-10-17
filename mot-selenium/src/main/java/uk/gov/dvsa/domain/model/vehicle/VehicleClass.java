package uk.gov.dvsa.domain.model.vehicle;

public class VehicleClass {
    public static final VehicleClass one = new VehicleClass("1", "1");
    public static final VehicleClass two = new VehicleClass("2", "2");
    public static final VehicleClass three = new VehicleClass("3", "3");
    public static final VehicleClass four = new VehicleClass("4", "4");
    public static final VehicleClass five = new VehicleClass("5", "5");
    public static final VehicleClass seven = new VehicleClass("7", "7");

    public static final VehicleClass[] values;

    static {
        values = new VehicleClass[]{one, two, three, four, five, seven};
    }

    private String code;
    private String name;

    /**
     * This parameterless constructor exists for the purpose of JSON deserialization.
     * We use ObjectMapper for handling JSON and it requires a default constructor.
     */
    private VehicleClass() {

    }

    private VehicleClass(String code, String name) {
        this.code = code;
        this.name = name;
    }

    public String getCode() {
        return this.code;
    }

    public String getName() {
        return this.name;
    }

    public static VehicleClass getRandomClass() {
        return values[(int) (Math.random() * values.length)];
    }
}
