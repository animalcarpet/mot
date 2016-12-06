package uk.gov.dvsa.domain.model.vehicle;

public interface IVehicleDataRandomizer {

    String nextVin();

    String nextReg();

    String nextVin(int length);

    String nextReg(int length);
}

