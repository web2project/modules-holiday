<?php
/**
 * Filter for Official New Zealand holidays.
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
 * @author	 sasquatch58
 * @license  http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @link     http://pear.php.net/package/Date_Holidays
 * @version	 0.2.0
 *
 * Based on work by Carsten Lucke <luckec@tool-garage.de>
 *
 * Filter that only accepts official New Zealand holidays.
 */
class Date_Holidays_Filter_NewZealand_Official extends Date_Holidays_Filter_Whitelist
{
    /**
     * Constructor.
     */
    function __construct()
    {
        parent::__construct(array('newYearsDay',
                                  'newYearsDayHoliday',
                                  'dayAfterNewYearsDay',
                                  'waitangiDay',
                                  'goodFriday',
                                  'easterMonday',
                                  'anzacDay',
                                  'queensBirthday',
                                  'labourDay',
                                  'christmasDay',
                                  'boxingDay',
                                  'christmasDayHoliday',
                                  'boxingDayHoliday'));
    }

    /**
     * Constructor.
     *
     * Only accepts official New Zealand holidays (that are valid for all of New Zealand).
     */
    function Date_Holidays_Filter_NewZealand_Official()
    {
        $this->__construct();
    }
}
?>
