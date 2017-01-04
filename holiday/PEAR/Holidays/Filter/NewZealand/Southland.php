<?php
/**
 * This file contains only the Driver class for determining holidays in Southland
 * New Zealand.
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


/**
 * This Driver class calculates holidays in Southland.  It should be used in
 * conjunction with the New Zealand Official driver.
 *
 * @category   Date
 * @package    Date_Holidays
 * @subpackage Filter
 * @author     sasquatch58
 * @license    http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @link       http://pear.php.net/package/Date_Holidays
 * @version	   0.3.0
 */
require_once "Official.php";

class Date_Holidays_Filter_NewZealand_Southland extends Date_Holidays_Filter_NewZealand_Official
{
    /**
     * this driver's name
     *
     * @access   protected
     * @var      string
     */
    var $_driverName = 'Southland';

    /**
     * Constructor.
    */
    function __construct()
    {
      parent::__construct();
      $holidays = $this->getFilteredHolidays();
      $holidays = array_merge(
        $holidays,
        array(
          'anniversaryDaySd'
        )
      );
      $this->setFilteredHolidays($holidays);
    }
    
    /**
     * Constructor
     *
     * @access   protected
     */
    function Date_Holidays_Filter_NewZealand_Southland()
    {
      $this->__construct();
    }

}
?>
