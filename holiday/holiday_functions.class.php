<?php /* $Id$ $URL$ */

if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

define('HOLIDAY_TYPE_COMPANY_WORKDAY', 0);
define('HOLIDAY_TYPE_COMPANY_HOLIDAY', 1);
define('HOLIDAY_TYPE_USER_HOLIDAY', 2);
define('HOLIDAY_TYPE_CALENDAR_HOLIDAY', 3);

require_once 'PEAR/Holidays.php';

if (is_object($AppUI)) {
    require_once $AppUI->getLibraryClass("PEAR/Date");
}

class HolidayFunctions
{

    private static $holiday_manual = null;
    private static $holiday_auto = null;
    private static $holiday_driver = null;
    private static $holiday_filter = null;
    private static $holiday_filter_instance = null;
    private static $holiday_driver_instance = null;

    private static function loadHolidaysSettings()
    {
        global $AppUI;
        if(is_null(self::$holiday_manual)) {
            // Query database for settings
            $q = new w2p_Database_Query();
            $q->addTable("holiday_settings");
            $q->addQuery("holiday_manual, holiday_auto, holiday_driver, holiday_filter");
            $settings = $q->loadHash();
            self::$holiday_manual = $settings['holiday_manual']; 
            self::$holiday_auto = $settings['holiday_auto'];
            self::$holiday_driver = $settings['holiday_driver'];
            self::$holiday_filter = $settings['holiday_filter'];
        }
        if(self::$holiday_auto && self::$holiday_filter >= 0 && is_null(self::$holiday_filter_instance)) {
            $filters_alloc = Date_Holidays::getInstalledFilters();
            require_once dirname(__FILE__)."/PEAR/Holidays/Filter/".str_replace("_", "/", $filters_alloc[self::$holiday_filter]['title']).".php";
            $filterclass = "Date_Holidays_Filter_".$filters_alloc[self::$holiday_filter]['title'];
            self::$holiday_filter_instance = new $filterclass();
        }
        if(self::$holiday_auto && self::$holiday_driver >= 0 && is_null(self::$holiday_driver_instance)) {
            $drivers_alloc = Date_Holidays::getInstalledDrivers();
            self::$holiday_driver_instance = Date_Holidays::factory($drivers_alloc[self::$holiday_driver]['title'], null, $AppUI->user_locale);
        }
    }

