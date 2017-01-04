@@ -1,192 +0,0 @@
<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * Driver for holidays in South Africa
 *
 * PHP Version 4
 *
 * Copyright (c) 1997-2008 The PHP Group
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available at through the world-wide-web at
 * http://www.php.net/license/3_01.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category Date
 * @package  Date_Holidays
 * @author   Stephen Metcalfe <raithlin@gmail.com>
 * @license  http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @version  CVS: $Id: SouthAfrica.php,v 1.0 2015/12/21 13:30:00 smetcalfe Exp $
 * @link     http://pear.php.net/package/Date_Holidays
 */
require_once 'Christian.php';
require_once 'USA.php';

/**
 * Driver class that calculates South African holidays
 *
 * @category   Date
 * @package    Date_Holidays
 * @subpackage Driver
 * @author     Stephen Metcalfe <raithlin@gmail.com>
 * @license    http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @version    CVS: $Id: SouthAfrica.php,v 1.0 2015/12/21 13:30:00 smetcalfe Exp $
 * @link       http://pear.php.net/package/Date_Holidays
 */
class Date_Holidays_Driver_SouthAfrica extends Date_Holidays_Driver
{
    /**
     * this driver's name
     *
     * @access   protected
     * @var      string
     */
    var $_driverName = 'SouthAfrica';

    /**
     * Constructor
     *
     * Use the Date_Holidays::factory() method to construct an object of a
     * certain driver
     *
     * @access protected
     */
    function Date_Holidays_Driver_SouthAfrica()
    {
    }

    /**
     * Build the internal arrays that contain data about the calculated holidays
     *
     * @access protected
     * @return boolean true on success, otherwise a PEAR_ErrorStack object
     * @throws object PEAR_ErrorStack
     */
    function _buildHolidays()
    {
        /**
         * New Year's Day
         */
        $newYearsDayDate = $this->_calcNearestWorkDay('01', '01');
        $this->_addHoliday('newYearsDay', $newYearsDayDate, 'New Year\'s Day');

        /**
         * Human Rights Day
         */
        $humanRightsDayDate = $this->_calcNearestWorkDay('03', '21');
        $this->_addHoliday('humanRightsDay', $humanRightsDayDate, 'Human Rights Day');

        /**
         * Easter Sunday
         */
        $easterDate = Date_Holidays_Driver_Christian::calcEaster($this->_year);

        /**
         * Good Friday
         */
        $goodFridayDate = new Date($easterDate);
        $goodFridayDate = $this->_addDays($easterDate, -2);
        $this->_addHoliday('goodFriday', $goodFridayDate, 'Good Friday');

        /**
         * Family Day
         */
        $this->_addHoliday('easterMonday', $easterDate->getNextDay(), 'Family Day');

        /**
         * Freedom Day
         */
        $freedomDayDate = $this->_calcNearestWorkDay('04', '27');
        $this->_addHoliday('freedomDay', $freedomDayDate, 'Freedom Day');

        /**
         * Worker's Day
         */
        $workersDayDate = $this->_calcNearestWorkDay('05', '01');
        $this->_addHoliday('workersDay', $workersDayDate, 'Worker\'s Day');

        /**
         * Youth Day
         */
        $youthDayDate = $this->_calcNearestWorkDay('06', '16');
        $this->_addHoliday('youthDay', $youthDayDate, 'Youth Day');

        /**
         * National Women's Day
         */
        $womensDayDate = $this->_calcNearestWorkDay('08', '09');
        $this->_addHoliday('womensDay', $womensDayDate, 'Women\'s Day');

        /**
         * Heritage Day
         */
        $heritageDayDate = $this->_calcNearestWorkDay('09', '24');
        $this->_addHoliday('heritageDay', $heritageDayDate, 'Heritage Day');

        /**
         * Day of Reconciliation
         */
        $dayOfReconciliation = $this->_calcNearestWorkDay('12', '16');
        $this->_addHoliday('dayOfReconciliation', $dayOfReconciliation, 'Day of Reconciliation');

        /**
         * Christmas Day
         */
        $christmasDay = $this->_calcNearestWorkDay('12', '25');
        $this->_addHoliday('christmasDay', $christmasDay, 'Christmas Day');

        /**
         * Day of Goodwill
         */
        $dayOfGoodwill = $this->_calcNearestWorkDay('12', '26');
        $this->_addHoliday('dayOfGoodwill', $dayOfGoodwill, 'Day of Goodwill');

        if (Date_Holidays::errorsOccurred()) {
            return Date_Holidays::getErrorStack();
        }

        return true;
    }

    /**
     * Calculate nearest workday for a certain day
     *
     * @param int $month month
     * @param int $day   day
     *
     * @access   private
     * @return   object Date date
     */
    function _calcNearestWorkDay($month, $day)
    {
        $month = sprintf("%02d", $month);
        $day   = sprintf("%02d", $day);
        $date  = new Date($this->_year . '-' . $month . '-' . $day);

        // When a public holiday falls on a
        // Sunday, the next day is also a holiday.
        if ($date->getDayOfWeek() == 0 ) {
            // bump it up one
            $date = $date->getNextDay();
        }

        return $date;
    }

    /**
     * Method that returns an array containing the ISO3166 codes that may possibly
     * identify a driver.
     *
     * @static
     * @access public
     * @return array possible ISO3166 codes
     */
    function getISO3166Codes()
    {
        return array('za', 'zaf');
    }
}
?>