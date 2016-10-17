package uk.gov.dvsa.domain.api.response;

import uk.gov.dvsa.domain.model.vehicle.FuelType;
import uk.gov.dvsa.domain.model.vehicle.VehicleClass;

public class Vehicle {

    private String amendedOn;
    private String bodyType;
    private String colour;
    private String colourSecondary;
    private String countryOfRegistrationId;
    private String cylinderCapacity;
    private String emptyVinReason;
    private String emptyVrmReason;
    private String firstRegistrationDate;
    private String firstUsedDate;
    private String fuelType;
    private FuelType fuelTypeCode;
    private String id;
    private String isNewAtFirstReg;
    private Make make;
    private String manufactureDate;
    private Model model;
    private String registrationDvsa;
    private String registrationDvla;
    private String transmissionType;
    private VehicleClass vehicleClass;
    private String version;
    private String vin;
    private String weight;

    public String getVersion() {
        return version;
    }

    public Vehicle setVersion(String version) {
        this.version = version;
        return this;
    }

    public String getAmendedOn() {
        return amendedOn;
    }

    public Vehicle setAmendedOn(String amendedOn) {
        this.amendedOn = amendedOn;
        return this;
    }

    public String getBodyType() {
        return bodyType;
    }

    public Vehicle setBodyType(String bodyType) {
        this.bodyType = bodyType;
        return this;
    }

    public String getColour() {
        return colour;
    }

    public Vehicle setColour(String colour) {
        this.colour = colour;
        return this;
    }

    public String getColourSecondary() {
        return colourSecondary;
    }

    public Vehicle setColourSecondary(String colourSecondary) {
        this.colourSecondary = colourSecondary;
        return this;
    }

    public String getColorsWithSeparator(String separator) {
        return colour + separator + colourSecondary;
    }

    public String getCylinderCapacity() {
        return cylinderCapacity;
    }

    public Vehicle setCylinderCapacity(String cylinderCapacity) {
        this.cylinderCapacity = cylinderCapacity;
        return this;
    }

    public String getDvsaRegistration() {
        return registrationDvsa;
    }

    public Vehicle setDvsaRegistration(String registrationDvsa) {
        this.registrationDvsa = registrationDvsa;
        return this;
    }

    public Vehicle setRegistration(String registration) {
        return setDvsaRegistration(registration);
    }

    public String getDvlaRegistration() {
        return registrationDvla;
    }

    public Vehicle setDvlaRegistration(String registrationDvla) {
        this.registrationDvla = registrationDvla;
        return this;
    }

    public String getEmptyVinReason() {
        return emptyVinReason;
    }

    public Vehicle setEmptyVinReason(String emptyVinReason) {
        this.emptyVinReason = emptyVinReason;
        return this;
    }

    public String getEmptyVrmReason() {
        return emptyVrmReason;
    }

    public Vehicle setEmptyVrmReason(String emptyVrmReason) {
        this.emptyVrmReason = emptyVrmReason;
        return this;
    }

    public String getFirstRegistrationDate() {
        return firstRegistrationDate;
    }

    public Vehicle setFirstRegistrationDate(String firstRegistrationDate) {
        this.firstRegistrationDate = firstRegistrationDate;
        return this;
    }

    public String getFirstUsedDate() {
        return firstUsedDate;
    }

    public Vehicle setFirstUsedDate(String firstUsedDate) {
        this.firstUsedDate = firstUsedDate;
        return this;
    }

    public String getFuelType() {
        return fuelType;
    }

    public Vehicle setFuelType(String fuelType) {
        this.fuelType = fuelType;
        return this;
    }

    public FuelType getFuelTypeCode() {
        return fuelTypeCode;
    }

    public Vehicle setFuelTypeCode(FuelType fuelTypeCode) {
        this.fuelTypeCode = fuelTypeCode;
        return this;
    }

    public String getId() {
        return id;
    }

    public Vehicle setId(String id) {
        this.id = id;
        return this;
    }