    public static function isHoliday( $date=0, $userid = 0 )
    {
        global $AppUI;
        self::loadHolidaysSettings();
        if(!$date) {
            $date=new w2p_Utilities_Date();
        }
        if(self::$holiday_manual) {
            // Check whether the date is blacklisted
            $q = new w2p_Database_Query();
            $q->addTable("holiday");
            $q->addQuery("*");
            $where = "( date(holiday_start_date) <= '";
            $where.= $date->format( '%Y-%m-%d' );
            $where.= "' AND date(holiday_end_date) >= '";
            $where.= $date->format( '%Y-%m-%d' ) ;
            $where.= "' AND holiday_type=".HOLIDAY_TYPE_COMPANY_WORKDAY." ) ";
            $where.= "OR ( ";
            $where.= " DATE_FORMAT(holiday_start_date, '%m-%d') <= '";
            $where.= $date->format( '%m-%d' );
            $where.= "' AND DATE_FORMAT(holiday_end_date, '%m-%d') >= '";
            $where.= $date->format( '%m-%d' );
            $where.= "' AND holiday_annual=1";
            $where.= " AND holiday_type=".HOLIDAY_TYPE_COMPANY_WORKDAY." )";
            $q->addWhere($where);
            if($q->loadResult()) {
                return false;
            }
            // Check if we have a whitelist item for this date 
            $q->addTable("holiday");
            $q->addQuery("*");
            $where = "( date(holiday_start_date) <= '";
            $where.= $date->format( '%Y-%m-%d' );
            $where.= "' AND date(holiday_end_date) >= '";
            $where.= $date->format( '%Y-%m-%d' ) ;
            if ($userid > 0) {
                $where.= "' AND (";
                $where.= "(holiday_user=0 AND holiday_type=".HOLIDAY_TYPE_COMPANY_HOLIDAY.")";
                $where.= " OR ";
                $where.= "(holiday_user=".$userid." AND holiday_type=".HOLIDAY_TYPE_USER_HOLIDAY.")";
                $where.= ")";
            }
            else {
                $where.= "' AND holiday_type=".HOLIDAY_TYPE_COMPANY_HOLIDAY;
            }
            $where.= " ) OR ( ";
            $where.= " DATE_FORMAT(holiday_start_date, '%m-%d') <= '";
            $where.= $date->format( '%m-%d' );
            $where.= "' AND DATE_FORMAT(holiday_end_date, '%m-%d') >= '";
            $where.= $date->format( '%m-%d' );
            $where.= "' AND holiday_annual=1";
            $where.= " AND holiday_type=".HOLIDAY_TYPE_COMPANY_HOLIDAY." )";
            $q->addWhere($where);
            if($q->loadResult()) {
                return true;
            }
        }
        if(self::$holiday_auto && self::$holiday_driver >= 0) {
            // Still here? Ok, lets poll the automatic system
            if (self::$holiday_driver_instance->getYear() != $date->getYear()) {
                self::$holiday_driver_instance->setYear($date->getYear());
            }
            if (!Date_Holidays::isError(self::$holiday_driver_instance)) {
                $holidays = self::$holiday_driver_instance->getHolidayForDate($date, null, true);
                if (!is_null($holidays)) {
                    foreach($holidays as $holiday) {
                        if (is_null(self::$holiday_filter_instance) || self::$holiday_filter_instance->accept($holiday->getInternalName())) {
                            return true;
                        }
                    }
                }
            }
        }
        // No hits, must be a working day
        return false;
    }

    public static function getBlacklistForDatespan( $start, $end )
    {
        self::loadHolidaysSettings();
        $blacklist = array();
        if(self::$holiday_manual) {
            $q = new w2p_Database_Query();
            $q->addTable("holiday");
            $q->addQuery("*");
            $where = "( date(holiday_start_date) <= '";
            $where.= $end->format( '%Y-%m-%d' );
            $where.= "' AND date(holiday_end_date) >= '";
            $where.= $start->format( '%Y-%m-%d' ) ;
            $where.= "' AND holiday_type=".HOLIDAY_TYPE_COMPANY_WORKDAY." ) ";
            $where.= "OR ( ";
            $where.= " DATE_FORMAT(holiday_start_date, '%m-%d') <= '";
            $where.= $end->format( '%m-%d' );
            $where.= "' AND DATE_FORMAT(holiday_end_date, '%m-%d') >= '";
            $where.= $start->format( '%m-%d' );
            $where.= "' AND holiday_annual=1";
            $where.= " AND holiday_type=".HOLIDAY_TYPE_COMPANY_WORKDAY." )";
            $q->addWhere($where);
            $list = $q->loadList();
            foreach ($list as $i => $item) {
                $startDate = new Date($item['holiday_start_date']);
                $endDate = new Date($item['holiday_end_date']);
                $blacklist[] = array(
                    'id'=>$item['holiday_id'], 
                    'user'=>$item['holiday_user'],
                    'name'=>$item['holiday_description'],
                    'type'=>HOLIDAY_TYPE_COMPANY_WORKDAY,
                    'startDate'=>$startDate,
                    'endDate'=>$endDate,
                    'description'=>$item['holiday_description']
                );
            }
        }
        return $blacklist;
    }

