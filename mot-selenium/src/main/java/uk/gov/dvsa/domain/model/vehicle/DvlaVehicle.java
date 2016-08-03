package uk.gov.dvsa.domain.model.vehicle;

import org.apache.commons.lang3.RandomStringUtils;
import org.joda.time.DateTime;

public class DvlaVehicle {

    private String id;
    private String registration;
    private String registration_collapsed;
    private String registration_validation_character;
    private String vin;
    private String vin_collapsed;
    private String model_code;
    private String make_code;
    private String make_in_full;
    private String colour_1_code;
    private String colour_2_code;
    private String propulsion_code;
    private String designed_gross_weight;
    private String unladen_weight;
    private String engine_number;
    private String engine_capacity;
    private String seating_capacity;
    private String manufacture_date;
    private String first_registration_date;
    private String is_seriously_damaged;
    private String recent_v5_document_number;
    private String is_vehicle_new_at_first_registration;
    private String body_type_code;
    private String wheelplan_code;
    private String sva_emission_standard;
    private String ct_related_mark;
    private String vehicle_id;
    private String dvla_vehicle_id;
    private String eu_classification;
    private String mass_in_service_weight;
    private String created_by;
    private String created_on;
    private String last_updated_by;
    private String last_updated_on;
    private String version;

    public String getId() {
        return id;
    }

    public DvlaVehicle setId(String id) {
        this.id = id;
        return this;
    }

    public String getRegistration() {
        return registration;
    }

    public DvlaVehicle setRegistration(String registration) {
        this.registration = registration;
        return this;
    }

    public String getRegistration_collapsed() {
        return registration_collapsed;
    }

    public DvlaVehicle setRegistration_collapsed(String registration_collapsed) {
        this.registration_collapsed = registration_collapsed;
        return this;
    }

    public String getRegistration_validation_character() {
        return registration_validation_character;
    }

    public DvlaVehicle setRegistration_validation_character(String registration_validation_character) {
        this.registration_validation_character = registration_validation_character;
        return this;
    }

    public String getVin() {
        return vin;
    }

    public DvlaVehicle setVin(String vin) {
        this.vin = vin;
        return this;
    }

    public String getVin_collapsed() {
        return vin_collapsed;
    }

    public DvlaVehicle setVin_collapsed(String vin_collapsed) {
        this.vin_collapsed = vin_collapsed;
        return this;
    }

    public String getModel_code() {
        return model_code;
    }

    public DvlaVehicle setModel_code(String model_code) {
        this.model_code = model_code;
        return this;
    }

    public String getMake_code() {
        return make_code;
    }

    public DvlaVehicle setMake_code(String make_code) {
        this.make_code = make_code;
        return this;
    }

    public String getMake_in_full() {
        return make_in_full;
    }

    public DvlaVehicle setMake_in_full(String make_in_full) {
        this.make_in_full = make_in_full;
        return this;
    }

    public String getColour_1_code() {
        return colour_1_code;
    }

    public DvlaVehicle setColour_1_code(String colour_1_code) {
        this.colour_1_code = colour_1_code;
        return this;
    }

    public String getColour_2_code() {
        return colour_2_code;
    }

    public DvlaVehicle setColour_2_code(String colour_2_code) {
        this.colour_2_code = colour_2_code;
        return this;
    }

    public String getPropulsion_code() {
        return propulsion_code;
    }

    public DvlaVehicle setPropulsion_code(String propulsion_code) {
        this.propulsion_code = propulsion_code;
        return this;
    }

    public String getDesigned_gross_weight() {
        return designed_gross_weight;
    }

    public DvlaVehicle setDesigned_gross_weight(String designed_gross_weight) {
        this.designed_gross_weight = designed_gross_weight;
        return this;
    }

    public String getUnladen_weight() {
        return unladen_weight;
    }

    public DvlaVehicle setUnladen_weight(String unladen_weight) {
        this.unladen_weight = unladen_weight;
        return this;
    }

    public String getEngine_number() {
        return engine_number;
    }

    public DvlaVehicle setEngine_number(String engine_number) {
        this.engine_number = engine_number;
        return this;
    }

    public String getEngine_capacity() {
        return engine_capacity;
    }

