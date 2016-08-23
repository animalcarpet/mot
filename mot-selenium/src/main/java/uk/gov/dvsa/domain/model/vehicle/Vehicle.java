package uk.gov.dvsa.domain.model.vehicle;

import org.apache.commons.lang3.RandomStringUtils;
import org.joda.time.DateTime;

public class Vehicle {

    private String amendedOn;
    private String bodyType;
    private String colour;
    private String colourSecondary;
    private String countryOfRegistration;
    private String cylinderCapacity;
    private String emptyVinReason;
    private String emptyVrmReason;
    private String firstRegistrationDate;
    private String firstUsedDate;
    private String fuelType;
    private String id;
    private String isNewAtFirstReg;
    private String make;
    private String makeOther;
    private String manufactureDate;
    private String model;
    private String modelOther;
    private String registrationDvsa;
    private String registrationDvla;
    private String transmissionType;
    private String vehicleClass;
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

    public String getMake() {
        return make;
    }

    public Vehicle setMake(String make) {
        this.make = make;
        return this;
    }

    public String getMakeModel() {
        return make + " " + model;
    }

    public String getMakeOther() {
        return makeOther;
    }

    public Vehicle setMakeOther(String makeOther) {
        this.makeOther = makeOther;
        return this;
    }

    public String getManufactureDate() {
        return manufactureDate;
    }

    public Vehicle setManufactureDate(String manufactureDate) {
        this.manufactureDate = manufactureDate;
        return this;
    }

    public String getModel() {
        return model;
    }

    public Vehicle setModel(String model) {
        this.model = model;
        return this;
    }

    public String getModelOther() {
        return modelOther;
    }

    public Vehicle setModelOther(String modelOther) {
        this.modelOther = modelOther;
        return this;
    }

    public String getTransmissionType() {
        return transmissionType;
    }

    public Vehicle setTransmissionType(String transmissionType) {
        this.transmissionType = transmissionType;
        return this;
    }

    public String getVehicleClass() {
        return vehicleClass;
    }

    public Vehicle setVehicleClass(String vehicleClass) {
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

    public String getCountryOfRegistration() {
        return countryOfRegistration;
    }

    public Vehicle setCountryOfRegistration(String countryOfRegistration) {
        this.countryOfRegistration = countryOfRegistration;
        return this;
    }

    public static Vehicle getVehicle(String colour,
                                     String countryOfRegistration,
                                     String cylinderCapacity,
                                     String dvsaRegistration,
                                     String dvlaRegistration,
                                     String firstUsedDate,
                                     String fuelType,
                                     String make,
                                     String makeOder,
                                     String model,
                                     String modelOther,
                                     String secondaryColour,
                                     String transmissionType,
                                     String vin,
                                     String vehicleClass,
                                     String weight
    ) {

        Vehicle vehicle = new Vehicle();
        vehicle.setColour(colour)
                .setCountryOfRegistration(countryOfRegistration)
                .setCylinderCapacity(cylinderCapacity)
                .setDvsaRegistration(dvsaRegistration)
                .setDvlaRegistration(dvlaRegistration)
                .setFirstUsedDate(firstUsedDate)
                .setFuelType(fuelType)
                .setMake(make)
                .setMakeOther(makeOder)
                .setModel(model)
                .setModelOther(modelOther)
                .setColourSecondary(secondaryColour)
                .setTransmissionType(transmissionType)
                .setVin(vin)
                .setVehicleClass(vehicleClass)
                .setWeight(weight);

        return vehicle;
    }

    public static Vehicle generateValidDetails() {

        String randomRegistrationNumber = RandomStringUtils.randomAlphabetic(7);
        return getVehicle(
                Colour.Blue.getName(),
                CountryOfRegistration.Great_Britain.getCountry(),
                "1700",
                randomRegistrationNumber,
                randomRegistrationNumber,
                new DateTime().minusYears(1).toString(),
                FuelTypes.Diesel.getName(),
                Make.BMW.getName(),
                "",
                Model.BMW_ALPINA.getName(),
                "",
                Colour.Black.getName(),
                TransmissionType.Manual.getName(),
                RandomStringUtils.randomAlphabetic(17),
                VehicleClass.four.getId(),
                "888"
        );
    }

    public static Vehicle generateEmptyAndInvalidDetails() {
        return getVehicle(
                " ", " ", " ",
                " ", " ", " ",
                "Fake name", "Fake make", "", "Fake model",
                "",
                Colour.Black.getName(),
                "FakeFuel",
                " ",
                VehicleClass.four.getId(),
                "888"
        );
    }

    @Override
    public String toString() {
        return "Vehicle{" +
                ", registrationNumber='" + registrationDvsa + '\'' +
                ", registration='" + registrationDvla + '\'' +
                ", vehicleId='" + id + '\'' +
                ", vin='" + vin + '\'' +
                ", make='" + make + '\'' +
                ", makeOther='" + makeOther + '\'' +
                ", model='" + model + '\'' +
                ", modelOther='" + modelOther + '\'' +
                ", makeModel='" + getMakeModel() + '\'' +
                ", colour='" + colour + '\'' +
                ", secondaryColour='" + colourSecondary + '\'' +
                ", dateOfFirstUse='" + firstUsedDate + '\'' +
                ", fuelType='" + fuelType + '\'' +
                ", vehicleClass='" + vehicleClass + '\'' +
                ", countryOfRegistration='" + countryOfRegistration + '\'' +
                ", cylinderCapacity='" + cylinderCapacity + '\'' +
                ", transmissionType='" + transmissionType + '\'' +
                ", bodyType='" + bodyType + '\'' +
                ", weight='" + weight + '\'' +
                '}';
    }
}