    public static function getWhitelistForDatespan( $start, $end, $userid = 0 )
    {
        self::loadHolidaysSettings();
        $whitelist = array();
        if(self::$holiday_manual) {
            $q = new w2p_Database_Query();
            $q->addTable("holiday");
            $q->addQuery("*");
            $where = "( date(holiday_start_date) <= '";
            $where.= $end->format( '%Y-%m-%d' );
            $where.= "' AND date(holiday_end_date) >= '";
            $where.= $start->format( '%Y-%m-%d' ) ;
            if ($userid > 0) {
                $where.= "' AND (";
                $where.= "(holiday_user=0 AND holiday_type=".HOLIDAY_TYPE_COMPANY_HOLIDAY.")";
                $where.= " OR ";
                $where.= "(holiday_user=".$userid." AND holiday_type=".HOLIDAY_TYPE_USER_HOLIDAY.")";
                $where.= ")";
            }
            else {
                $where.= "' AND holiday_type=".HOLIDAY_TYPE_COMPANY_HOLIDAY;
            }
            $where.= " ) OR ( ";
            $where.= " DATE_FORMAT(holiday_start_date, '%m-%d') <= '";
            $where.= $end->format( '%m-%d' );
            $where.= "' AND DATE_FORMAT(holiday_end_date, '%m-%d') >= '";
            $where.= $start->format( '%m-%d' );
            $where.= "' AND holiday_annual=1";
            $where.= " AND holiday_type=".HOLIDAY_TYPE_COMPANY_HOLIDAY." )";
            $q->addWhere($where);
            $list = $q->loadList();
            foreach ($list as $i => $item) {
                $startDate = new Date($item['holiday_start_date']);
                $endDate = new Date($item['holiday_end_date']);
                $whitelist[] = array(
                    'id'=>$item['holiday_id'], 
                    'user'=>$item['holiday_user'],
                    'name'=>$item['holiday_description'],
                    'type'=>$item['holiday_type'],
                    'startDate'=>$startDate,
                    'endDate'=>$endDate,
                    'description'=>$item['holiday_description']
                );
            }
        }
        return $whitelist;
    }

    public static function getDefaultCalendarHolidaysForDatespan( $start, $end )
    {
        global $AppUI;
        self::loadHolidaysSettings();
        $holidaysArray = array();
        if(self::$holiday_auto && self::$holiday_driver >= 0) {
            if (self::$holiday_driver_instance->getYear() != $start->getYear()) {
                self::$holiday_driver_instance->setYear($start->getYear());
                self::$holiday_driver_instance->setLocale($AppUI->user_locale);
            }
            if (!Date_Holidays::isError(self::$holiday_driver_instance)) {
                $holidays = self::$holiday_driver_instance->getHolidaysForDatespan($start, $end, self::$holiday_filter_instance);
                $id = 1000;
                foreach($holidays as $holiday) {
                    if (Date_Holidays::isError($holiday)) {
                        continue;
                    }
                    $title = $holiday->getTitle();
                    if (!is_string($title)) {
                        $title = $title->getMessage();
                    }
                    $holidaysArray[] = array(
                        'id'=>$id++, 
                        'user'=>0,
                        'name'=>$holiday->getInternalName(),
                        'type'=>HOLIDAY_TYPE_CALENDAR_HOLIDAY,
                        'startDate'=>$holiday->getDate(),
                        'endDate'=>$holiday->getDate(),
                        'description'=>$title
                    );
                }
            }
        }
        return $holidaysArray;
    }