    public DvlaVehicle setEngine_capacity(String engine_capacity) {
        this.engine_capacity = engine_capacity;
        return this;
    }

    public String getSeating_capacity() {
        return seating_capacity;
    }

    public DvlaVehicle setSeating_capacity(String seating_capacity) {
        this.seating_capacity = seating_capacity;
        return this;
    }

    public String getManufacture_date() {
        return manufacture_date;
    }

    public DvlaVehicle setManufacture_date(String manufacture_date) {
        this.manufacture_date = manufacture_date;
        return this;
    }

    public String getFirst_registration_date() {
        return first_registration_date;
    }

    public DvlaVehicle setFirst_registration_date(String first_registration_date) {
        this.first_registration_date = first_registration_date;
        return this;
    }

    public String getIs_seriously_damaged() {
        return is_seriously_damaged;
    }

    public DvlaVehicle setIs_seriously_damaged(String is_seriously_damaged) {
        this.is_seriously_damaged = is_seriously_damaged;
        return this;
    }

    public String getRecent_v5_document_number() {
        return recent_v5_document_number;
    }

    public DvlaVehicle setRecent_v5_document_number(String recent_v5_document_number) {
        this.recent_v5_document_number = recent_v5_document_number;
        return this;
    }

    public String getIs_vehicle_new_at_first_registration() {
        return is_vehicle_new_at_first_registration;
    }

    public DvlaVehicle setIs_vehicle_new_at_first_registration(String is_vehicle_new_at_first_registration) {
        this.is_vehicle_new_at_first_registration = is_vehicle_new_at_first_registration;
        return this;
    }

    public String getBody_type_code() {
        return body_type_code;
    }

    public DvlaVehicle setBody_type_code(String body_type_code) {
        this.body_type_code = body_type_code;
        return this;
    }

    public String getWheelplan_code() {
        return wheelplan_code;
    }

    public DvlaVehicle setWheelplan_code(String wheelplan_code) {
        this.wheelplan_code = wheelplan_code;
        return this;
    }

    public String getSva_emission_standard() {
        return sva_emission_standard;
    }

    public DvlaVehicle setSva_emission_standard(String sva_emission_standard) {
        this.sva_emission_standard = sva_emission_standard;
        return this;
    }

    public String getCt_related_mark() {
        return ct_related_mark;
    }

    public DvlaVehicle setCt_related_mark(String ct_related_mark) {
        this.ct_related_mark = ct_related_mark;
        return this;
    }

    public String getVehicle_id() {
        return vehicle_id;
    }

    public DvlaVehicle setVehicle_id(String vehicle_id) {
        this.vehicle_id = vehicle_id;
        return this;
    }

    public String getDvla_vehicle_id() {
        return dvla_vehicle_id;
    }

    public DvlaVehicle setDvla_vehicle_id(String dvla_vehicle_id) {
        this.dvla_vehicle_id = dvla_vehicle_id;
        return this;
    }

    public String getEu_classification() {
        return eu_classification;
    }

    public DvlaVehicle setEu_classification(String eu_classification) {
        this.eu_classification = eu_classification;
        return this;
    }

    public String getMass_in_service_weight() {
        return mass_in_service_weight;
    }

    public DvlaVehicle setMass_in_service_weight(String mass_in_service_weight) {
        this.mass_in_service_weight = mass_in_service_weight;
        return this;
    }

    public String getCreated_by() {
        return created_by;
    }

    public DvlaVehicle setCreated_by(String created_by) {
        this.created_by = created_by;
        return this;
    }

    public String getCreated_on() {
        return created_on;
    }

    public DvlaVehicle setCreated_on(String created_on) {
        this.created_on = created_on;
        return this;
    }

    public String getLast_updated_by() {
        return last_updated_by;
    }

    public DvlaVehicle setLast_updated_by(String last_updated_by) {
        this.last_updated_by = last_updated_by;
        return this;
    }

    public String getLast_updated_on() {
        return last_updated_on;
    }

    public DvlaVehicle setLast_updated_on(String last_updated_on) {
        this.last_updated_on = last_updated_on;
        return this;
    }

    public String getVersion() {
        return version;
    }

    public DvlaVehicle setVersion(String version) {
        this.version = version;
        return this;
    }
}
