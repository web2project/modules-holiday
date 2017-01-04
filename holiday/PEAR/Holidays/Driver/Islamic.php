<?php

/**
 * class that calculates Islamic holidays
 *
 * @category   Date
 * @package    Date_Holidays
 * @subpackage Driver
 * @author     Jacques Archimède <eureka@dbmail.com>
 */
class Date_Holidays_Driver_Islamic extends Date_Holidays_Driver
{
    /**
     * this driver's name
     *
     * @access   protected
     * @var      string
     */
    var $_driverName = 'Islamic';

    /**
     * Constructor
     *
     * Use the Date_Holidays::factory() method to construct an object of a
     * certain driver
     *
     * @access   protected
     */
    function Date_Holidays_Driver_Islamic()
    {
    }

    /**
     * Build the internal arrays that contain data about the calculated holidays
     *
     * @access   protected
     * @return   boolean true on success, otherwise a PEAR_ErrorStack object
     * @throws   object PEAR_ErrorStack
     */
    function _buildHolidays()
    {
        $islamic = self::fromGregorian($this->_year , 1, 1);
        $year = $islamic->getYear();

        /**
         * Islamic New Year
         */
				$this->_addHoliday('islamicNewYear', self::toGregorian($year, 1, 1, $this->_year), 'Islamic New Year');

        /**
         * Day of 'Ashura
         */
        $this->_addHoliday('ashura', self::toGregorian($year , 1, 10, $this->_year), 'Day of \'Ashura');

        /**
         * Rabi'I
         */
        $this->_addHoliday('rabiI', self::toGregorian($year , 3, 1, $this->_year), 'Rabi\'I');

        /**
         * Mawlid an-Nabi
         */
        $this->_addHoliday('mawlidAnNabi', self::toGregorian($year , 3, 12, $this->_year), 'Mawlid an-Nabi');

        /**
         * Lailat al-Miraj
         */
        $this->_addHoliday('lailatAlMiraj', self::toGregorian($year , 7, 27, $this->_year), 'Lailat al-Miraj');
 
        /**
         * Lailat al-Baraa
         */
        $this->_addHoliday('lailatAlBaraa', self::toGregorian($year , 8, 15, $this->_year), 'Lailat al-Baraa');

        /**
         * First day of Ramadan
         */
        $this->_addHoliday('firstDayofRamadan', self::toGregorian($year , 9, 1, $this->_year), 'First day of Ramadan');
 
        /**
         * Lailat al-Qadr
         */
        $this->_addHoliday('lailatAlQadr', self::toGregorian($year , 9, 27, $this->_year), 'Lailat al-Qadr');

        /**
         * Last day of Ramadan
         */
        $this->_addHoliday('lastDayofRamadan', self::toGregorian($year , 9, 30, $this->_year), 'Last day of Ramadan');

        /**
         * Aid al-Fitr
         */
        $this->_addHoliday('aidalFitr', self::toGregorian($year , 10, 1, $this->_year), 'Aid al-Fitr');

        /**
         * Aid al-Adha
         */
        $this->_addHoliday('aidAlAdha', self::toGregorian($year , 12, 10, $this->_year), 'Aid al-Adha');
                           
        if (Date_Holidays::errorsOccurred()) {
            return Date_Holidays::getErrorStack();
        }
        return true;
    }

    function intPart($floatNum)
    {
        if ($floatNum< -0.0000001){
            return ceil($floatNum-0.0000001);
        }
        return floor($floatNum+0.0000001);
    }

		function toGregorian($year, $month, $day, $gyear) {
        $y = $year;
        $m = $month;
        $d = $day;
        // http://www.oriold.uzh.ch/static/hegira.html
        $jd = self::intPart((11 * $y + 3) / 30) + 354 * $y + 30 * $m - self::intPart(($m - 1) / 2)+ $d + 1948440 - 385;
        if ($jd > 2299160 ) {
            $l = $jd + 68569;
            $n = self::intPart((4 * $l) / 146097);
            $l = $l - self::intPart((146097 * $n + 3) / 4);
            $i = self::intPart((4000 * ($l + 1)) / 1461001);
            $l = $l-self::intPart((1461 * $i) / 4) + 31;
            $j = self::intPart((80 * $l) / 2447);
            $d = $l - self::intPart((2447 * $j) / 80);
            $l = self::intPart($j / 11);
            $m = $j + 2 - 12 * $l;
            $y = 100 * ($n - 49) + $i + $l;
        }  
        else {
            $j = $jd + 1402;
            $k = self::intPart(($j - 1) / 1461);
            $l = $j - 1461 * $k;
            $n = self::intPart(($l - 1) / 365) - self::intPart($l / 1461);
            $i = $l - 365 * $n + 30;
            $j = self::intPart((80 * $i) / 2447);
            $d = $i-self::intPart((2447 * $j) / 80);
            $i = self::intPart($j / 11);
            $m = $j + 2 - 12 * $i;
            $y = 4 * k + $n + $i - 4716;
        }
        if ($y < $gyear) {
						return self::toGregorian($year + 1, $month, $day - 1, $gyear);
        }
				return new Date($y."-".sprintf("%02d-%02d", $m, $d));
    }

    function fromGregorian($y, $m, $d) {
        // http://www.oriold.uzh.ch/static/hegira.html
        if (($y > 1582) || (($y == 1582) && ($m > 10)) || (($y == 1582) && ($m == 10) && ($d > 14))) {
            $jd = self::intPart((1461 * ($y + 4800 + self::intPart(($m - 14) / 12))) / 4) +
                  self::intPart((367 * ($m - 2 - 12 * (self::intPart(($m - 14) / 12)))) / 12) -
                  self::intPart( (3 * (self::intPart(($y + 4900 + self::intPart(($m - 14) / 12)) / 100))) / 4) + $d - 32075;
        }
        else {
            $jd = 367 * $y - self::intPart((7 * ($y + 5001 + self::intPart(($m - 9) / 7))) / 4) + self::intPart((275 * $m) / 9) + $d + 1729777;
        }
        $l = $jd - 1948440 + 10632;
        $n = self::intPart(($l - 1) / 10631);
        $l = $l - 10631 * $n + 354;
        $j = (self::intPart((10985 - $l) / 5316)) * (self::intPart((50 * $l) / 17719)) + 
                (self::intPart($l / 5670)) * (self::intPart((43 * $l) / 15238));
        $l = $l - (self::intPart((30 - $j) / 15)) * (self::intPart((17719 * $j) / 50)) - 
                (self::intPart($j / 16)) * (self::intPart((15238 * $j) / 43)) + 29;
        $m = self::intPart((24 * $l) / 709);
        $d = $l - self::intPart((709 * $m) / 24);
        $y = 30 * $n + $j - 30;
        return new Date($y."-".sprintf("%02d-%02d", $m, $d));
    }
}
?>