    public static function getHolidaysForDatespan( $start, $end, $userid = 0 )
    {
        global $AppUI;
        self::loadHolidaysSettings();
        $holidaysArray = self::getWhitelistForDatespan( $start, $end, $userid );
        $blacklist = self::getBlacklistForDatespan( $start, $end );
        if(self::$holiday_auto && self::$holiday_driver >= 0) {
            if (self::$holiday_driver_instance->getYear() != $start->getYear()) {
                self::$holiday_driver_instance->setYear($start->getYear());
                self::$holiday_driver_instance->setLocale($AppUI->user_locale);
            }
            if (!Date_Holidays::isError(self::$holiday_driver_instance)) {
                $holidays = self::$holiday_driver_instance->getHolidaysForDatespan($start, $end, self::$holiday_filter_instance);
                $id = 1000;
                foreach($holidays as $holiday) {
                    if (!self::isInList( clone $holiday->getDate(), $blacklist )) {
                        $title = $holiday->getTitle();
                        if (!is_string($title)) {
                            $title = $title->getMessage();
                        }
                        $holidaysArray[] = array(
                            'id'=>$id++, 
                            'user'=>0,
                            'name'=>$holiday->getInternalName(),
                            'type'=>HOLIDAY_TYPE_CALENDAR_HOLIDAY,
                            'startDate'=>$holiday->getDate(),
                            'endDate'=>$holiday->getDate(),
                            'description'=>$title
                        );
                    }
                }
            }
        }
        return $holidaysArray;
    }

    public static function getHolidayTitle( $date=0, $userid = 0 )
    {
        global $AppUI;
        self::loadHolidaysSettings();
        if(!$date) {
            $date=new w2p_Utilities_Date();
        }
        if(self::$holiday_manual) {
            $q = new w2p_Database_Query();
            // Check if we have a whitelist item for this date 
            $q->addTable("holiday");
            $q->addQuery("holiday_description");
            $where = "( date(holiday_start_date) <= '";
            $where.= $date->format( '%Y-%m-%d' );
            $where.= "' AND date(holiday_end_date) >= '";
            $where.= $date->format( '%Y-%m-%d' ) ;
            if ($userid > 0) {
                $where.= "' AND (";
                $where.= "(holiday_user=0 AND holiday_type=".HOLIDAY_TYPE_COMPANY_HOLIDAY.")";
                $where.= " OR ";
                $where.= "(holiday_user=".$userid." AND holiday_type=".HOLIDAY_TYPE_USER_HOLIDAY.")";
                $where.= ")";
            }
            else {
                $where.= "' AND holiday_type=".HOLIDAY_TYPE_COMPANY_HOLIDAY;
            }
            $where.= " ) OR ( ";
            $where.= " DATE_FORMAT(holiday_start_date, '%m-%d') <= '";
            $where.= $date->format( '%m-%d' );
            $where.= "' AND DATE_FORMAT(holiday_end_date, '%m-%d') >= '";
            $where.= $date->format( '%m-%d' );
            $where.= "' AND holiday_annual=1";
            $where.= " AND holiday_type=".HOLIDAY_TYPE_COMPANY_HOLIDAY." )";
            $q->addWhere($where);
            $holiday_description = $q->loadResult();
            if ($holiday_description !== false) {
                return $holiday_description;
            }
        }
        if(self::$holiday_auto && self::$holiday_driver >= 0) {
            // Still here? Ok, lets poll the automatic system
            if (self::$holiday_driver_instance->getYear() != $date->getYear()) {
                self::$holiday_driver_instance->setYear($date->getYear());
                self::$holiday_driver_instance->setLocale($AppUI->user_locale);
            }
            if (!Date_Holidays::isError(self::$holiday_driver_instance)) {
                $holidays = self::$holiday_driver_instance->getHolidayForDate($date, null, true);
                if (!is_null($holidays)) {
                    $titles = array();
                    foreach($holidays as $holiday) {
                        if (is_null(self::$holiday_filter_instance) || self::$holiday_filter_instance->accept($holiday->getInternalName())) {
                            $title = $holiday->getTitle();
                            if (!in_array($title, $titles)) {
                                $titles[] = gettype($title) == 'object' ? $title->getMessage() : $title;
                            }
                        }
                    }
                    return implode("/", $titles);
                }
            }
        }
        return "";
    }

