<?php
/**
 * This file contains only the Driver class for determining holidays in New Zealand.
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
 * @author   sasquatch58
 * @license  http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @link     http://pear.php.net/package/Date_Holidays
 */

require_once 'Christian.php'; 

/**
 * This is a Driver class that calculates holidays in New Zealand.  Individual regions
 * generally have other holidays as well so if one is available you should combine it with this one.
 * Based on work by Sam Wilson <sam@archives.org.au> for the Australia package
 *
 * @category   Date
 * @package    Date_Holidays
 * @subpackage Driver
 * @author     sasquatch58
 * @license    http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @link       http://pear.php.net/package/Date_Holidays
 * @version    0.4.0
 */

class Date_Holidays_Driver_NewZealand extends Date_Holidays_Driver
{
    /**
     * this driver's name
     *
     * @access   protected
     * @var      string
     */
    var $_driverName = 'NewZealand';

    /**
     * Constructor
     *
     * Use the Date_Holidays::factory() method to construct an object of a
     * certain driver
     *
     * @access   protected
     */
    function Date_Holidays_Driver_NewZealand()
    {
    }

    /**
     * Build the internal arrays that contain data about holidays.
     *
     * @access   protected
     * @return   boolean true on success, otherwise a PEAR_ErrorStack object
     * @throws   object PEAR_ErrorStack
     */
    function _buildHolidays()
    {
        parent::_buildHolidays();
        /**
         * Method that returns the date of the nearest Monday to the specified date
         * 
         * @return Monday date closest to specified date
         * 
         */

        /**
         * New Year's Day and Day after New Year's Day
         * always observed on a working day (1..5)
         * always show New Year's Day regardless of day of week
         */
        $newYearsDay = new Date($this->_year . '-01-01');
        $dayAfterNewYearsDay = new Date($this->_year . '-01-02');

        $this->_addHoliday(
                'newYearsDay', $newYearsDay, 'New Year\'s Day'
            );        

        if ($newYearsDay->getDayOfWeek() == 0) {
            $this->_addHoliday(
                'dayAfterNewYearsDay', $this->_year . '-01-02', 'Day after New Year\'s Day'
            );
            $this->_addHoliday(
                'newYearsDayHoliday', $this->_year . '-01-03', 'New Year\'s Holiday'
            );
        } elseif ($newYearsDay->getDayOfWeek() == 5) {
            $this->_addHoliday(
                'dayAfterNewYearsDay', $this->_year . '-01-04', 'New Year\'s Holiday'
            );
        }  elseif ($newYearsDay->getDayOfWeek() == 6) {
            $this->_addHoliday(
                'newYearsDayHoliday', $this->_year . '-01-03', 'New Year\'s Holiday'
            );
            $this->_addHoliday(
                'dayAfterNewYearsDay', $this->_year . '-01-04', 'New Year\'s Holiday'
            );
        } else {
            $this->_addHoliday(
                'dayAfterNewYearsDay', $dayAfterNewYearsDay, 'Day after New Year\'s Day'
            );
        }

        /**
         * Waitangi Day
         * always observed on 6 February
         */
        $waitangiDay = new Date($this->_year . '-02-06');
        $this->_addHoliday(
            'waitangiDay', $waitangiDay, 'Waitangi Day'
        );

        /**
         * Easter
         */
        $easter = Date_Holidays_Driver_Christian::calcEaster($this->_year);
        $goodFridayDate = new Date($easter);
        $goodFridayDate = $this->_addDays($easter, -2);
        $this->_addHoliday(
            'goodFriday', $goodFridayDate, 'Good Friday'
        );
        $this->_addHoliday(
            'easterMonday', $easter->getNextDay(), 'Easter Monday'
        );

        /**
         * Anzac Day
         * always observed on 25 April
         * differs from Australia in that there is no working day lost if Anzac Day falls on a weekend
         */
        $anzacDay = new Date($this->_year . '-04-25');
        $this->_addHoliday(
            'anzacDay', $anzacDay, 'Anzac Day'
        );
        
        /**
         * The Queen's Birthday.
         * always observed on 1st Monday in June
         */
        $queensBirthday = Date_Calc::NWeekdayOfMonth(1, 1, 6, $this->_year);
        $this->_addHoliday(
            'queensBirthday', $queensBirthday, "Queen\'s Birthday"
        );

        /**
         * Labour Day.
         * observed as 4th Monday in October
         */
        $labourDay = Date_Calc::NWeekdayOfMonth(4, 1, 10, $this->_year);
        $this->_addHoliday(
            'labourDay', $labourDay, "Labour Day"
        );
        
       /**
         * Christmas and Boxing days
         * always observed on a working day (1..5)
         * always show Christmas and Boxing days
         */
        $christmasDay = new Date($this->_year.'-12-25');
        $boxingDay = new Date($this->_year.'-12-26');
        $this->_addHoliday(
            'christmasDay', $christmasDay, 'Christmas Day'
        );
        $this->_addHoliday(
            'boxingDay', $boxingDay, 'Boxing Day'
        );

        if ($christmasDay->getDayOfWeek() == 0) {
            $this->_addHoliday(
                'christmasDayHoliday', $this->_year . '-12-27', 'Christmas Day Holiday'
            );
        } elseif ($christmasDay->getDayOfWeek() == 5) {
            $this->_addHoliday(
                'boxingDayHoliday', $this->_year . '-12-28', 'Boxing Day Holiday'
            );
        } elseif ($christmasDay->getDayOfWeek() == 6) {
            $this->_addHoliday(
                'christmasDayHoliday', $this->_year . '-12-27', 'Christmas Day Holiday'
            );
            $this->_addHoliday(
                'boxingDayHoliday', $this->_year . '-12-28', 'Boxing Day Holiday'
            );
        }

        /**
         * Regional anniversary calculations
         * http://www.dol.govt.nz/er/holidaysandleave/publicholidays/publicholidaydates/current.asp
         * ordered by date of observation
         * Note - where rule may be modified by proximity of Easter, this is NOT taken into account
         * 
         * Each of 8 regions can use common rule of nearest Monday
         * Southland 17 Jan
         * Wellington 22 Jan
         * Auckland 29 Jan
         * Nelson 1 Feb
         * Otago 23 Mar (some local variation)
         * Marlborough 1 Nov
         * Chatam Islands 30 Nov
         * Westland 1 Dec (some local variation)
         */

        $anniversaryDaySd = new Date($this->_year . '-01-17');
        $this->_addHoliday(
            'anniversaryDaySd', Date_Holidays_Driver_NewZealand::nearestMonday($anniversaryDaySd)  , "Southland Anniversary Day"
        );
        
        $anniversaryDayWn = new Date($this->_year . '-01-22');
        $this->_addHoliday(
            'anniversaryDayWn', Date_Holidays_Driver_NewZealand::nearestMonday($anniversaryDayWn) , "Wellington Anniversary Day"
        );
        
        $anniversaryDayAk = new Date($this->_year.'-01-29');
        $this->_addHoliday(
            'anniversaryDayAk', Date_Holidays_Driver_NewZealand::nearestMonday($anniversaryDayAk) , "Auckland Anniversary Day"
        );
        
        $anniversaryDayNn = new Date($this->_year.'-02-01');
        $this->_addHoliday(
            'anniversaryDayNn', Date_Holidays_Driver_NewZealand::nearestMonday($anniversaryDayNn) , "Nelson Anniversary Day"
        );
        
        $anniversaryDayOo = new Date($this->_year.'-03-23');
        $this->_addHoliday(
            'anniversaryDayOo', Date_Holidays_Driver_NewZealand::nearestMonday($anniversaryDayOo) , "Otago Anniversary Day"
        );
        
        $anniversaryDayMb = new Date($this->_year.'-11-01');
        $this->_addHoliday(
            'anniversaryDayMb', Date_Holidays_Driver_NewZealand::nearestMonday($anniversaryDayMb) , "Marlborough Anniversary Day"
        );
        
        $anniversaryDayCi = new Date($this->_year.'-11-30');
        $this->_addHoliday(
            'anniversaryDayCi', Date_Holidays_Driver_NewZealand::nearestMonday($anniversaryDayCi) , "Chatam Islands Anniversary Day"
        );
        
        $anniversaryDayWd = new Date($this->_year.'-12-01');
        $this->_addHoliday(
            'anniversaryDayWd', Date_Holidays_Driver_NewZealand::nearestMonday($anniversaryDayWd) , "Westland Anniversary Day"
        );

        /**
         * Taranaki Anniversary.
         * 2nd Monday in March.
         */
        $anniversaryDayTk = Date_Calc::nWeekdayOfMonth(2, 1, 3, $this->_year);
        $this->_addHoliday(
            'anniversaryDayTk', $anniversaryDayTk, "Taranaki Anniversary Day"
        );
                
        /**
         * South Canterbury Anniversary.
         * 4th Monday in September.
         */
        $anniversaryDaySc = Date_Calc::nWeekdayOfMonth(4, 1, 9, $this->_year);
        $this->_addHoliday(
            'anniversaryDaySc', $anniversaryDaySc, "South Canterbury Anniversary Day"
        );

        /**
         * Hawkes' Bay Anniversary.
         * Friday before Labour Day (or 3rd Friday in October).
         *
        */
        $anniversaryDayHb = Date_Calc::nWeekdayOfMonth(3, 5, 10, $this->_year);
        $this->_addHoliday(
            'anniversaryDayHb', $anniversaryDayHb, "Hawkes\' Bay Anniversary"
        );

        /**
         * Canterbury Anniversary or Show Day for North and Central Canterbury.
         * 2nd Friday after 1st Tuesday in month of November.
         * find 1st Tuesday then add 10 days
        */
        $anniversaryDayNc = $this->_calcNthWeekDayInMonth(1, 2, 11);
        $anniversaryDayNc = $this->_addDays($anniversaryDayNc, 10);
        $this->_addHoliday(
            'anniversaryDayNc', $anniversaryDayNc, "Canterbury Anniversary Day"
        );

        /**
         * Check for errors, and return.
         */
        if (Date_Holidays::errorsOccurred()) {
            return Date_Holidays::getErrorStack();
        }
        return true;
    }

    /**
     * Method that returns an array containing the ISO3166 codes ('nz' and 'nzl')
     * that identify this driver.
     *
     * @static
     * @access public
     * @return array possible ISO3166 codes
     */
    function getISO3166Codes()
    {
        return array('nz', 'nzl');
    }
      /**
       * Calculate nearest monday to any given date
       * @param integer $regionDate date
       *
       * @access     private
       *
       */
    public function nearestMonday($regionDate) {
        switch ($regionDate->getDayOfWeek()) {
            case 0:
                $regionMonday = $this->_addDays($regionDate, 1);
                break;
            case 1:
                $regionMonday = $regionDate;
                break;
            case 2:
                $regionMonday = $this->_addDays($regionDate, -1);
                break;
            case 3:
                $regionMonday = $this->_addDays($regionDate, -2);
                break;
            case 4:
                $regionMonday = $this->_addDays($regionDate, -3);
                break;
            case 5:
                $regionMonday = $this->_addDays($regionDate, 3);
                break;
            case 6:
                $regionMonday = $this->_addDays($regionDate, 2);
                break;
            default:
                $regionMonday = $regionDate;
                break;
        }
        return $regionMonday;
    }    
}
?>
