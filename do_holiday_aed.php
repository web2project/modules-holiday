<?php /* $Id$ $URL$ */

if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

require_once 'PEAR/Holidays.php';
require_once "holiday_functions.class.php";

$target = w2PgetParam($_POST, 'target', "");

$q = new w2p_Database_Query();
if($target=="calendar") {
    $annual = w2PgetParam($_POST, 'holiday_annual', 0);
    $description = w2PgetParam($_POST, 'holiday_description', "");
    $newholidays = w2PgetParam($_POST, 'newholidays', "");
    $newholidays = $newholidays ? explode(",", $newholidays) : array();
    $holidays = array();
    foreach($newholidays as $newholiday) {
        list($id, $date, $name) = explode("-", $newholiday);
        if ($id > 0) { // this holidays is blacklisted
            $holiday = new CHoliday();
            $holiday->loadFull($AppUI, $id);
            $holiday->remove($AppUI, new w2p_Utilities_Date($date));
        }
        else {
            $holidays[] = $date; // put the date in whitelist
        }
    }
    $newholidaysranges = HolidayFunctions::makeHolidaysRecords($holidays, 0, HOLIDAY_TYPE_COMPANY_HOLIDAY, $annual, $description);
    HolidayFunctions::storeRecords( $newholidaysranges, 0, HOLIDAY_TYPE_COMPANY_HOLIDAY, $annual, $description );
    $newworkdays = w2PgetParam($_POST, 'newworkdays', "");
    $newworkdays = $newworkdays ? explode(",", $newworkdays) : array();
    $workdays = array();
    foreach($newworkdays as $newworkday) {
        list($id, $date, $name) = explode("-", $newworkday);
        if ($id > 0) { // this holidays is in whitelist
            $holiday = new CHoliday();
            $holiday->loadFull($AppUI, $id);
            $holiday->remove($AppUI, new w2p_Utilities_Date($date));
        }
        else {
            $workdays[] = $date; // put the date in blacklist
        }
    }
    $newworkdaysranges = HolidayFunctions::makeHolidaysRecords($workdays, 0, HOLIDAY_TYPE_COMPANY_WORKDAY);
    HolidayFunctions::storeRecords( $newworkdaysranges, 0, HOLIDAY_TYPE_COMPANY_WORKDAY );
    $AppUI->setMsg( "Public Holidays updated" );
}
if($target=="user") {
    $user_id = w2PgetParam($_POST, 'user_id', $AppUI->user_id);
    $description = w2PgetParam($_POST, 'holiday_description', "");
    $newholidays = w2PgetParam($_POST, 'newholidays', "");
    $newholidays = $newholidays ? explode(",", $newholidays) : array();
    $holidays = array();
    foreach($newholidays as $newholiday) {
        list($id, $date, $name) = explode("-", $newholiday);
        $holidays[] = $date; // put the date in whitelist
    }
    $newholidaysranges = HolidayFunctions::makeHolidaysRecords($holidays, $user_id, HOLIDAY_TYPE_USER_HOLIDAY, 0, $description);
    HolidayFunctions::storeRecords( $newholidaysranges, $user_id, HOLIDAY_TYPE_USER_HOLIDAY, 0, $description );
    $newworkdays = w2PgetParam($_POST, 'newworkdays', "");
    $newworkdays = $newworkdays ? explode(",", $newworkdays) : array();
    foreach($newworkdays as $newworkday) {
        list($id, $date, $name) = explode("-", $newworkday);
        if ($id > 0) { // this holidays is in whitelist of the user
            $holiday = new CHoliday();
            $holiday->loadFull($AppUI, $id);
            $holiday->remove($AppUI, new w2p_Utilities_Date($date));
        }
    }
    $AppUI->setMsg( "User Holidays updated" );
}
$AppUI->redirect('m=holiday');