    public static function makeHolidaysRecords($dates, $user=0, $type=HOLIDAY_TYPE_COMPANY_HOLIDAY, $annual=1, $description="")
    {
        sort($dates);
        $records = array();
        $start = $end = null;
        foreach($dates as $i => $date) {
            $odate = new Date($date);
            if ($i == 0) {
                $start = clone $odate;
                $end = clone $odate;
            }
            elseif ($end->getNextDay()->equals(clone $odate)) {
                $end = clone $odate;
            }
            else {
                $records[] = array(
                    'startDate' => $start, 
                    'endDate' => $end,
                    'holiday_user' => $user, 
                    'holiday_type' => $type, 
                    'holiday_annual' => $annual, 
                    'holiday_start_date' => $start->getDate(), 
                    'holiday_end_date' => $end->getDate(),
                    'holiday_description' => $description
                );
                $start = clone $odate;
                $end = clone $odate;
            }
        }
        if (!is_null($start) && !is_null($end)) {
            $records[] = array(
                'startDate' => $start, 
                'endDate' => $end,
                'holiday_user' => $user, 
                'holiday_type' => $type, 
                'holiday_annual' => $annual, 
                'holiday_start_date' => $start->getDate(), 
                'holiday_end_date' => $end->getDate(),
                'holiday_description' => $description
            );
        }
        return $records;
    }

    public static function storeRecords( $records, $user, $type=HOLIDAY_TYPE_COMPANY_HOLIDAY, $annual=1, $description="" )
    {
        global $AppUI;
        foreach($records as $record) {
            $startDate = $record['startDate']->getPrevDay()->format( '%Y-%m-%d' );
            $endDate = $record['endDate']->getNextDay()->format( '%Y-%m-%d' );
            $q = new w2p_Database_Query();
            $q->addTable("holiday");
            $q->addQuery("*");
            $where = "( date(holiday_start_date) = '";
            $where.= $endDate;
            $where.= "' OR date(holiday_end_date) = '";
            $where.= $startDate;
            $where.= "' )";
            $where.= " AND holiday_user=".$user;
            $where.= " AND holiday_type=".$type;
            $where.= " AND holiday_annual=".$annual;
            $where.= " AND holiday_description=".$q->quote($description);
            $q->addWhere($where);
            $q->addOrder('holiday_start_date');
            $list = $q->loadList();
            $obj = new CHoliday();
            switch (sizeof($list)) {
                case 0:
                    if (!$obj->bind($record)) {
                        $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
                        $AppUI->redirect();
                    } 
                    break;
                case 1:
                    $item = $list[0];
                    $obj->load($item['holiday_id']);
                    if (substr($item['holiday_start_date'], 0, 10) == $endDate) {
                        $obj->holiday_start_date = $record['endDate']->getDate();
                    }
                    else {
                        $obj->holiday_end_date = $record['startDate']->getDate();
                    }
                    break;
                case 2:
                    $item1 = $list[0];
                    $item2 = $list[1];
                    if (substr($item1['holiday_end_date'], 0, 10) != $startDate || substr($item2['holiday_start_date'], 0, 10) != $endDate) {
                        $AppUI->setMsg($AppUI->_('User holidays inconsistency'), UI_MSG_ERROR);
                        $AppUI->redirect();
                    }
                    $obj2 = new CHoliday();
                    $obj2->load($item2['holiday_id']);
                    $result = $obj2->delete($AppUI);
                    if (is_string($result)) {
                        $AppUI->setMsg($result, UI_MSG_ERROR);
                        $AppUI->redirect();
                    }
                    $obj->load($item1['holiday_id']);
                    $obj->holiday_end_date = $item2['holiday_end_date'];
                    break;
                default:
                    $AppUI->setMsg($AppUI->_('User holidays inconsistency'), UI_MSG_ERROR);
                    $AppUI->redirect();
            }
            $result = $obj->store($AppUI);
            if (is_string($result)) {
                $AppUI->setMsg($result, UI_MSG_ERROR);
                $AppUI->redirect();
            }
        }
    }

    private static function isInList( $date, $list )
    {
        foreach ($list as $item) {
            if (!$date->before(clone $item['startDate']) && !$date->after(clone $item['endDate'])) {
                return true;
            }
        }
        return false;
    }
}
