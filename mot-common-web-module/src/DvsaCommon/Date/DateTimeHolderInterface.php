<?php
namespace DvsaCommon\Date;

interface DateTimeHolderInterface
{
    /**
     * Returns current datetime
     *
     * @return \DateTime
     */
    public function getCurrent($withMilliseconds = false);

    /**
     * @return \DateTime
     */
    public function getCurrentDate();
}