    public String getIsNewAtFirstReg() {
        return isNewAtFirstReg;
    }

    public Vehicle setIsNewAtFirstReg(String isNewAtFirstReg) {
        this.isNewAtFirstReg = isNewAtFirstReg;
        return this;
    }

    public Make getMake() {
        return make;
    }

    public Vehicle setMake(Make make) {
        this.make = make;
        return this;
    }

    public String getMakeModelWithSeparator(String separator) {
        return make.getName() + separator + model.getName();
    }

    public String getManufactureDate() {
        return manufactureDate;
    }

    public Vehicle setManufactureDate(String manufactureDate) {
        this.manufactureDate = manufactureDate;
        return this;
    }

    public Model getModel() {
        return model;
    }

    public Vehicle setModel(Model model) {
        this.model = model;
        return this;
    }

    public String getTransmissionType() {
        return transmissionType;
    }

    public Vehicle setTransmissionType(String transmissionType) {
        this.transmissionType = transmissionType;
        return this;
    }

    public VehicleClass getVehicleClass() {
        return vehicleClass;
    }

    public Vehicle setVehicleClass(VehicleClass vehicleClass) {
        this.vehicleClass = vehicleClass;
        return this;
    }

    public String getVin() {
        return vin;
    }

    public Vehicle setVin(String vin) {
        this.vin = vin;
        return this;
    }

    public String getWeight() {
        return weight;
    }

    public Vehicle setWeight(String weight) {
        this.weight = weight;
        return this;
    }

    public String getCountryOfRegistrationId() {
        return countryOfRegistrationId;
    }

    public Vehicle setCountryOfRegistrationId(String countryOfRegistrationId) {
        this.countryOfRegistrationId = countryOfRegistrationId;
        return this;
    }

    public static Vehicle createVehicle(String colour,
                                     String countryOfRegistrationId,
                                     String cylinderCapacity,
                                     String dvsaRegistration,
                                     String dvlaRegistration,
                                     String firstUsedDate,
                                     String fuelType,
                                     String make,
                                     String model,
                                     String secondaryColour,
                                     String transmissionType,
                                     String vin,
                                     VehicleClass vehicleClass,
                                     String weight
    ) {

        Vehicle vehicle = new Vehicle();

        vehicle.setColour(colour)
                .setCountryOfRegistrationId(countryOfRegistrationId)
                .setCylinderCapacity(cylinderCapacity)
                .setDvsaRegistration(dvsaRegistration)
                .setDvlaRegistration(dvlaRegistration)
                .setFirstUsedDate(firstUsedDate)
                .setFuelType(fuelType)
                .setMake(new Make().setName(make))
                .setModel(new Model().setName(model))
                .setColourSecondary(secondaryColour)
                .setTransmissionType(transmissionType)
                .setVin(vin)
                .setVehicleClass(vehicleClass)
                .setWeight(weight);

        return vehicle;
    }

    @Override
    public String toString() {
        return "Vehicle{" +
                ", registrationNumber='" + registrationDvsa + '\'' +
                ", registration='" + registrationDvla + '\'' +
                ", vehicleId='" + id + '\'' +
                ", vin='" + vin + '\'' +
                ", make='" + make + '\'' +
                ", model='" + model + '\'' +
                ", makeModel='" + getMakeModelWithSeparator(" ") + '\'' +
                ", colour='" + colour + '\'' +
                ", secondaryColour='" + colourSecondary + '\'' +
                ", dateOfFirstUse='" + firstUsedDate + '\'' +
                ", fuelType='" + fuelType + '\'' +
                ", vehicleClass='" + vehicleClass + '\'' +
                ", countryOfRegistrationId='" + countryOfRegistrationId + '\'' +
                ", cylinderCapacity='" + cylinderCapacity + '\'' +
                ", transmissionType='" + transmissionType + '\'' +
                ", bodyType='" + bodyType + '\'' +
                ", weight='" + weight + '\'' +
                '}';
    }
}
