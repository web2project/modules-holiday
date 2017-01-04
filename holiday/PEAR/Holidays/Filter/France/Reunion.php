<?php
/**
 * Filter for Reunion (France) holidays.
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
 * @author   Carsten Lucke <luckec@tool-garage.de>
 * @license  http://www.php.net/license/3_01.txt PHP License 3.0.1
 */

/**
 * Filter that only accepts holidays in Reunion (France).
 *
 * @category   Date
 * @package    Date_Holidays
 * @subpackage Filter
 * @author     Jacques Archimède <eureka@dbmail.com>
 * @license    http://www.php.net/license/3_01.txt PHP License 3.0.1
 */
require_once "Official.php";

class Date_Holidays_Filter_France_Reunion extends Date_Holidays_Filter_France_Official
{
    /**
     * Constructor.
     */
    function __construct()
    {
        parent::__construct();
				$holidays = $this->getFilteredHolidays();
				$holidays = array_merge($holidays, array(
					'shroveMonday',
					'shroveTuesday',
					'ashWednesday',
					'abolitionOfSlaveryDayReunion'
				));
				$this->setFilteredHolidays($holidays);
    }

    /**
     * Constructor.
     *
     * Only accepts official French holidays (that are valid for entire France).
     */
    function Date_Holidays_Filter_France_Reunion()
    {
        $this->__construct();
    }
}
?>
