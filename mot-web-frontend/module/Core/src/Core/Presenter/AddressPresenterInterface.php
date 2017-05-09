<?php

namespace Core\Presenter;

/**
 * Interface used when displaying address details.
 */
interface AddressPresenterInterface
{
    /**
     * @return string
     */
    public function displayAddressLine1();

    /**
     * @return string
     */
    public function displayAddressLine2();

    /**
     * @return string
     */
    public function displayAddressLine3();

    /**
     * @return string
     */
    public function displayAddressLine4();

    /**
     * @return string
     */
    public function displayPostcode();

    /**
     * @return string
     */
    public function displayTown();
}
